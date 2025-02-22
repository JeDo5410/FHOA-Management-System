<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountReceivableController extends Controller
{
    public function index()
    {
        return view('accounts.receivable');
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'address' => 'required|string',
            'received_from' => 'required|string',
            'date' => 'required|date',
            'items' => 'required|array',
            'items.*.coa' => 'required|string',
            'items.*.amount' => 'required|numeric',
            'items.*.address_id' => 'required|string',
            'total_amount' => 'required|numeric',
            'received_by' => 'required|string',
            'payment_mode' => 'required|in:CASH,GCASH,CHECK,BANK_TRANSFER',
            'reference_no' => 'nullable|string',
            'remarks' => 'nullable|string|max:45'
        ]);

        try {
            // TODO: Add your logic to store the receivable record
            // This will depend on your database structure

            return response()->json([
                'status' => 'success',
                'message' => 'Account receivable created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating account receivable: ' . $e->getMessage()
            ], 500);
        }
    }
}
