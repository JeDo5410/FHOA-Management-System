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
        $accountTypes = ChartOfAccount::where('acct_type', 'LIKE', '%Association Receipts%')
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
        // Check what type of operation this is
        $isReversal = false;
        $isEdit = false;
        
        // Check for edit flag
        if ($request->has('sin_edit')) {
            $isEdit = true;
        }
        
        // Check for cancellation flag
        if ($request->has('sin_cancellation')) {
            $isReversal = true;
        }
        
        // Check remarks for operation type (fallback)
        if (!$isEdit && !$isReversal) {
            if ($request->has('remarks')) {
                $remarks = trim($request->input('remarks'));
                if (stripos($remarks, 'CANCELLED SIN') === 0) {
                    $isReversal = true;
                } elseif (stripos($remarks, 'EDITED FROM SIN') === 0) {
                    $isEdit = true;
                }
            }
            
            if ($request->has('arrears_remarks')) {
                $remarks = trim($request->input('arrears_remarks'));
                if (stripos($remarks, 'CANCELLED SIN') === 0) {
                    $isReversal = true;
                } elseif (stripos($remarks, 'EDITED FROM SIN') === 0) {
                    $isEdit = true;
                }
            }
        }

        // Determine which form was submitted and route to the appropriate method
        $formType = $request->input('form_type');
        
        if ($formType === 'account_receivable') {
            if ($isEdit) {
                return $this->storeAccountReceivableEdit($request);
            } elseif ($isReversal) {
                return $this->storeAccountReceivableReversal($request);
            } else {
                return $this->storeAccountReceivable($request);
            }
        } elseif ($formType === 'arrears_receivable') {
            if ($isEdit) {
                return $this->storeArrearsReceivableEdit($request);
            } elseif ($isReversal) {
                return $this->storeArrearsReceivableReversal($request);
            } else {
                return $this->storeArrearsReceivable($request);
            }
        }
        
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
                'service_invoice_no' => 'required|string',
                'date' => 'required|date',
                'items' => 'required|array',
                'items.*.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'items.*.amount' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'received_by' => 'required|string|max:45',
                'payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
                'reference_no' => 'nullable|string|max:45',
                'remarks' => 'nullable|string|max:300'
            ]);

            // Trim leading zeros from SIN before saving
            $sinNumber = (int) ltrim($validated['service_invoice_no'], '0');
    
            // Begin transaction for data integrity
            DB::beginTransaction();
            
            // Create a separate transaction record for each line item
            foreach ($validated['items'] as $item) {
                $accountReceivable = new AcctReceivable();
                $accountReceivable->or_number = $sinNumber;
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
                ->with('success', 'Account receivable created successfully')
                ->with('double_redirect', true);                        
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
     * Store an account receivable edit record (first tab)
     */
    private function storeAccountReceivableEdit(Request $request)
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
                'remarks' => 'nullable|string|max:300'
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
                $accountReceivable->payment_Ref = $validated['reference_no'] ?? null;
                $accountReceivable->receive_by = $validated['received_by'];
                $accountReceivable->ar_remarks = $validated['remarks'] ?? null;
                $accountReceivable->user_id = Auth::id();
                $accountReceivable->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Return success response with toast notification
            return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'account')])
                ->with('success', 'Account receivable updated successfully');
                        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error, no need to rollback as no DB operations were performed
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            // Log the error
            Log::error('Error updating account receivable: ' . $e->getMessage());
            
            // Return error response with toast notification
            return back()->with('error', 'Error updating account receivable: ' . $e->getMessage())
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
                'arrears_service_invoice_no' => 'required|string',
                'arrears_date' => 'required|date',
                'arrears_items' => 'required|array',
                'arrears_items.0.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'arrears_items.0.amount' => 'required|numeric|min:0.01',
                'arrears_received_by' => 'required|string|max:45',
                'arrears_payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
                'arrears_reference_no' => 'nullable|string|max:45',
                'arrears_remarks' => 'nullable|string|max:45'
            ]);

            // *** MODIFICATION: Convert Address ID to formatted string ***
            $formattedAddress = $this->formatAddressId($validated['arrears_address_id']);

            // Trim leading zeros from SIN before saving
            $sinNumber = (int) ltrim($validated['arrears_service_invoice_no'], '0');

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
            $accountReceivable->or_number = $sinNumber;
            $accountReceivable->ar_date = $validated['arrears_date'];
            $accountReceivable->ar_amount = $paymentAmount;
            $accountReceivable->arrear_bal = $newArrearBalance; // The running balance after this payment
            $accountReceivable->acct_type_id = $validated['arrears_items'][0]['coa'];
            $accountReceivable->payor_name = strtoupper($validated['arrears_received_from']);
            $accountReceivable->payor_address = $formattedAddress;
            $accountReceivable->payment_type = $validated['arrears_payment_mode'];
            $accountReceivable->payment_Ref = $validated['arrears_reference_no'] ?? null;
            $accountReceivable->receive_by = $validated['arrears_received_by'];
            $accountReceivable->ar_remarks = $validated['arrears_remarks'] ?? null;
            $accountReceivable->user_id = Auth::id();
            
            $accountReceivable->save();
            
            // Update member_sum record with the payment information
            $memberSum->last_or = $sinNumber;
            $memberSum->last_paydate = $validated['arrears_date'];
            $memberSum->last_payamount = $paymentAmount;
            $memberSum->arrear = $newArrearBalance;
            
            // Log before arrear_total calculation
            Log::info('STORE ARREARS - Before arrear_total calculation', [
                'member_id' => $memberSum->mem_id,
                'current_arrear' => $memberSum->arrear,
                'current_arrear_interest' => $memberSum->arrear_interest,
                'new_arrear_balance' => $newArrearBalance,
                'payment_amount' => $paymentAmount
            ]);
            
            // Calculate and update arrear_total (arrear + arrear_interest)
            $arrearInterest = $memberSum->arrear_interest ?? 0;
            $calculatedArrearTotal = $newArrearBalance + $arrearInterest;
            $memberSum->arrear_total = $calculatedArrearTotal;
            
            // Log after arrear_total calculation
            Log::info('STORE ARREARS - After arrear_total calculation', [
                'member_id' => $memberSum->mem_id,
                'arrear_interest' => $arrearInterest,
                'calculated_arrear_total' => $calculatedArrearTotal,
                'assigned_arrear_total' => $memberSum->arrear_total
            ]);
            
            $memberSum->user_id = Auth::id();
            $memberSum->save();
            
            // Log after saving
            Log::info('STORE ARREARS - After saving to database', [
                'member_id' => $memberSum->mem_id,
                'saved_arrear' => $memberSum->arrear,
                'saved_arrear_total' => $memberSum->arrear_total
            ]);
            
            // Commit the transaction
            DB::commit();
            
            return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'arrears')])
                ->with('success', 'HOA Monthly Dues payment recorded successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating HOA monthly dues record: ' . $e->getMessage());
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
            // Get only the most recent transaction for each unique OR number
            $payments = AcctReceivable::where('acct_receivable.mem_id', $memberId)
                ->join('charts_of_account', 'acct_receivable.acct_type_id', '=', 'charts_of_account.acct_type_id')
                ->where('charts_of_account.acct_type', 'Association Receipts')
                ->where('charts_of_account.acct_name', 'Association Dues')
                ->whereIn('acct_receivable.ar_transno', function($query) use ($memberId) {
                    $query->select(DB::raw('MAX(ar_transno)'))
                          ->from('acct_receivable')
                          ->where('mem_id', $memberId)
                          ->groupBy('or_number');
                })
                ->orderBy('acct_receivable.ar_date', 'desc')
                ->orderBy('acct_receivable.ar_transno', 'desc')
                ->select(
                    'acct_receivable.ar_transno',
                    'acct_receivable.ar_date',
                    'acct_receivable.ar_amount',
                    'acct_receivable.or_number',
                    'acct_receivable.arrear_bal',
                    'acct_receivable.ar_remarks',
                    'acct_receivable.payor_name',
                    'acct_receivable.timestamp',
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
     * Check if an invoice number exists for construction permit and return its details
     */
    public function checkConstructionPermitInvoice($invoiceNumber)
    {
        try {
            // Find the most recent transaction by invoice number with acct_type_id = 108 (Construction fees)
            $transaction = AcctReceivable::where('or_number', $invoiceNumber)
                ->where('acct_type_id', 108)
                ->orderBy('ar_transno', 'desc')
                ->first();
            
            if (!$transaction) {
                // Check if SIN exists but with different account type
                $existingTransaction = AcctReceivable::where('or_number', $invoiceNumber)
                    ->orderBy('ar_transno', 'desc')
                    ->first();
                
                if ($existingTransaction) {
                    return response()->json([
                        'exists' => false,
                        'message' => 'SIN is not a Construction fee type'
                    ]);
                } else {
                    return response()->json([
                        'exists' => false,
                        'message' => 'Permit SIN entered doesn\'t exist'
                    ]);
                }
            }
            
            // Check if this is a cancelled transaction
            if (str_contains($transaction->ar_remarks ?? '', 'SYSTEM CANCELLATION FOR EDIT')) {
                // Find the actual edited transaction (should be the next one created)
                $actualTransaction = AcctReceivable::where('or_number', $invoiceNumber)
                    ->where('acct_type_id', 108)
                    ->where('ar_amount', '>', 0) // Positive amount = actual transaction
                    ->orderBy('ar_transno', 'desc')
                    ->first();
                    
                if ($actualTransaction) {
                    $transaction = $actualTransaction;
                }
            }
            
            return response()->json([
                'exists' => true,
                'transaction' => $transaction,
                'message' => 'Construction permit SIN found'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking construction permit invoice: ' . $e->getMessage());
            
            return response()->json([
                'exists' => false,
                'message' => 'Error checking SIN: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if an invoice number exists and return its details
     */
    public function checkInvoice($invoiceNumber)
    {
        try {
            // Find the most recent transaction by invoice number
            $transaction = AcctReceivable::where('or_number', $invoiceNumber)
                ->orderBy('ar_transno', 'desc')  // ✅ Use ar_transno instead of created_at
                ->first();
            
            if (!$transaction) {
                return response()->json([
                    'exists' => false,
                    'message' => 'SIN not found'
                ]);
            }
            
            // Check if this is the most recent non-cancelled transaction
            if (str_contains($transaction->ar_remarks ?? '', 'SYSTEM CANCELLATION FOR EDIT')) {
                // Find the actual edited transaction (should be the next one created)
                $actualTransaction = AcctReceivable::where('or_number', $invoiceNumber)
                    ->where('ar_amount', '>', 0) // Positive amount = actual transaction
                    ->orderBy('ar_transno', 'desc')  // ✅ Use ar_transno here too
                    ->first();
                    
                if ($actualTransaction) {
                    $transaction = $actualTransaction;
                }
            }
            
            $lineItems = null;
            $isArrears = !empty($transaction->mem_id);
            
            // For regular account receivables, get only the most recent line items
            if (!$isArrears) {
                // Get the timestamp of the most recent transaction to group related line items
                $recentTimestamp = $transaction->timestamp;
                
                // Fetch line items created within 3 seconds of the most recent transaction
                // This groups transactions from the same form submission while handling millisecond differences
                $lineItems = AcctReceivable::where('acct_receivable.or_number', $invoiceNumber)
                    ->where('acct_receivable.ar_amount', '>', 0) // Only positive amounts (actual transactions)
                    ->whereBetween('acct_receivable.timestamp', [
                        date('Y-m-d H:i:s', strtotime($recentTimestamp . ' -3 seconds')),
                        date('Y-m-d H:i:s', strtotime($recentTimestamp . ' +3 seconds'))
                    ])
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
            
            return response()->json([
                'exists' => true,
                'transaction' => $transaction,
                'line_items' => $lineItems,
                'is_arrears' => $isArrears,
                'tab_type' => $isArrears ? 'arrears' : 'account',
                'can_edit' => true,
                'message' => 'Transaction found'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking invoice: ' . $e->getMessage());
            
            return response()->json([
                'exists' => false,
                'message' => 'Error checking SIN: ' . $e->getMessage() . '. At line No. ' . $e->getLine()
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
                'remarks' => 'required|string|max:300'  // Increased max length to accommodate "CANCELLED OR: "
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

            $formattedAddress = $this->formatAddressId($validated['arrears_address_id']);

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
            $accountReceivable->payor_address = $formattedAddress;
            $accountReceivable->payment_type = $validated['arrears_payment_mode'];
            $accountReceivable->payment_Ref = $validated['arrears_reference_no'] ?? null;
            $accountReceivable->receive_by = $validated['arrears_received_by'];
            $accountReceivable->ar_remarks = $validated['arrears_remarks'];
            $accountReceivable->user_id = Auth::id();
            
            $accountReceivable->save();
            
            // Update member_sum record with the new arrear balance
            $memberSum->arrear = $newArrearBalance;
            
            // Calculate and update arrear_total (arrear + arrear_interest)
            $arrearInterest = $memberSum->arrear_interest ?? 0;
            $memberSum->arrear_total = $newArrearBalance + $arrearInterest;
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
    
    /**
     * Get the next Service Invoice Number (SIN) for auto-population
     */
    public function getNextSinNumber()
    {
        try {
            // Use Laravel's Query Builder with proper method chaining
            // This is more Laravel-idiomatic than raw SQL
            $maxOrNumber = AcctReceivable::max('or_number');
            
            // Handle case where no records exist or result is null
            $nextSin = ($maxOrNumber !== null) ? $maxOrNumber + 1 : 1;
            
            // Ensure we have a valid number (minimum 1)
            $nextSin = max($nextSin, 1);

            // Log using Laravel's Log facade with structured data
            Log::info('Next SIN number retrieved successfully', [
                'next_sin' => $nextSin,
                'max_or_number_found' => $maxOrNumber,
                'user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            return response()->json([
                'success' => true,
                'next_sin' => $nextSin,
                'formatted_sin' => str_pad($nextSin, 5, '0', STR_PAD_LEFT),
                'message' => 'Next SIN retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            // Enhanced error logging with more context
            Log::error('Failed to retrieve next SIN number', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'user_id' => Auth::id(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Return a more informative error response
            return response()->json([
                'success' => false,
                'next_sin' => 1,
                'formatted_sin' => '00001',
                'message' => 'Unable to retrieve next SIN number. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store an edit transaction for arrears receivable
     */
private function storeArrearsReceivableEdit(Request $request)
{
    // Add this logging at the very beginning of the method, right after the opening brace
    Log::info('=== ARREARS RECEIVABLE EDIT STARTED ===', [
        'user_id' => Auth::id(),
        'user_name' => Auth::user()->fullname ?? Auth::user()->name,
        'request_data' => $request->all(),
        'timestamp' => now()
    ]);

    try {
        // Validate the HOA Monthly Dues form
        $validated = $request->validate([
            'arrears_address_id' => 'required|string|max:5',
            'arrears_received_from' => 'nullable|string|max:45',
            'arrears_service_invoice_no' => 'required|integer',
            'arrears_date' => 'required|date',
            'arrears_items' => 'required|array',
            'arrears_items.0.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
            'arrears_items.0.amount' => 'required|numeric|min:0.01',
            'arrears_received_by' => 'required|string|max:45',
            'arrears_payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
            'arrears_reference_no' => 'nullable|string|max:45',
            'arrears_remarks' => 'required|string|max:150' // Increased for edit remarks
        ]);

        // Add this logging right after validation
        Log::info('Validation passed for arrears edit', [
            'validated_data' => $validated,
            'sin_number' => $validated['arrears_service_invoice_no']
        ]);

        $formattedAddress = $this->formatAddressId($validated['arrears_address_id']);

        // Begin transaction for data integrity
        DB::beginTransaction();
        
        // Find the member record using the address ID
        $memberSum = MemberSum::where('mem_add_id', $validated['arrears_address_id'])->first();
        
        if (!$memberSum) {
            // Add this logging when member is not found
            Log::error('Member not found during arrears edit', [
                'address_id' => $validated['arrears_address_id'],
                'sin_number' => $validated['arrears_service_invoice_no']
            ]);
            throw new \Exception('Member not found with the provided Address ID');
        }

        // Add this logging after finding member
        Log::info('Member found for arrears edit', [
            'member_id' => $memberSum->mem_id,
            'member_name' => $memberSum->mem_fname . ' ' . $memberSum->mem_lname,
            'address_id' => $validated['arrears_address_id'],
            'current_arrear_balance' => $memberSum->arrear
        ]);
        
        // Get the original transaction that we're editing
        $originalSinNumber = $validated['arrears_service_invoice_no'];
        $originalTransaction = AcctReceivable::where('or_number', $originalSinNumber)
            ->where('mem_id', $memberSum->mem_id)
            ->first();
            
        if (!$originalTransaction) {
            // Add this logging when original transaction is not found
            Log::error('Original transaction not found during arrears edit', [
                'sin_number' => $originalSinNumber,
                'member_id' => $memberSum->mem_id
            ]);
            throw new \Exception('Original transaction not found');
        }

        // Add this logging after finding original transaction
        Log::info('Original transaction found for editing', [
            'original_transaction_id' => $originalTransaction->ar_transno,
            'original_amount' => $originalTransaction->ar_amount,
            'original_date' => $originalTransaction->ar_date,
            'original_remarks' => $originalTransaction->ar_remarks,
            'sin_number' => $originalSinNumber
        ]);
        
        // STEP 1: Create cancellation entry for original transaction
        $cancellationTransaction = new AcctReceivable();
        $cancellationTransaction->mem_id = $memberSum->mem_id;
        $cancellationTransaction->or_number = $originalSinNumber;
        $cancellationTransaction->ar_date = now()->format('Y-m-d');
        $cancellationTransaction->ar_amount = -abs($originalTransaction->ar_amount); // Negative amount
        $cancellationTransaction->arrear_bal = $memberSum->arrear + abs($originalTransaction->ar_amount); // Restore original balance
        $cancellationTransaction->acct_type_id = $originalTransaction->acct_type_id;
        $cancellationTransaction->payor_name = $originalTransaction->payor_name;
        $cancellationTransaction->payor_address = $formattedAddress;
        $cancellationTransaction->payment_type = $originalTransaction->payment_type;
        $cancellationTransaction->payment_Ref = $originalTransaction->payment_Ref;
        $cancellationTransaction->receive_by = Auth::user()->fullname ?? Auth::user()->name;
        $cancellationTransaction->ar_remarks = "SYSTEM CANCELLATION FOR EDIT - Original SIN: {$originalSinNumber}";
        $cancellationTransaction->user_id = Auth::id();
        $cancellationTransaction->save();

        // Add this logging after creating cancellation transaction
        Log::info('Cancellation transaction created', [
            'cancellation_transaction_id' => $cancellationTransaction->ar_transno,
            'cancellation_amount' => $cancellationTransaction->ar_amount,
            'restored_balance' => $cancellationTransaction->arrear_bal,
            'sin_number' => $originalSinNumber
        ]);
        
        // STEP 2: Create new transaction with updated details
        $newPaymentAmount = $validated['arrears_items'][0]['amount'];
        $currentArrear = $memberSum->arrear + abs($originalTransaction->ar_amount); // Balance after cancellation
        $newArrearBalance = $currentArrear - $newPaymentAmount;

        // Add this logging before creating new transaction
        Log::info('Creating new edited transaction', [
            'new_amount' => $newPaymentAmount,
            'balance_after_cancellation' => $currentArrear,
            'new_balance_after_payment' => $newArrearBalance,
            'sin_number' => $originalSinNumber
        ]);
        
        $editedTransaction = new AcctReceivable();
        $editedTransaction->mem_id = $memberSum->mem_id;
        $editedTransaction->or_number = $originalSinNumber; // Keep same SIN number
        $editedTransaction->ar_date = now()->format('Y-m-d');;
        $editedTransaction->ar_amount = $newPaymentAmount;
        $editedTransaction->arrear_bal = $newArrearBalance;
        $editedTransaction->acct_type_id = $validated['arrears_items'][0]['coa'];
        $editedTransaction->payor_name = strtoupper($validated['arrears_received_from']);
        $editedTransaction->payor_address = $formattedAddress;
        $editedTransaction->payment_type = $validated['arrears_payment_mode'];
        $editedTransaction->payment_Ref = $validated['arrears_reference_no'] ?? null;
        $editedTransaction->receive_by = $validated['arrears_received_by'];
        $editedTransaction->ar_remarks = $validated['arrears_remarks'];
        $editedTransaction->user_id = Auth::id();
        $editedTransaction->save();

        // Add this logging after creating edited transaction
        Log::info('New edited transaction created', [
            'edited_transaction_id' => $editedTransaction->ar_transno,
            'edited_amount' => $editedTransaction->ar_amount,
            'edited_date' => $editedTransaction->ar_date,
            'edited_remarks' => $editedTransaction->ar_remarks,
            'final_arrear_balance' => $editedTransaction->arrear_bal,
            'sin_number' => $originalSinNumber
        ]);
        
        // STEP 3: Update member_sum with new payment information
        $memberSum->last_or = $originalSinNumber;
        $memberSum->last_paydate = $validated['arrears_date'];
        $memberSum->last_payamount = $newPaymentAmount;
        $memberSum->arrear = $newArrearBalance;
        
        // Log before arrear_total calculation
        Log::info('EDIT ARREARS - Before arrear_total calculation', [
            'member_id' => $memberSum->mem_id,
            'current_arrear' => $memberSum->arrear,
            'current_arrear_interest' => $memberSum->arrear_interest,
            'new_arrear_balance' => $newArrearBalance,
            'new_payment_amount' => $newPaymentAmount,
            'original_sin' => $originalSinNumber
        ]);
        
        // Calculate and update arrear_total (arrear + arrear_interest)
        $arrearInterest = $memberSum->arrear_interest ?? 0;
        $calculatedArrearTotal = $newArrearBalance + $arrearInterest;
        $memberSum->arrear_total = $calculatedArrearTotal;
        
        // Log after arrear_total calculation
        Log::info('EDIT ARREARS - After arrear_total calculation', [
            'member_id' => $memberSum->mem_id,
            'arrear_interest' => $arrearInterest,
            'calculated_arrear_total' => $calculatedArrearTotal,
            'assigned_arrear_total' => $memberSum->arrear_total
        ]);
        
        $memberSum->user_id = Auth::id();
        $memberSum->save();
        
        // Log after saving
        Log::info('EDIT ARREARS - After saving to database', [
            'member_id' => $memberSum->mem_id,
            'saved_arrear' => $memberSum->arrear,
            'saved_arrear_total' => $memberSum->arrear_total
        ]);

        // Add this logging after updating member summary
        Log::info('Member summary updated', [
            'member_id' => $memberSum->mem_id,
            'updated_last_or' => $memberSum->last_or,
            'updated_last_paydate' => $memberSum->last_paydate,
            'updated_last_payamount' => $memberSum->last_payamount,
            'updated_arrear_balance' => $memberSum->arrear,
            'sin_number' => $originalSinNumber
        ]);
        
        // Commit the transaction
        DB::commit();

        // Add this logging after successful commit
        Log::info('=== ARREARS RECEIVABLE EDIT COMPLETED SUCCESSFULLY ===', [
            'sin_number' => $originalSinNumber,
            'member_id' => $memberSum->mem_id,
            'original_amount' => $originalTransaction->ar_amount,
            'new_amount' => $newPaymentAmount,
            'user_id' => Auth::id(),
            'timestamp' => now()
        ]);
        
        // Return success response
        return redirect()->route('accounts.receivables', ['tab' => $request->input('active_tab', 'arrears')])
            ->with('success', "SIN #{$originalSinNumber} has been successfully updated");
                        
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Add this logging for validation errors
        Log::error('Validation failed during arrears edit', [
            'errors' => $e->errors(),
            'input_data' => $request->all(),
            'user_id' => Auth::id()
        ]);
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        DB::rollBack();

        // Add this logging for general errors - place it right after DB::rollBack()
        Log::error('=== ARREARS RECEIVABLE EDIT FAILED ===', [
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'sin_number' => $request->input('arrears_service_invoice_no'),
            'user_id' => Auth::id(),
            'request_data' => $request->all(),
            'timestamp' => now()
        ]);

        Log::error('Error editing HOA monthly dues: ' . $e->getMessage());
        return back()->with('error', 'Error editing HOA monthly dues: ' . $e->getMessage())
            ->withInput();
    }
}
/**
 * Converts a 5-digit address ID into a human-readable format.
 * e.g., '10603' becomes 'Ph. 1 Blk. 06 Lot 03'
 *
 * @param string $addressId The 5-digit address ID.
 * @return string The formatted address or the original ID on failure.
 */
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
}