<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\AcctReceivable;
use App\Models\ArDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountReceivableController extends Controller
{
    public function index()
    {
        // For Account Receivable tab: All Association Receipts EXCEPT Association Dues
        $accountTypes = ChartOfAccount::where('acct_type', 'Association Receipts')
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
        // Determine which form was submitted and route to the appropriate method
        $formType = $request->input('form_type');
        
        if ($formType === 'account_receivable') {
            return $this->storeAccountReceivable($request);
        } elseif ($formType === 'arrears_receivable') {
            return $this->storeArrearsReceivable($request);
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
            
            // Create the account receivable record
            $accountReceivable = new AcctReceivable();
            $accountReceivable->or_number = $validated['service_invoice_no'];
            $accountReceivable->ar_date = $validated['date'];
            $accountReceivable->ar_total = $validated['total_amount'];
            $accountReceivable->ar_remarks = $validated['remarks'] ?? null;
            $accountReceivable->receive_by = $validated['received_by'];
            $accountReceivable->payment_type = $validated['payment_mode'];
            $accountReceivable->payment_ref = $validated['reference_no'] ?? null;
            $accountReceivable->user_id = Auth::id(); // Get the currently logged-in user's ID
            $accountReceivable->save();
            
            // Create the AR detail records
            foreach ($validated['items'] as $item) {
                $detail = new ArDetail();
                $detail->ar_transno = $accountReceivable->ar_transno;
                $detail->payor_name = $validated['received_from'];
                $detail->payor_address = $validated['address'];
                $detail->acct_type_id = $item['coa'];
                $detail->ar_amount = $item['amount'];
                $detail->user_id = Auth::id();
                $detail->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            // Return success response with toast notification
            return redirect()->route('accounts.receivables')
                ->with('success', 'Account receivable created successfully');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error, no need to rollback as no DB operations were performed
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();
            
            // Log the error
            \Log::error('Error creating account receivable: ' . $e->getMessage());
            
            // Return error response with toast notification
            return back()->with('error', 'Error creating account receivable: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Store an arrears receivable record (second tab)
     */
    private function storeArrearsReceivable(Request $request)
    {
        try {
            // Validate the HOA Monthly Dues form
            $validated = $request->validate([
                'arrears_received_from' => 'required|string|max:45',
                'arrears_service_invoice_no' => 'required|integer',
                'arrears_date' => 'required|date',
                'arrears_items' => 'required|array',
                'arrears_items.*.coa' => 'required|integer|exists:charts_of_account,acct_type_id',
                'arrears_items.*.amount' => 'required|numeric|min:0',
                'arrears_total_amount' => 'required|numeric|min:0',
                'arrears_remarks' => 'nullable|string|max:45'
            ]);

            // We'll implement the HOA Monthly Dues tab later
            return back()->with('info', 'HOA Monthly Dues form submitted - Implementation coming soon');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            \Log::error('Error creating HOA monthly dues record: ' . $e->getMessage());
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
            // Get all payments for this member with acct_type_id = 101
            $payments = AcctReceivable::where('mem_id', $memberId)
                ->where('acct_type_id', 101)
                ->orderBy('ar_date', 'desc')
                ->get(['ar_transno', 'ar_date', 'ar_amount', 'or_number', 'arrear_bal', 'ar_remarks']);
                
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
}