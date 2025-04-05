<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\AcctReceivable;
use App\Models\ArDetail;
use App\Models\MemberSum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountReceivableController extends Controller
{
    public function index()
    {
        // For Account Receivable tab: All Association Receipts EXCEPT Association Dues
        $accountTypes = ChartOfAccount::where('acct_type', 'LIKE', '%Receipts%')
        ->where('acct_name', '!=', 'Association Dues')
        ->orderBy('acct_description')
        ->get();
                    
        // For HOA Monthly Dues tab: ONLY Association Dues
        $duesAccountTypes = ChartOfAccount::where('acct_type', 'Association Receipts')
            ->where('acct_name', 'Association Dues')
            ->orderBy('acct_description')
            ->get();
            
        return view('accounts.receivable', compact('accountTypes', 'duesAccountTypes'));
    }

    /**
     * Process the form submission from the main store endpoint
     */
    public function store(Request $request)
    {
        // Check if this is a reversal (based on remarks containing "CANCELLED OR")
        $isReversal = false;
        
        if ($request->has('remarks')) {
            $remarks = trim($request->input('remarks'));
            if (stripos($remarks, 'CANCELLED SIN') === 0) {
                $isReversal = true;
            }
        }
        
        if ($request->has('arrears_remarks')) {
            $remarks = trim($request->input('arrears_remarks'));
            if (stripos($remarks, 'CANCELLED SIN') === 0) {
                $isReversal = true;
            }
        }

        // Determine which form was submitted and route to the appropriate method
        $formType = $request->input('form_type');
        
        if ($formType === 'account_receivable') {
            return $isReversal 
                ? $this->storeAccountReceivableReversal($request)
                : $this->storeAccountReceivable($request);
        } elseif ($formType === 'arrears_receivable') {
            return $isReversal
                ? $this->storeArrearsReceivableReversal($request)
                : $this->storeArrearsReceivable($request);
        }
        
        // Invalid form type
        return back()->with('error', 'Invalid form type');


        // At the top of your store method
        Log::debug('Store method called', [
            'form_type' => $request->input('form_type'),
            'has_remarks' => $request->has('remarks'),
            'remarks' => $request->input('remarks'),
            'has_arrears_remarks' => $request->has('arrears_remarks'),
            'arrears_remarks' => $request->input('arrears_remarks'),
            'detected_as_reversal' => $isReversal
        ]);

        // Then in each method add:
        // In storeAccountReceivableReversal
        Log::debug('Account Receivable Reversal method called');

        // In storeArrearsReceivableReversal
        Log::debug('Arrears Receivable Reversal method called');
        
        // Invalid form type
        return back()->with('error', 'Invalid form type');
    }
    
    /**
     * Store an account receivable record (first tab)
     */
    private function storeAccountReceivable(Request $request)
    {
        try {
            // Validate the Account Receivable form
            $validated = $request->validate([
                'address' => 'required|string|max:100',
                'received_from' => 'required|string|max:45',
                'service_invoice_no' => 'required|integer',
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'items.*.amount' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'received_by' => 'required|string|max:45',
                'payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
                'reference_no' => 'nullable|string|max:45',
                'remarks' => 'nullable|string|max:45'
            ]);
    
            // Begin transaction for data integrity
            DB::beginTransaction();
            
            // Create a separate transaction record for each line item
            foreach ($validated['items'] as $item) {
                $accountReceivable = new AcctReceivable();
                $accountReceivable->or_number = $validated['service_invoice_no'];
                $accountReceivable->ar_date = $validated['date'];
                $accountReceivable->ar_amount = $item['amount'];
                $accountReceivable->acct_type_id = $item['coa'];
                $accountReceivable->payor_name = strtoupper($validated['received_from']);
                $accountReceivable->payor_address = $validated['address'];
                $accountReceivable->payment_type = $validated['payment_mode'];
                $accountReceivable->payment_Ref = $validated['reference_no'] ?? null; // Note the capital R in Ref
                $accountReceivable->receive_by = $validated['received_by'];
                $accountReceivable->ar_remarks = $validated['remarks'] ?? null;
                $accountReceivable->user_id = Auth::id(); // Get the currently logged-in user's ID
                $accountReceivable->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Return success response with toast notification
            return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'account')])
                ->with('success', 'Account receivable created successfully');
                        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error, no need to rollback as no DB operations were performed
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            // Log the error
            Log::error('Error creating account receivable: ' . $e->getMessage());
            
            // Return error response with toast notification
            return back()->with('error', 'Error creating account receivable: ' . $e->getMessage())
                ->withInput();
        }
    }    
    
    /**
     * Store an arrears receivable record (HOA Monthly Dues tab)
     */
    private function storeArrearsReceivable(Request $request)
    {
        try {
            // Validate the HOA Monthly Dues form
            $validated = $request->validate([
                'arrears_address_id' => 'required|string|max:5',
                'arrears_received_from' => 'required|string|max:45',
                'arrears_service_invoice_no' => 'required|integer',
                'arrears_date' => 'required|date',
                'arrears_items' => 'required|array',
                'arrears_items.0.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'arrears_items.0.amount' => 'required|numeric|min:0.01',
                'arrears_received_by' => 'required|string|max:45',
                'arrears_payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
                'arrears_reference_no' => 'nullable|string|max:45',
                'arrears_remarks' => 'nullable|string|max:45'
            ]);

            // Begin transaction for data integrity
            DB::beginTransaction();
            
            // Find the member record using the address ID
            $memberSum = MemberSum::where('mem_add_id', $validated['arrears_address_id'])->first();
            
            if (!$memberSum) {
                throw new \Exception('Member not found with the provided Address ID');
            }
            
            // Get payment amount from the single line item
            $paymentAmount = $validated['arrears_items'][0]['amount'];
            
            // Get the current arrear balance
            $currentArrear = $memberSum->arrear ?? 0;
            
            // Calculate the new arrear balance
            $newArrearBalance = $currentArrear - $paymentAmount;
            
            // Create a new acct_receivable record for this payment
            $accountReceivable = new AcctReceivable();
            $accountReceivable->mem_id = $memberSum->mem_id;
            $accountReceivable->or_number = $validated['arrears_service_invoice_no'];
            $accountReceivable->ar_date = $validated['arrears_date'];
            $accountReceivable->ar_amount = $paymentAmount;
            $accountReceivable->arrear_bal = $newArrearBalance; // The running balance after this payment
            $accountReceivable->acct_type_id = $validated['arrears_items'][0]['coa'];
            $accountReceivable->payor_name = strtoupper($validated['arrears_received_from']);
            $accountReceivable->payor_address = $validated['arrears_address_id']; // Use the address ID
            $accountReceivable->payment_type = $validated['arrears_payment_mode'];
            $accountReceivable->payment_Ref = $validated['arrears_reference_no'] ?? null; // Note the exact casing from model
            $accountReceivable->receive_by = $validated['arrears_received_by'];
            $accountReceivable->ar_remarks = $validated['arrears_remarks'] ?? null;
            $accountReceivable->user_id = Auth::id(); // Get the currently logged-in user's ID
            
            $accountReceivable->save();
            
            // Update member_sum record with the payment information
            $memberSum->last_or = $validated['arrears_service_invoice_no'];
            $memberSum->last_paydate = $validated['arrears_date'];
            $memberSum->last_payamount = $paymentAmount;
            $memberSum->arrear = $newArrearBalance; // Update with new arrear balance (can be negative)
            $memberSum->user_id = Auth::id();
            $memberSum->save();
            
            // Commit the transaction
            DB::commit();
            
            // Return success response with toast notification
            return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'arrears')])
                ->with('success', 'HOA Monthly Dues payment recorded successfully');
                        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error, no need to rollback as no DB operations were performed
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            // Log the error
            Log::error('Error creating HOA monthly dues record: ' . $e->getMessage());
            
            // Return error response with toast notification
            return back()->with('error', 'Error creating HOA monthly dues record: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get payment history for a member with type 101
     */
    public function getPaymentHistory($memberId)
    {
        try {
            // Get all payments for this member with account type "Association Receipts" and account name "Association Dues"
            $payments = AcctReceivable::where('acct_receivable.mem_id', $memberId)
                ->join('charts_of_account', 'acct_receivable.acct_type_id', '=', 'charts_of_account.acct_type_id')
                ->where('charts_of_account.acct_type', 'Association Receipts')
                ->where('charts_of_account.acct_name', 'Association Dues')
                ->orderBy('acct_receivable.ar_date', 'desc')
                ->select(
                    'acct_receivable.ar_transno',
                    'acct_receivable.ar_date',
                    'acct_receivable.ar_amount',
                    'acct_receivable.or_number',
                    'acct_receivable.arrear_bal',
                    'acct_receivable.ar_remarks',
                    'acct_receivable.payor_name',
                    'charts_of_account.acct_description'
                )
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $payments,
                'message' => 'Payment history retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment history: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if an invoice number exists and return its details
     */
    public function checkInvoice($invoiceNumber)
    {
        try {
            // Find the transaction by invoice number (or_number)
            $transaction = AcctReceivable::where('or_number', $invoiceNumber)->first();
            
            if (!$transaction) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Invoice not found'
                ]);
            }
            
            // For account receivable transactions, we need to get all line items
            $lineItems = null;
            
            // Determine transaction type based on mem_id (most reliable indicator)
            $isArrears = !empty($transaction->mem_id);
            
            // Double-check with account type for extra validation
            $accountTypeMatch = ChartOfAccount::where('acct_type_id', $transaction->acct_type_id)
                ->where('acct_type', 'Association Receipts')
                ->where('acct_name', 'Association Dues')
                ->exists();
            
            // If there's a discrepancy, log it for investigation
            if ($isArrears !== $accountTypeMatch) {
                Log::warning('Transaction type mismatch detected', [
                    'or_number' => $invoiceNumber,
                    'mem_id_exists' => !empty($transaction->mem_id),
                    'account_type_match' => $accountTypeMatch
                ]);
            }
            
            // For regular account receivables, get all line items with account type info
            if (!$isArrears) {
                // Get all line items for this invoice number (for account receivable)
                // Join with charts_of_account to get descriptions
                $lineItems = AcctReceivable::where('acct_receivable.or_number', $invoiceNumber)
                    ->join('charts_of_account', 'acct_receivable.acct_type_id', '=', 'charts_of_account.acct_type_id')
                    ->select(
                        'acct_receivable.*',
                        'charts_of_account.acct_description',
                        'charts_of_account.acct_name',
                        'charts_of_account.acct_type'
                    )
                    ->get()
                    ->toArray();
            }

            // Add debug logging
            Log::debug('Invoice lookup details', [
                'or_number' => $invoiceNumber,
                'transaction_found' => isset($transaction),
                'is_arrears' => $isArrears,
                'line_items_count' => $lineItems ? count($lineItems) : 0,
                'first_line_item' => $lineItems && count($lineItems) > 0 ? array_keys($lineItems[0]) : []
            ]);
            
            // Return the transaction details
            return response()->json([
                'exists' => true,
                'transaction' => $transaction,
                'line_items' => $lineItems,
                'is_arrears' => $isArrears,
                'tab_type' => $isArrears ? 'arrears' : 'account',
                'message' => 'Transaction found'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking invoice: ' . $e->getMessage());
            
            return response()->json([
                'exists' => false,
                'message' => 'Error checking invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a reversal transaction for account receivable
     */
    private function storeAccountReceivableReversal(Request $request)
    {

        Log::debug('Account Receivable Reversal method called');

        try {
            // Validate the Account Receivable form
            $validated = $request->validate([
                'address' => 'required|string|max:100',
                'received_from' => 'required|string|max:45',
                'service_invoice_no' => 'required|integer',
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'items.*.amount' => 'required|numeric', // Allow negative amounts
                'total_amount' => 'required|numeric',   // Allow negative amounts
                'received_by' => 'required|string|max:45',
                'payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
                'reference_no' => 'nullable|string|max:45',
                'remarks' => 'required|string|max:100'  // Increased max length to accommodate "CANCELLED OR: "
            ]);

            // Begin transaction for data integrity
            DB::beginTransaction();
            
            // Create a separate transaction record for each line item (with negative amounts)
            foreach ($validated['items'] as $item) {
                $accountReceivable = new AcctReceivable();
                $accountReceivable->or_number = $validated['service_invoice_no']; 
                $accountReceivable->ar_date = $validated['date'];
                $accountReceivable->ar_amount = $item['amount']; // Should already be negative
                $accountReceivable->acct_type_id = $item['coa'];
                $accountReceivable->payor_name = strtoupper($validated['received_from']);
                $accountReceivable->payor_address = $validated['address'];
                $accountReceivable->payment_type = $validated['payment_mode'];
                $accountReceivable->payment_Ref = $validated['reference_no'] ?? null;
                $accountReceivable->receive_by = $validated['received_by'];
                $accountReceivable->ar_remarks = $validated['remarks'];
                $accountReceivable->user_id = Auth::id();
                $accountReceivable->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Return success response with toast notification
            return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'account')])
                ->with('success', 'Account receivable #' . $validated['service_invoice_no'] . ' has been successfully reversed');
                        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error, no need to rollback as no DB operations were performed
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            // Log the error
            Log::error('Error reversing account receivable: ' . $e->getMessage());
            
            // Return error response with toast notification
            return back()->with('error', 'Error reversing account receivable: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store a reversal transaction for arrears receivable
     */
    private function storeArrearsReceivableReversal(Request $request)
    {

        Log::debug('Arrears Receivable Reversal method called');

        try {
            // Validate the HOA Monthly Dues form
            $validated = $request->validate([
                'arrears_address_id' => 'required|string|max:5',
                'arrears_received_from' => 'required|string|max:45',
                'arrears_service_invoice_no' => 'required|integer',
                'arrears_date' => 'required|date',
                'arrears_items' => 'required|array',
                'arrears_items.0.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'arrears_items.0.amount' => 'required|numeric', // Allow negative amounts
                'arrears_received_by' => 'required|string|max:45',
                'arrears_payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
                'arrears_reference_no' => 'nullable|string|max:45',
                'arrears_remarks' => 'required|string|max:100' // Increased max length to accommodate "CANCELLED OR: "
            ]);

            // Begin transaction for data integrity
            DB::beginTransaction();
            
            // Find the member record using the address ID
            $memberSum = MemberSum::where('mem_add_id', $validated['arrears_address_id'])->first();
            
            if (!$memberSum) {
                throw new \Exception('Member not found with the provided Address ID');
            }
            
            // Get payment amount from the line item (should be negative)
            $reversalAmount = $validated['arrears_items'][0]['amount'];
            
            // Get the current arrear balance
            $currentArrear = $memberSum->arrear ?? 0;
            
            // Calculate the new arrear balance (add the negative reversal amount to increase arrears)
            $newArrearBalance = $currentArrear - $reversalAmount; // Subtracting a negative = adding
            
            // Create a new acct_receivable record for this reversal
            $accountReceivable = new AcctReceivable();
            $accountReceivable->mem_id = $memberSum->mem_id;
            $accountReceivable->or_number = $validated['arrears_service_invoice_no'];
            $accountReceivable->ar_date = $validated['arrears_date'];
            $accountReceivable->ar_amount = $reversalAmount; // Should be negative
            $accountReceivable->arrear_bal = $newArrearBalance; // The new running balance after reversal
            $accountReceivable->acct_type_id = $validated['arrears_items'][0]['coa'];
            $accountReceivable->payor_name = strtoupper($validated['arrears_received_from']);
            $accountReceivable->payor_address = $validated['arrears_address_id'];
            $accountReceivable->payment_type = $validated['arrears_payment_mode'];
            $accountReceivable->payment_Ref = $validated['arrears_reference_no'] ?? null;
            $accountReceivable->receive_by = $validated['arrears_received_by'];
            $accountReceivable->ar_remarks = $validated['arrears_remarks'];
            $accountReceivable->user_id = Auth::id();
            
            $accountReceivable->save();
            
            // Update member_sum record with the new arrear balance
            $memberSum->arrear = $newArrearBalance;
            $memberSum->user_id = Auth::id();
            $memberSum->save();
            
            // Commit the transaction
            DB::commit();
            
            // Return success response with toast notification
            return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'arrears')])
                ->with('success', 'HOA Monthly Dues payment #' . $validated['arrears_service_invoice_no'] . ' has been successfully reversed');
                        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error, no need to rollback as no DB operations were performed
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            // Log the error
            Log::error('Error reversing HOA monthly dues: ' . $e->getMessage());
            
            // Return error response with toast notification
            return back()->with('error', 'Error reversing HOA monthly dues: ' . $e->getMessage())
                ->withInput();
        }
    }
    
}