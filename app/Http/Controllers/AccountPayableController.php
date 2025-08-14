<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\ApDetail;
use App\Models\ChartOfAccount;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountPayableController extends Controller
{
    public function index()
    {
        // Get only account types that contain 'expense' in the acct_type field
        $accountTypes = ChartOfAccount::where('acct_type', 'like', '%expense%')
            ->orderBy('acct_description')
            ->get();
            
        return view('accounts.payables', compact('accountTypes'));
    }

    /**
     * Process the form submission from the main store endpoint
     */
    public function store(Request $request)
    {
        // Check what type of operation this is
        $isReversal = false;
        $isEdit = false;
        
        // Check for edit flag
        if ($request->has('voucher_edit')) {
            $isEdit = true;
        }
        
        // Check for cancellation flag
        if ($request->has('voucher_cancellation')) {
            $isReversal = true;
        }
        
        // Check remarks for operation type (fallback)
        if (!$isEdit && !$isReversal && $request->has('remarks')) {
            $remarks = trim($request->input('remarks'));
            if (stripos($remarks, 'CANCELLED VOUCHER') === 0) {
                $isReversal = true;
            } elseif (stripos($remarks, 'EDITED FROM VOUCHER') === 0) {
                $isEdit = true;
            }
        }

        // Route to the appropriate method
        if ($isEdit) {
            return $this->storePayableEdit($request);
        } elseif ($isReversal) {
            return $this->storePayableReversal($request);
        } else {
            return $this->storePayable($request);
        }
    }

    /**
     * Store a regular account payable record
     */
    private function storePayable(Request $request)
    {
        try {
            // Validate the form
            $validated = $request->validate([
                'voucher_no' => 'required|string|max:50',
                'payee' => 'required|string|max:100', 
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.particular' => 'required|string|max:100',
                'items.*.amount' => 'required|numeric|min:0.01',
                'items.*.account_type' => 'required|integer|exists:charts_of_account,acct_type_id',
                'total_amount' => 'required|numeric|min:0.01',
                'payment_mode' => 'required|in:PETTY CASH,CASH,GCASH,CHECK',
                'reference_no' => 'nullable|string|max:50',
                'remarks' => 'nullable|string|max:100'
            ]);

            DB::beginTransaction();

            // Create Account Payable record
            $accountPayable = AccountPayable::create([
                'ap_voucherno' => $validated['voucher_no'],
                'ap_date' => $validated['date'],
                'ap_payee' => strtoupper($validated['payee']),
                'ap_paytype' => $validated['payment_mode'],
                'paytype_reference' => $validated['reference_no'] ?? null,
                'ap_total' => $validated['total_amount'],
                'remarks' => $validated['remarks'] ?? null,
                'user_id' => FacadesAuth::id()
            ]);

            // Create AP Details records
            foreach ($validated['items'] as $item) {
                ApDetail::create([
                    'ap_transno' => $accountPayable->ap_transno,
                    'ap_particular' => $item['particular'],
                    'ap_amount' => $item['amount'],
                    'acct_type_id' => $item['account_type']
                ]);
            }

            DB::commit();
            return redirect()->route('accounts.payables')->with('success', 'Account Payable created successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating account payable: ' . $e->getMessage());
            return back()->with('error', 'Error creating account payable: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Check if a voucher number exists and return its details
     */
    public function checkVoucher($voucherNumber)
    {
        try {
            // Find the most recent transaction by voucher number
            $transaction = AccountPayable::where('ap_voucherno', $voucherNumber)
                ->orderBy('ap_transno', 'desc')
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Voucher not found'
                ]);
            }
            
            // Check if this is the most recent non-cancelled transaction
            if (str_contains($transaction->remarks ?? '', 'SYSTEM CANCELLATION FOR EDIT')) {
                // Find the actual edited transaction (should be the next one created)
                $actualTransaction = AccountPayable::where('ap_voucherno', $voucherNumber)
                    ->where('ap_total', '>', 0) // Positive amount = actual transaction
                    ->orderBy('ap_transno', 'desc')
                    ->first();
                    
                if ($actualTransaction) {
                    $transaction = $actualTransaction;
                }
            }
            
            // Get line items for this transaction
            $lineItems = ApDetail::where('ap_transno', $transaction->ap_transno)
                ->join('charts_of_account', 'ap_details.acct_type_id', '=', 'charts_of_account.acct_type_id')
                ->select(
                    'ap_details.*',
                    'charts_of_account.acct_description',
                    'charts_of_account.acct_name',
                    'charts_of_account.acct_type'
                )
                ->get()
                ->toArray();
            
            return response()->json([
                'exists' => true,
                'transaction' => $transaction,
                'line_items' => $lineItems,
                'can_edit' => true,
                'message' => 'Transaction found'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking voucher: ' . $e->getMessage());
            
            return response()->json([
                'exists' => false,
                'message' => 'Error checking voucher: ' . $e->getMessage() . '. At line No. ' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Store an edit transaction for account payable
     */
    private function storePayableEdit(Request $request)
    {
        try {
            // Validate the form
            $validated = $request->validate([
                'voucher_no' => 'required|string|max:50',
                'payee' => 'required|string|max:100', 
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.particular' => 'required|string|max:100',
                'items.*.amount' => 'required|numeric|min:0.01',
                'items.*.account_type' => 'required|integer|exists:charts_of_account,acct_type_id',
                'total_amount' => 'required|numeric|min:0.01',
                'payment_mode' => 'required|in:PETTY CASH,CASH,GCASH,CHECK',
                'reference_no' => 'nullable|string|max:50',
                'remarks' => 'required|string|max:150'
            ]);

            DB::beginTransaction();
            
            // Get the original transaction that we're editing
            $originalVoucherNumber = $validated['voucher_no'];
            $originalTransaction = AccountPayable::where('ap_voucherno', $originalVoucherNumber)
                ->first();
                
            if (!$originalTransaction) {
                throw new \Exception('Original transaction not found');
            }

            // Get original line items
            $originalLineItems = ApDetail::where('ap_transno', $originalTransaction->ap_transno)->get();
            
            // STEP 1: Create cancellation entry for original transaction
            $cancellationTransaction = AccountPayable::create([
                'ap_voucherno' => $originalVoucherNumber,
                'ap_date' => now()->format('Y-m-d'),
                'ap_payee' => $originalTransaction->ap_payee,
                'ap_paytype' => $originalTransaction->ap_paytype,
                'paytype_reference' => $originalTransaction->paytype_reference,
                'ap_total' => -abs($originalTransaction->ap_total), // Negative amount
                'remarks' => "SYSTEM CANCELLATION FOR EDIT - Original Voucher: {$originalVoucherNumber}",
                'user_id' => FacadesAuth::id()
            ]);

            // Create cancellation line items (negative amounts)
            foreach ($originalLineItems as $originalItem) {
                ApDetail::create([
                    'ap_transno' => $cancellationTransaction->ap_transno,
                    'ap_particular' => $originalItem->ap_particular,
                    'ap_amount' => -abs($originalItem->ap_amount),
                    'acct_type_id' => $originalItem->acct_type_id
                ]);
            }
            
            // STEP 2: Create new transaction with updated details
            $editedTransaction = AccountPayable::create([
                'ap_voucherno' => $originalVoucherNumber, // Keep same voucher number
                'ap_date' => $validated['date'],
                'ap_payee' => strtoupper($validated['payee']),
                'ap_paytype' => $validated['payment_mode'],
                'paytype_reference' => $validated['reference_no'] ?? null,
                'ap_total' => $validated['total_amount'],
                'remarks' => $validated['remarks'],
                'user_id' => FacadesAuth::id()
            ]);

            // Create new line items
            foreach ($validated['items'] as $item) {
                ApDetail::create([
                    'ap_transno' => $editedTransaction->ap_transno,
                    'ap_particular' => $item['particular'],
                    'ap_amount' => $item['amount'],
                    'acct_type_id' => $item['account_type']
                ]);
            }
            
            DB::commit();
            return redirect()->route('accounts.payables')
                ->with('success', "Voucher #{$originalVoucherNumber} has been successfully updated");
                        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error editing account payable: ' . $e->getMessage());
            return back()->with('error', 'Error editing account payable: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store a reversal transaction for account payable
     */
    private function storePayableReversal(Request $request)
    {
        try {
            // Validate the form
            $validated = $request->validate([
                'voucher_no' => 'required|string|max:50',
                'payee' => 'required|string|max:100', 
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.particular' => 'required|string|max:100',
                'items.*.amount' => 'required|numeric', // Allow negative amounts
                'items.*.account_type' => 'required|integer|exists:charts_of_account,acct_type_id',
                'total_amount' => 'required|numeric', // Allow negative amounts
                'payment_mode' => 'required|in:PETTY CASH,CASH,GCASH,CHECK',
                'reference_no' => 'nullable|string|max:50',
                'remarks' => 'required|string|max:150'
            ]);

            DB::beginTransaction();

            // Create Account Payable reversal record with negative amounts
            $accountPayable = AccountPayable::create([
                'ap_voucherno' => $validated['voucher_no'],
                'ap_date' => $validated['date'],
                'ap_payee' => strtoupper($validated['payee']),
                'ap_paytype' => $validated['payment_mode'],
                'paytype_reference' => $validated['reference_no'] ?? null,
                'ap_total' => $validated['total_amount'], // Should be negative
                'remarks' => $validated['remarks'],
                'user_id' => FacadesAuth::id()
            ]);

            // Create AP Details records with negative amounts
            foreach ($validated['items'] as $item) {
                ApDetail::create([
                    'ap_transno' => $accountPayable->ap_transno,
                    'ap_particular' => $item['particular'],
                    'ap_amount' => $item['amount'], // Should be negative
                    'acct_type_id' => $item['account_type']
                ]);
            }

            DB::commit();
            return redirect()->route('accounts.payables')
                ->with('success', 'Voucher #' . $validated['voucher_no'] . ' has been successfully cancelled');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cancelling account payable: ' . $e->getMessage());
            return back()->with('error', 'Error cancelling account payable: ' . $e->getMessage())->withInput();
        }
    }
}