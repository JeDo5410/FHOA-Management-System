<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPermit;
use App\Models\PermitType;
use App\Models\StatusType;
use App\Models\MemberSum;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConstructionPermitController extends Controller
{
    public function index()
    {
        $permitTypes = PermitType::orderBy('typecode')->get();

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
}