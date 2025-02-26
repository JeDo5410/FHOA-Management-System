<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MemberSum;
use App\Models\MemberData;
use App\Models\MemType;
use App\Models\CarSticker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResidentController extends Controller
{
    public function residentsData()
    {
        // Get all member types for the radio buttons
        $memberTypes = MemType::all();

        return view('residents.residents_data', compact('memberTypes'));
    }

    public function searchByName(Request $request)
    {
        try {
            Log::info('Member name search initiated', ['ip' => $request->ip(), 'user_id' => auth()->id() ?? 'guest']);
            
            $search = $request->input('query');
            Log::info('Search query received', ['query' => $search]);
    
            if (empty($search)) {
                Log::info('Empty search query, returning empty result');
                return response()->json([]);
            }
    
            // Build the query
            $query = MemberData::where(function($q) use ($search) {
                    $q->where('mem_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('mem_SPA_Tenant', 'LIKE', '%' . $search . '%');
                })
                ->join('member_sum', 'member_data.mem_id', '=', 'member_sum.mem_id')
                ->select(
                    'member_data.mem_id',
                    'member_data.mem_name',
                    'member_data.mem_SPA_Tenant',
                    'member_sum.mem_add_id'
                )
                // Get only the latest record for each member
                ->whereIn('member_data.mem_transno', function($subquery) {
                    $subquery->selectRaw('MAX(md.mem_transno)')
                        ->from('member_data as md')
                        ->groupBy('md.mem_id');
                })
                ->limit(10);
                
            // Log the SQL query being executed
            $sqlWithBindings = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('SQL query for member search', [
                'sql' => $sqlWithBindings,
                'bindings' => $bindings
            ]);
            
            // Execute the query
            $members = $query->get();
            
            Log::info('Member search results retrieved', [
                'count' => $members->count(),
                'query' => $search
            ]);
    
            return response()->json($members);
        } catch (\Exception $e) {
            Log::error('Member name search error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'query' => $request->input('query')
            ]);
            return response()->json(['error' => 'Error searching members: ' . $e->getMessage()], 500);
        }
    }
    

    public function searchAddress(Request $request)
    {
        try {
            $search = $request->input('query');
    
            if (empty($search)) {
                return response()->json([]);
            }
    
            // Search member_sum table for matching addresses and join with member_data to get names
            $addresses = MemberSum::where('mem_add_id', 'LIKE', $search . '%')
                ->join('member_data', function ($join) {
                    $join->on('member_sum.mem_id', '=', 'member_data.mem_id')
                        ->whereIn('member_data.mem_transno', function ($query) {
                            $query->select(DB::raw('MAX(mem_transno)'))
                                ->from('member_data')
                                ->groupBy('mem_id');
                        });
                })
                ->select('member_sum.mem_id', 'member_sum.mem_add_id', 'member_data.mem_name')
                ->limit(10)
                ->get();
    
            return response()->json($addresses);
        } catch (\Exception $e) {
            Log::error('Address search error: ' . $e->getMessage());
            return response()->json(['error' => 'Error searching addresses'], 500);
        }
    }
    

    public function validateAddress($addressId)
    {
        try {
            // Modified to handle alphanumeric address IDs
            $memberSum = MemberSum::where('mem_add_id', $addressId)->first();

            if (!$memberSum) {
                return response()->json(['error' => 'Address not found'], 404);
            }

            // Get latest member data entry
            $memberData = MemberData::where('mem_id', $memberSum->mem_id)
                ->orderBy('mem_transno', 'desc')
                ->first();

            // Get latest vehicle records
            $latestVehiclesTimestamp = CarSticker::where('mem_id', $memberSum->mem_id)
                ->max('timestamp');

            $vehicles = CarSticker::where('mem_id', $memberSum->mem_id)
                ->where('timestamp', $latestVehiclesTimestamp)
                ->get();

            return response()->json([
                'mem_id' => $memberSum->mem_id,
                'memberData' => [
                    'memberSum' => $memberSum,
                    'memberData' => $memberData,
                    'vehicles' => $vehicles
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Address validation error: ' . $e->getMessage());
            return response()->json(['error' => 'Error validating address'], 500);
        }
    }

    public function getMemberDetails($mem_id)
    {
        try {
            // Get latest member data entry
            $memberData = MemberData::where('mem_id', $mem_id)
                ->orderBy('mem_transno', 'desc')
                ->first();

            // Get only the latest vehicle records using a subquery for max timestamp
            $latestVehiclesTimestamp = CarSticker::where('mem_id', $mem_id)
                ->max('timestamp');

            $vehicles = CarSticker::where('mem_id', $mem_id)
                ->where('timestamp', $latestVehiclesTimestamp)
                ->get();

            // Get member summary information
            $memberSum = MemberSum::find($mem_id);

            if (!$memberSum) {
                return response()->json(['error' => 'Member not found'], 404);
            }

            return response()->json([
                'memberSum' => $memberSum,
                'memberData' => $memberData,
                'vehicles' => $vehicles
            ]);
        } catch (\Exception $e) {
            Log::error('Member details fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching member details'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get mem_id from member_sum using mem_add_id
            $memberSum = MemberSum::where('mem_add_id', $request->address_id)->first();

            if (!$memberSum) {
                return back()->with('error', 'Address ID not found');
            }

            // Create new member_data entry
            $memberData = new MemberData();
            $memberData->mem_id = $memberSum->mem_id;
            $memberData->mem_typecode = $request->mem_typecode;
            // Convert member's name to uppercase
            $memberData->mem_name = strtoupper($request->mem_name);
            $memberData->mem_mobile = $request->contact_number;
            $memberData->mem_email = $request->email;
            // Convert tenant/SPA to uppercase
            $memberData->mem_SPA_Tenant = strtoupper($request->tenant_spa);
            $memberData->mem_remarks = $request->member_remarks;
            $memberData->user_id = auth()->id();

            // Validate the request
            $request->validate([
                // ... other validation rules ...
                'member_remarks' => 'nullable|string|max:100',
            ]);

            // Handle residents and relationships with uppercase conversion
            for ($i = 0; $i < 10; $i++) {
                $dbFieldNumber = $i + 1;
                
                // Convert resident name to uppercase if not empty
                $residentName = $request->input("residents.$i.name");
                if (!empty($residentName)) {
                    $residentName = strtoupper($residentName);
                }
                $memberData->{"mem_Resident$dbFieldNumber"} = $residentName;
                $memberData->{"mem_Relationship$dbFieldNumber"} = $request->input("residents.$i.relationship");
            }

            $memberData->save();

            // Handle vehicle information
            if ($request->has('vehicles')) {
                foreach ($request->vehicles as $vehicle) {
                    // Only create record if at least one field is filled
                    if (!empty($vehicle['vehicle_maker']) || !empty($vehicle['car_sticker'])) {
                        $carSticker = new CarSticker();
                        $carSticker->mem_id = $memberSum->mem_id;
                        $carSticker->mem_code = $request->mem_typecode;
                        $carSticker->car_sticker = $vehicle['car_sticker'];
                        $carSticker->vehicle_maker = $vehicle['vehicle_maker'];
                        $carSticker->vehicle_type = $vehicle['vehicle_type'];
                        $carSticker->vehicle_color = $vehicle['vehicle_color'];
                        $carSticker->vehicle_OR = $vehicle['vehicle_OR'];
                        $carSticker->vehicle_CR = $vehicle['vehicle_CR'];
                        $carSticker->vehicle_plate = $vehicle['vehicle_plate'];
                        $carSticker->vehicle_active = $vehicle['vehicle_active'] ?? 0;
                        $carSticker->remarks = $request->vehicle_remarks;
                        $carSticker->user_id = auth()->id();
                        $carSticker->save();
                    }
                }
            }

            DB::commit();
            return redirect()->route('residents.residents_data')->with('success', 'Resident information saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving resident information: ' . $e->getMessage());
            return back()->with('error', 'Error saving resident information: ' . $e->getMessage());
        }
    }
}