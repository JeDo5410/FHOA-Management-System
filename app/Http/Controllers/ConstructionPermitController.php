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
                'remarks' => 'nullable|string|max:300',
                // Optional inspector/bond fields
                'inspector' => 'nullable|string|max:255',
                'inspector_note' => 'nullable|string|max:255',
                'inspection_date' => 'nullable|date',
                'inspection_form' => 'nullable|boolean',
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
                'inspection_form' => $request->has('inspection_form') ? 1 : 0,
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

            // Get status description from status_type table
            $statusType = StatusType::where('statuscode', $permit->statuscode)->first();
            $statusDescription = $statusType ? $statusType->statusdescription : 'Unknown';

            // Map the view's aliased columns to the desired JSON response keys.
            $permitData = [
                // CORRECTED: Access the property using the correct alias.
                'permit_no' => $permit->{'Permit No.'},
                'status_type' => $permit->{'statuscode'},
                'status_description' => $statusDescription,
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
                'inspection_form' => $permit->inspection_form ?? 0,
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
            // Get the latest record by timestamp since new records are created on update
            $existingPermit = ConstructionPermit::where('permit_no', $permitNumber)
                ->orderBy('timestamp', 'desc')
                ->first();

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
                'remarks' => 'nullable|string|max:300',
                // Optional inspector/bond fields
                'inspector' => 'nullable|string|max:255',
                'inspector_note' => 'nullable|string|max:255',
                'inspection_date' => 'nullable|date',
                'inspection_form' => 'nullable|boolean',
                'bond_receiver' => 'nullable|string|max:255',
                'bond_release_date' => 'nullable|date',
                'bond_release_type' => 'nullable|string|max:255',
            ]);

            // Custom validation for inspector section
            $inspectorNote = $validatedData['inspector_note'] ?? null;
            $bondReceiver = $validatedData['bond_receiver'] ?? null;
            $bondReleaseDate = $validatedData['bond_release_date'] ?? null;
            $bondReleaseType = $validatedData['bond_release_type'] ?? null;
            $inspectionDate = $validatedData['inspection_date'] ?? null;

            // Validation 1: Forfeiture scenario - should not have bond release data
            if ($inspectorNote === 'For Bond Forfeiture') {
                $forfeitureErrors = [];
                
                if (!empty($bondReceiver)) {
                    $forfeitureErrors['bond_receiver'] = ['Bond Receiver must be empty when Inspector Note is "For Bond Forfeiture"'];
                }
                if (!empty($bondReleaseDate)) {
                    $forfeitureErrors['bond_release_date'] = ['Bond Release Date must be empty when Inspector Note is "For Bond Forfeiture"'];
                }
                if (!empty($bondReleaseType)) {
                    $forfeitureErrors['bond_release_type'] = ['Payment Type must be empty when Inspector Note is "For Bond Forfeiture"'];
                }
                
                if (!empty($forfeitureErrors)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bond release information cannot be provided when inspector note is set to "For Bond Forfeiture"',
                        'errors' => $forfeitureErrors
                    ], 422);
                }
            }

            // Validation 2: Date validation for bond release
            if ($inspectorNote === 'For Bond Release' && !empty($bondReleaseDate)) {
                $today = \Carbon\Carbon::today();
                $releaseDate = \Carbon\Carbon::parse($bondReleaseDate);
                
                // Check if bond release date is in the future
                if ($releaseDate->gt($today)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bond Release Date cannot be in the future',
                        'errors' => ['bond_release_date' => ['Bond Release Date cannot be in the future']]
                    ], 422);
                }
                
                // Check if inspection date is provided and bond release date is before inspection date
                if (!empty($inspectionDate)) {
                    $inspDate = \Carbon\Carbon::parse($inspectionDate);
                    if ($releaseDate->lt($inspDate)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bond Release Date cannot be earlier than Inspection Date',
                            'errors' => ['bond_release_date' => ['Bond Release Date cannot be earlier than Inspection Date']]
                        ], 422);
                    }
                }
            }

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

            // Rule 3: Check for complete bond release (auto-close when all fields are complete)
            if ($inspectorNote === 'For Bond Release') {
                // Check if all inspector section fields are complete
                $inspector = $validatedData['inspector'] ?? null;
                $inspectionDate = $validatedData['inspection_date'] ?? null;
                
                $allInspectorFieldsComplete = !empty($inspector) && 
                                            !empty($inspectorNote) && 
                                            !empty($inspectionDate) && 
                                            !empty($bondReceiver) && 
                                            !empty($bondReleaseDate) && 
                                            !empty($bondReleaseType);
                
                if ($allInspectorFieldsComplete) {
                    $statusType = 5; // Close (Bond Released)
                    Log::info('Auto-setting status to Close (Bond Released) - all inspector fields complete', [
                        'permit_no' => $validatedData['permit_number'],
                        'inspector' => $inspector,
                        'inspector_note' => $inspectorNote,
                        'inspection_date' => $inspectionDate,
                        'bond_receiver' => $bondReceiver,
                        'bond_release_date' => $bondReleaseDate,
                        'bond_release_type' => $bondReleaseType
                    ]);
                }
            }

            // Rule 4: Check if inspection form is created for On-Going permits
            // If inspection_form checkbox is checked and permit was On-Going, change to For Inspection
            // Only applies if inspector_note is not set (inspector_note has higher priority)
            if ($request->has('inspection_form') &&
                $existingPermit->status_type == 1 &&
                empty($validatedData['inspector_note'])) {
                $statusType = 2; // For Inspection
                Log::info('Auto-setting status to For Inspection - inspection form created', [
                    'permit_no' => $validatedData['permit_number'],
                    'original_status' => $existingPermit->status_type,
                    'new_status' => $statusType,
                    'inspection_form' => 1
                ]);
            }

            // Rule 5: Status reversion logic when fields are cleared or note changes
            // If current permit was status 5 (Close - Bond Released) but conditions no longer met, revert status
            if ($existingPermit->status_type == 5) {
                $inspector = $validatedData['inspector'] ?? null;
                $inspectionDate = $validatedData['inspection_date'] ?? null;
                
                // Check if inspector fields are no longer complete or note changed
                $allInspectorFieldsComplete = !empty($inspector) && 
                                            !empty($inspectorNote) && 
                                            !empty($inspectionDate) && 
                                            !empty($bondReceiver) && 
                                            !empty($bondReleaseDate) && 
                                            !empty($bondReleaseType);
                
                // If fields are incomplete or note changed from "For Bond Release", revert status
                if (!$allInspectorFieldsComplete || $inspectorNote !== 'For Bond Release') {
                    if ($inspectorNote === 'For Bond Forfeiture') {
                        $statusType = 4; // Close (Forfeited Bond)
                    } elseif ($inspectorNote === 'For Bond Release') {
                        $statusType = 3; // For Bond Release (incomplete)
                    } else {
                        $statusType = 1; // On-Going (if note is empty or other)
                    }
                    
                    Log::info('Reverting status from Close (Bond Released) due to field changes', [
                        'permit_no' => $validatedData['permit_number'],
                        'original_status' => $existingPermit->status_type,
                        'new_status' => $statusType,
                        'inspector_note' => $inspectorNote,
                        'all_fields_complete' => $allInspectorFieldsComplete
                    ]);
                }
            }

            // Final status determination log
            Log::info('Final status determined for permit update', [
                'permit_no' => $validatedData['permit_number'],
                'original_status' => $existingPermit->status_type,
                'final_status' => $statusType,
                'inspection_form_checked' => $request->has('inspection_form'),
                'inspection_form_value' => $request->has('inspection_form') ? 1 : 0,
                'inspector_note' => $validatedData['inspector_note'] ?? null,
                'end_date_extended' => $newEndDate->gt($originalEndDateCarbon)
            ]);


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
                'inspection_form' => $request->has('inspection_form') ? 1 : 0,
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

    public function getNextPermitNumber(): JsonResponse
    {
        try {
            // Get current year and month
            $currentDate = now();
            $year = $currentDate->format('y'); // Last 2 digits of year (e.g., 25 for 2025)
            $month = $currentDate->format('m'); // Month with leading zero (01-12)
            
            // Create the YYMM prefix
            $prefix = $year . $month;
            
            // Query ViewConstructionPermit for the maximum permit number with current YYMM prefix
            $maxPermitNo = ViewConstructionPermit::whereBetween('permit_no', [
                intval($prefix . '00'), // Start of range (e.g., 250100)
                intval($prefix . '99')  // End of range (e.g., 250199)
            ])->max('permit_no');
            
            // Calculate the next permit number
            if ($maxPermitNo) {
                // Extract the last 2 digits (NN part) and increment
                $lastSequence = intval(substr(strval($maxPermitNo), -2));
                $nextSequence = $lastSequence + 1;
                
                // Check if we've exceeded 99 permits for this month
                if ($nextSequence > 99) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maximum permits (99) reached for this month.'
                    ], 422);
                }
            } else {
                // No permits found for this month, start with 01
                $nextSequence = 1;
            }
            
            // Format the next permit number as YYMMNN
            $nextPermitNumber = intval($prefix . sprintf('%02d', $nextSequence));
            
            Log::info('Next permit number generated', [
                'prefix' => $prefix,
                'max_existing' => $maxPermitNo,
                'next_sequence' => $nextSequence,
                'next_permit_number' => $nextPermitNumber,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'permit_number' => $nextPermitNumber,
                'formatted_number' => strval($nextPermitNumber),
                'year' => $year,
                'month' => $month,
                'sequence' => sprintf('%02d', $nextSequence)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error generating next permit number', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the permit number. Please try again.'
            ], 500);
        }
    }

    /**
     * Get construction permit status data with various filter options
     */
    public function getPermitStatusData(Request $request)
    {
        $filterType = $request->input('filter_type', 'all');
        $countOnly = $request->input('count_only', 0);
        
        $query = ViewConstructionPermit::query();
        
        // Apply filters based on filter type
        switch ($filterType) {
            case 'all':
                // No additional filtering needed
                break;
                
            case 'permit_id':
                $permitId = $request->input('permit_id');
                if ($permitId) {
                    $query->where('permit_no', $permitId);
                }
                break;
                
            case 'address_id':
                $addressId = $request->input('address_id');
                Log::info('Address ID Filter: ' . $addressId);
                if ($addressId) {
                    $query->where('hoa_address_id', $addressId);
                }
                break;
                
            case 'status':
                $status = $request->input('status');
                if ($status) {
                    $query->where('statuscode', $status);
                }
                break;
                
            // Legacy support for old status parameter
            default:
                $status = $request->input('status', 'all');
                if ($status !== 'all') {
                    $query->where('statuscode', $status);
                }
                break;
        }
        
        if ($countOnly) {
            $count = $query->count();
            return response()->json(['count' => $count]);
        }
        
        $permits = $query->orderBy('permit_no', 'desc')->get();
        
        return response()->json($permits);
    }

    /**
     * Download construction permit status data as CSV
     */
    public function downloadPermitStatusData(Request $request)
    {
        $filterType = $request->input('filter_type', 'all');
        
        $query = ViewConstructionPermit::query();
        
        // Apply filters based on filter type (same logic as getPermitStatusData)
        switch ($filterType) {
            case 'all':
                // No additional filtering needed
                break;
                
            case 'permit_id':
                $permitId = $request->input('permit_id');
                if ($permitId) {
                    $query->where('permit_no', $permitId);
                }
                break;
                
            case 'address_id':
                $addressId = $request->input('address_id');
                if ($addressId) {
                    $query->where('HOA Address ID.', $addressId);
                }
                break;
                
            case 'status':
                $status = $request->input('status');
                if ($status) {
                    $query->where('statuscode', $status);
                }
                break;
                
            // Legacy support for old status parameter
            default:
                $status = $request->input('status', 'all');
                if ($status !== 'all') {
                    $query->where('statuscode', $status);
                }
                break;
        }
        
        $permits = $query->orderBy('permit_no', 'desc')->get();

        // Create CSV content with the 28 columns (including No., User Fullname, and Time Entry)
        $headers = [
            'No.', 'Permit No.', 'Permit Type', 'Permit Status', 'Permit Start Date', 'Permit End Date',
            'HOA Address ID', 'HOA Name', 'Application Date', 'Applicant Name', 'Applicant Contact',
            'Contractor Name', 'Contractor Contact', 'Payment SIN', 'SIN Date', 'Fee Amount',
            'Bond ARN', 'Bond Amount', 'Bond Date', 'Inspector', 'Inspection Date',
            'Inspector Note', 'Bond Release Type', 'Bond Receiver', 'Bond Release Date', 'Remarks',
            'User Fullname', 'Time Entry'
        ];
        
        $csv = implode(',', $headers) . "\n";
        
        foreach ($permits as $permit) {
            $row = [
                $permit->{'No.'} ?? '',
                '"' . str_replace('"', '""', $permit->{'Permit No.'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Permit Type'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Permit Status'} ?? '') . '"',
                $permit->{'Permit Start Date'} ?? '',
                $permit->{'Permit End Date'} ?? '',
                '"' . str_replace('"', '""', $permit->{'HOA Address ID.'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'HOA Name'} ?? '') . '"',
                $permit->ApplicationDate ?? '',
                '"' . str_replace('"', '""', $permit->{'Applicant Name'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Applicant Contact'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Contractor Name'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Contractor Contact'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Payment SIN'} ?? '') . '"',
                $permit->{'SIN Date'} ?? '',
                $permit->{'Fee Amt.'} ?? '',
                '"' . str_replace('"', '""', $permit->{'Bond ARN'} ?? '') . '"',
                $permit->{'Bond Amt.'} ?? '',
                $permit->{'Bond Date'} ?? '',
                '"' . str_replace('"', '""', $permit->Inspector ?? '') . '"',
                $permit->{'Inspection Date'} ?? '',
                '"' . str_replace('"', '""', $permit->{'Inspector Note'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Bond Release Type'} ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'Bond Receiver'} ?? '') . '"',
                $permit->{'Bond Release Date'} ?? '',
                '"' . str_replace('"', '""', $permit->Remarks ?? '') . '"',
                '"' . str_replace('"', '""', $permit->{'User Fullname'} ?? '') . '"',
                $permit->{'Time Entry'} ?? ''
            ];
            
            $csv .= implode(',', $row) . "\n";
        }
        
        // Generate filename based on filter type and current date
        $filterText = $this->getFilterTextForFilename($filterType, $request);
        $filename = 'construction_permits_' . $filterText . '_' . date('Y-m-d') . '.csv';
        
        // Create download response
        return response()->make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Helper method to generate filter text for filename
     */
    private function getFilterTextForFilename($filterType, $request)
    {
        switch ($filterType) {
            case 'all':
                return 'all';
            case 'permit_id':
                $permitId = $request->input('permit_id');
                return 'permit_' . ($permitId ?: 'id');
            case 'address_id':
                $addressId = $request->input('address_id');
                return 'address_' . ($addressId ?: 'id');
            case 'status':
                $status = $request->input('status');
                return 'status_' . ($status ?: 'filtered');
            default:
                return 'filtered';
        }
    }

    /**
     * Get permit counts grouped by status type
     */
    public function getPermitStatusCounts()
    {
        try {
            // Get counts for each status type from the view
            $statusCounts = DB::table('vw_construction_permit')
                ->select('statuscode', DB::raw('count(*) as count'))
                ->groupBy('statuscode')
                ->orderBy('statuscode')
                ->get()
                ->keyBy('statuscode');

            // Define status mapping
            $statusMap = [
                1 => ['name' => 'On-Going', 'color' => 'primary'],
                2 => ['name' => 'For Inspection', 'color' => 'warning'],
                3 => ['name' => 'For Bond Release', 'color' => 'info'],
                4 => ['name' => 'Close (Forfeited Bond)', 'color' => 'danger'],
                5 => ['name' => 'Close (Bond Released)', 'color' => 'success']
            ];

            $counts = [];
            $totalCount = 0;

            // Build response with counts for each status
            foreach ($statusMap as $statusId => $statusInfo) {
                $count = $statusCounts->has($statusId) ? $statusCounts[$statusId]->count : 0;
                $counts[] = [
                    'status_id' => $statusId,
                    'status_name' => $statusInfo['name'],
                    'count' => $count,
                    'color' => $statusInfo['color']
                ];
                $totalCount += $count;
            }

            return response()->json([
                'success' => true,
                'total_count' => $totalCount,
                'status_counts' => $counts
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting permit status counts', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching status counts.'
            ], 500);
        }
    }
}