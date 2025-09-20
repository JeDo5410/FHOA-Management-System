<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPermit;
use App\Models\PermitType;
use App\Models\StatusType;
use App\Models\MemberSum;
use App\Models\ViewConstructionPermit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConstructionPermitController extends Controller
{
    public function index()
    {
        $permitTypes = PermitType::orderBy('typecode')->get();

        try {
            DB::statement('CALL sp_permit_status()');
            LOG::info('Stored procedure sp_permit_status executed successfully', [
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error executing stored procedure sp_permit_status', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'timestamp' => now()
            ]);
        }

        return view('construction-permit.construction-permit', compact('permitTypes'));
    }

    public function store(Request $request)
    {
        try {
            // Validate required fields (excluding inspector/bond section)
            $validatedData = $request->validate([
                'permit_number' => 'required|integer',
                'address_id' => 'required|string|max:45',
                'applicant_name' => 'required|string|max:255',
                'application_date' => 'required|date',
                'applicant_contact' => 'required|string|max:255',
                'contractor_name' => 'required|string|max:255',
                'contractor_contact' => 'required|string|max:255',
                'permit_type_id' => 'required|integer|exists:permit_type,typecode',
                'permit_sin' => 'required|integer',
                'amount_paid' => 'required|numeric|min:0',
                'paid_date' => 'required|date',
                'bond_arn' => 'required|string|max:255',
                'bond_paid' => 'required|numeric|min:0',
                'bond_paid_date' => 'required|date',
                'permit_start_date' => 'required|date',
                'permit_end_date' => 'required|date|after_or_equal:permit_start_date',
                'remarks' => 'nullable|string|max:200',
                // Optional inspector/bond fields
                'inspector' => 'nullable|string|max:255',
                'inspector_note' => 'nullable|string|max:255',
                'inspection_date' => 'nullable|date',
                'bond_receiver' => 'nullable|string|max:255',
                'bond_release_date' => 'nullable|date',
                'payment_type' => 'nullable|string|max:255',
            ]);

            // Get member ID from Member_sum table using address_id
            $memberSum = MemberSum::where('mem_add_id', $validatedData['address_id'])->first();
            
            if (!$memberSum) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Address ID. Member not found.'
                ], 422);
            }

            // Prepare data for construction permit
            $permitData = [
                'permit_no' => $validatedData['permit_number'], 
                'mem_id' => $memberSum->mem_id,
                'application_date' => $validatedData['application_date'],
                'applicant_name' => $validatedData['applicant_name'],
                'applicant_contact' => $validatedData['applicant_contact'],
                'contractor_name' => $validatedData['contractor_name'],
                'contractor_contact' => $validatedData['contractor_contact'],
                'permit_type' => $validatedData['permit_type_id'],
                'permit_sin' => $validatedData['permit_sin'],
                'permit_arn' => $validatedData['bond_arn'], // permit_arn comes from bond_arn
                'permit_fee' => $validatedData['amount_paid'],
                'permit_bond' => $validatedData['bond_paid'],
                'permit_fee_date' => $validatedData['paid_date'],
                'permit_bond_date' => $validatedData['bond_paid_date'],
                'permit_start_date' => $validatedData['permit_start_date'],
                'permit_end_date' => $validatedData['permit_end_date'],
                'status_type' => 1, // Set to 1 for "On-Going" status
                'remarks' => $validatedData['remarks'],
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ];

            // Add optional inspector/bond fields if provided
            if (!empty($validatedData['inspector'])) {
                $permitData['Inspector'] = $validatedData['inspector'];
            }
            if (!empty($validatedData['inspector_note'])) {
                $permitData['inspector_note'] = $validatedData['inspector_note'];
            }
            if (!empty($validatedData['inspection_date'])) {
                $permitData['inspection_date'] = $validatedData['inspection_date'];
            }
            if (!empty($validatedData['bond_receiver'])) {
                $permitData['bond_receiver'] = $validatedData['bond_receiver'];
            }
            if (!empty($validatedData['bond_release_date'])) {
                $permitData['bond_release_date'] = $validatedData['bond_release_date'];
            }
            if (!empty($validatedData['payment_type'])) {
                $permitData['bond_release_type'] = $validatedData['payment_type'];
            }

            // Create the construction permit
            $constructionPermit = ConstructionPermit::create($permitData);

            // Log the successful creation
            Log::info('Construction permit created successfully', [
                'permit_no' => $constructionPermit->permit_no,
                'user_id' => Auth::id(),
                'member_id' => $memberSum->mem_id,
                'address_id' => $validatedData['address_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Construction permit created successfully!',
                'permit_no' => $constructionPermit->permit_no,
                'permit_data' => $constructionPermit
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating construction permit', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the construction permit. Please try again.'
            ], 500);
        }
    }
    
    private function formatAddressId(string $addressId): string
    {
        try {
            if (strlen($addressId) !== 5 || !ctype_digit($addressId)) {
                // Return original ID if it's not a 5-digit string
                return $addressId;
            }

            $phase = substr($addressId, 0, 1);
            $block = substr($addressId, 1, 2);
            $lot = substr($addressId, 3, 2);

            return "Ph. {$phase} Blk. {$block} Lot {$lot}";

        } catch (\Exception $e) {
            // Log the error for debugging and return the original ID as a fallback
            Log::error('Error formatting Address ID: ' . $e->getMessage(), ['addressId' => $addressId]);
            return $addressId;
        }
    }

