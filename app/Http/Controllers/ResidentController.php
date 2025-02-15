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

    public function searchAddress(Request $request)
    {
        try {
            $search = $request->input('query');
            
            if (empty($search)) {
                return response()->json([]);
            }

            // Search member_sum table for matching addresses
            $addresses = MemberSum::where('mem_add_id', 'LIKE', $search . '%')
                ->select('mem_id', 'mem_add_id')
                ->limit(10)
                ->get();

            return response()->json($addresses);
            
        } catch (\Exception $e) {
            Log::error('Address search error: ' . $e->getMessage());
            return response()->json(['error' => 'Error searching addresses'], 500);
        }
    }

    public function getMemberDetails($mem_id)
    {
        try {
            Log::info("Fetching details for member ID: {$mem_id}");
    
            // Get latest member data entry
            $memberData = MemberData::where('mem_id', $mem_id)
                ->orderBy('mem_transno', 'desc')
                ->first();
            
            Log::info("Member data retrieved", [
                'mem_id' => $mem_id,
                'trans_no' => $memberData?->mem_transno ?? 'Not found'
            ]);
    
            // Get ALL vehicle records with detailed logging
            $vehicles = CarSticker::where('mem_id', $mem_id)->get();
            
            // Log each vehicle's details
            foreach ($vehicles as $vehicle) {
                Log::info("Vehicle record retrieved", [
                    'mem_id' => $mem_id,
                    'vehicle_id' => $vehicle->id,
                    'plate_no' => $vehicle->plate_no,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'color' => $vehicle->color,
                    'sticker_no' => $vehicle->sticker_no,
                    'status' => $vehicle->status,
                    'created_at' => $vehicle->created_at,
                    'updated_at' => $vehicle->updated_at
                ]);
            }
    
            Log::info("Total vehicles found for member: " . $vehicles->count());
    
            // Get member summary information
            $memberSum = MemberSum::find($mem_id);
            
            if (!$memberSum) {
                Log::warning("Member summary not found for ID: {$mem_id}");
                return response()->json(['error' => 'Member not found'], 404);
            }
    
            return response()->json([
                'memberSum' => $memberSum,
                'memberData' => $memberData,
                'vehicles' => $vehicles
            ]);
    
        } catch (\Exception $e) {
            Log::error('Member details fetch error', [
                'mem_id' => $mem_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            $memberData->mem_name = $request->members_name;
            $memberData->mem_mobile = $request->contact_number;
            $memberData->mem_email = $request->email;
            $memberData->mem_SPA_Tenant = $request->tenant_spa;
            $memberData->mem_remarks = $request->member_remarks; // Updated field name
            $memberData->user_id = auth()->id();
            
            // Handle residents and relationships
            for ($i = 1; $i <= 10; $i++) {
                $memberData->{"mem_Resident$i"} = $request->input("residents.$i.name");
                $memberData->{"mem_Relationship$i"} = $request->input("residents.$i.relationship");
            }
            
            $memberData->save();
    
            // Handle vehicle information
            if ($request->has('vehicles')) {
                foreach ($request->vehicles as $vehicle) {
                    // Only create record if at least one field is filled
                    if (!empty($vehicle['vehicle_maker']) || !empty($vehicle['car_sticker'])) {
                        $carSticker = new CarSticker();
                        $carSticker->mem_id = $memberSum->mem_id;
                        $carSticker->car_sticker = $vehicle['car_sticker'];
                        $carSticker->vehicle_maker = $vehicle['vehicle_maker'];
                        $carSticker->vehicle_type = $vehicle['vehicle_type'];
                        $carSticker->vehicle_color = $vehicle['vehicle_color'];
                        $carSticker->vehicle_OR = $vehicle['vehicle_OR'];
                        $carSticker->vehicle_CR = $vehicle['vehicle_CR'];
                        $carSticker->vehicle_plate = $vehicle['vehicle_plate'];
                        $carSticker->vehicle_active = $vehicle['vehicle_active'] ?? 0;
                        $carSticker->remarks = $request->vehicle_remarks; // Updated field name
                        $carSticker->user_id = auth()->id();
                        $carSticker->save();
                    }
                }
            }
    
            DB::commit();
            return redirect()->route('residents.index')->with('success', 'Resident information saved successfully');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving resident information');
        }
    }
}
