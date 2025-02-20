<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\ApDetail;
use App\Models\ChartOfAccount;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Create Account Payable record
            $accountPayable = AccountPayable::create([
                'ap_voucherno' => $request->voucher_no,
                'ap_date' => $request->date,
                'ap_payee' => $request->payee,
                'ap_paytype' => $request->payment_mode,
                'paytype_reference' => $request->reference_no,
                'ap_total' => $request->total_amount,
                'remarks' => $request->remarks,
                'user_id' => FacadesAuth::id()
            ]);

            // Create AP Details records
            foreach ($request->items as $item) {
                ApDetail::create([
                    'ap_transno' => $accountPayable->ap_transno,
                    'ap_particular' => $item['particular'],
                    'ap_amount' => $item['amount'],
                    'acct_type_id' => $item['account_type']
                ]);
            }

            // Flash success message to session
            session()->flash('success', 'Account Payable created successfully!');

            DB::commit();
            return redirect()->route('accounts.payables.index')->with('success', 'Account Payable created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'An error occurred while saving the record')->withInput();
        }
    }
}