public function search(string $permitNumber): JsonResponse
    {
        try {
            // CORRECTED: Use the correct column alias 'Permit No.' from the view.
            $permit = ViewConstructionPermit::where('permit_no', $permitNumber)->first();

            if (!$permit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Construction permit not found.'
                ], 404);
            }

            // Map the view's aliased columns to the desired JSON response keys.
            $permitData = [
                // CORRECTED: Access the property using the correct alias.
                'permit_no' => $permit->{'Permit No.'},
                'status_type' => $permit->{'statuscode'},
                'address_id' => $permit->{'HOA Address ID.'},
                'member_name' => $permit->{'HOA Name'},
                'address' => $this->formatAddressId($permit->{'HOA Address ID.'}),
                'total_arrears' => $permit->arrear_total,
                'applicant_name' => $permit->{'Applicant Name'},
                'application_date' => $permit->ApplicationDate,
                'applicant_contact' => $permit->{'Applicant Contact'},
                'contractor_name' => $permit->{'Contractor Name'},
                'contractor_contact' => $permit->{'Contractor Contact'},
                'permit_type' => $permit->{'typecode'},
                'permit_sin' => $permit->{'Payment SIN'},
                'permit_fee' => $permit->{'Fee Amt.'},
                'permit_fee_date' => $permit->{'SIN Date'},
                'permit_arn' => $permit->{'Bond ARN'},
                'permit_bond' => $permit->{'Bond Amt.'},
                'permit_bond_date' => $permit->{'Bond Date'},
                'permit_start_date' => $permit->{'Permit Start Date'},
                'permit_end_date' => $permit->{'Permit End Date'},
                'Inspector' => $permit->Inspector,
                'inspector_note' => $permit->{'Inspector Note'},
                'inspection_date' => $permit->{'Inspection Date'},
                'bond_receiver' => $permit->{'Bond Receiver'},
                'bond_release_date' => $permit->{'Bond Release Date'},
                'bond_release_type' => $permit->{'Bond Release Type'},
                'payment_type' => $permit->{'Payment Type'},
                'remarks' => $permit->Remarks,
            ];

            Log::info('Construction permit found', [
                'permit_no' => $permit->{'Permit No.'},
                'status_type' => $permit->{'statuscode'},
                'address_id' => $permit->{'HOA Address ID.'},
                'member_name' => $permit->{'HOA Name'},
                'address' => $this->formatAddressId($permit->{'HOA Address ID.'}),
                'total_arrears' => $permit->arrear_total,
                'applicant_name' => $permit->{'Applicant Name'},
                'application_date' => $permit->ApplicationDate,
                'applicant_contact' => $permit->{'Applicant Contact'},
                'contractor_name' => $permit->{'Contractor Name'},
                'contractor_contact' => $permit->{'Contractor Contact'},
                'permit_type' => $permit->{'typecode'},
                'permit_sin' => $permit->{'Payment SIN'},
                'permit_fee' => $permit->{'Fee Amt.'},
                'permit_fee_date' => $permit->{'SIN Date'},
                'permit_arn' => $permit->{'Bond ARN'},
                'permit_bond' => $permit->{'Bond Amt.'},
                'permit_bond_date' => $permit->{'Bond Date'}, // Corrected based on your latest view
                'permit_start_date' => $permit->{'Permit Start Date'},
                'permit_end_date' => $permit->{'Permit End Date'},
                'Inspector' => $permit->Inspector,
                'inspector_note' => $permit->{'Inspector Note'},
                'inspection_date' => $permit->{'Inspection Date'}, // 'inspection_date' is not available in this view
                'bond_receiver' => $permit->{'Bond Receiver'},
                'bond_release_date' => $permit->{'Bond Release Date'},
                'bond_release_type' => $permit->{'Bond Release Type'},
                'payment_type' => $permit->{'Payment Type'},
                'remarks' => $permit->Remarks,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Construction permit found.',
                'permit' => $permitData
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error searching construction permit', [
                'error' => $e->getMessage(),
                'permit_number' => $permitNumber,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for the permit. Please try again.'
            ], 500);
        }
    }

    public function update(Request $request, $permitNumber)
    {
        try {
            // Find the existing construction permit to get the original data
            $existingPermit = ConstructionPermit::where('permit_no', $permitNumber)->first();

            if (!$existingPermit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Construction permit not found.'
                ], 404);
            }

            // Store the original permit end date for comparison
            $originalEndDate = $existingPermit->permit_end_date;

            Log::info('Form Data: Permit', $request->all());
            // Validate the request data
            $validatedData = $request->validate([
                'permit_number' => 'required|integer',
                'address_id' => 'required|string|max:45',
                'applicant_name' => 'required|string|max:255',
                'application_date' => 'required|date',
                'applicant_contact' => 'required|string|max:255',
                'contractor_name' => 'required|string|max:255',
                'contractor_contact' => 'required|string|max:255',
                'permit_type_id' => 'required|integer|exists:permit_type,typecode',
                'permit_sin' => 'required|string|max:45',
                'amount_paid' => 'required|numeric|min:0',
                'paid_date' => 'required|date',
                'bond_arn' => 'required|string|max:255',
                'bond_paid' => 'required|numeric|min:0',
                'bond_paid_date' => 'required|date',
                'permit_start_date' => 'required|date',
                'permit_end_date' => 'required|date|after_or_equal:permit_start_date',
                'remarks' => 'nullable|string|max:200',
                // Optional inspector/bond fields
                'inspector' => 'nullable|string|max:255',
                'inspector_note' => 'nullable|string|max:255',
                'inspection_date' => 'nullable|date',
                'bond_receiver' => 'nullable|string|max:255',
                'bond_release_date' => 'nullable|date',
                'bond_release_type' => 'nullable|string|max:255',
            ]);

            // Get member ID from Member_sum table using address_id
            $memberSum = MemberSum::where('mem_add_id', $validatedData['address_id'])->first();
            
            if (!$memberSum) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Address ID. Member not found.'
                ], 422);
            }

            // Determine the status_type based on business rules
            $statusType = $existingPermit->status_type; // Default to existing status
            
            // Rule 1: Check inspector_note for bond release/forfeiture
            if (!empty($validatedData['inspector_note'])) {
                if ($validatedData['inspector_note'] === 'For Bond Release') {
                    $statusType = 3; // For Bond Release
                } elseif ($validatedData['inspector_note'] === 'For Bond Forfeiture') {
                    $statusType = 4; // Close (Forfeited Bond)
                }
            }
            
            // Rule 2: Check if permit end date is extended (only if not bond release/forfeiture)
            $newEndDate = \Carbon\Carbon::parse($validatedData['permit_end_date']);
            $originalEndDateCarbon = \Carbon\Carbon::parse($originalEndDate);
            
            // Only allow extension if inspector_note is not for bond release or forfeiture
            if ($newEndDate->gt($originalEndDateCarbon)) {
                if (empty($validatedData['inspector_note']) || 
                    (!in_array($validatedData['inspector_note'], ['For Bond Release', 'For Bond Forfeiture']))) {
                    $statusType = 1; // On-Going (extended permit)
                } else {
                    // If trying to extend date with bond release/forfeiture, return error
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot extend permit end date when inspector note is set to "For Bond Release" or "For Bond Forfeiture".'
                    ], 422);
                }
            }

            Log::info('Validated data for update', $validatedData);

            // Prepare data for new record insertion
            $insertData = [
                'permit_no' => $validatedData['permit_number'],
                'mem_id' => $memberSum->mem_id,
                'application_date' => $validatedData['application_date'],
                'applicant_name' => $validatedData['applicant_name'],
                'applicant_contact' => $validatedData['applicant_contact'],
                'contractor_name' => $validatedData['contractor_name'],
                'contractor_contact' => $validatedData['contractor_contact'],
                'permit_type' => $validatedData['permit_type_id'],
                'permit_sin' => $validatedData['permit_sin'],
                'permit_arn' => $validatedData['bond_arn'],
                'permit_fee' => $validatedData['amount_paid'],
                'permit_bond' => $validatedData['bond_paid'],
                'permit_fee_date' => $validatedData['paid_date'],
                'permit_bond_date' => $validatedData['bond_paid_date'],
                'permit_start_date' => $validatedData['permit_start_date'],
                'permit_end_date' => $validatedData['permit_end_date'],
                'status_type' => $statusType,
                'remarks' => $validatedData['remarks'],
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ];

            Log::info('Insert Data Prepared', $insertData);

            // Add optional inspector/bond fields if provided
            if (!empty($validatedData['inspector'])) {
                $insertData['Inspector'] = $validatedData['inspector'];
            }
            if (!empty($validatedData['inspector_note'])) {
                $insertData['inspector_note'] = $validatedData['inspector_note'];
            }
            if (!empty($validatedData['inspection_date'])) {
                $insertData['inspection_date'] = $validatedData['inspection_date'];
            }
            if (!empty($validatedData['bond_receiver'])) {
                $insertData['bond_receiver'] = $validatedData['bond_receiver'];
            }
            if (!empty($validatedData['bond_release_date'])) {
                $insertData['bond_release_date'] = $validatedData['bond_release_date'];
            }
            if (!empty($validatedData['bond_release_type'])) {
                $insertData['bond_release_type'] = $validatedData['bond_release_type'];
            }

        // Create a new record (transactional approach)
        $newPermit = ConstructionPermit::create($insertData);

        Log::info('Construction permit updated successfully (new record created)', [
            'original_permit_no' => $existingPermit->permit_no,
            'new_permit_no' => $newPermit->permit_no,
            'status_type' => $statusType,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Construction permit updated successfully!',
            'permit_no' => $newPermit->permit_no,
            'status_type' => $statusType
        ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating construction permit', [
                'error' => $e->getMessage(),
                'permit_number' => $permitNumber,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the construction permit. Please try again.'
            ], 500);
        }
    }
}