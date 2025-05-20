<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportExtractionController extends Controller
{
    /**
     * Display the extraction page
     */
    public function index()
    {
        // Get list of available reports
        $reportTypes = [
            'residents' => 'Resident Information',
            'members' => 'Members List',
            'vehicles' => 'Vehicle/Car Sticker Information',
            'payments' => 'Payment Transactions',
            'receivables' => 'Account Receivables',
            'payables' => 'Account Payables',
        ];
        
        return view('reports.extraction', compact('reportTypes'));
    }
        /**
     * Get members data with optional status filter
     */
    public function getMembersData(Request $request)
    {
        $status = $request->input('status', 'all');
        
        $query = DB::table('vw_member_data');
        
        // Apply filters based on status
        if ($status == 'active') {
            $query->where('arrear_count', '<=', 2);
        } else if ($status == 'delinquent') {
            $query->where('arrear_count', '>', 2);
        }
        
        $members = $query->get();
        
        return response()->json($members);
    }
    
    /**
     * Get car sticker data
     */
    public function getCarStickerData(Request $request)
    {
        $carStickers = DB::table('vw_car_sticker')->get();
        
        return response()->json($carStickers);
    }
    
    /**
     * Download members data as CSV
     */
    public function downloadMembersData(Request $request)
    {
        $status = $request->input('status', 'all');
        
        $query = DB::table('vw_member_data');
        
        // Apply filters based on status
        if ($status == 'active') {
            $query->where('arrear_count', '<=', 2);
        } else if ($status == 'delinquent') {
            $query->where('arrear_count', '>', 2);
        }
        
        $members = $query->get();
        
        // Create CSV content
        $headers = [
            'Member ID', 'Trans No.', 'Address ID', 'Name', 'SPA/Tenant', 'Type', 'Monthly Dues',
            'Arrear Month', 'Arrear', 'Arrear Count', 'Arrear Interest', 'Last OR', 'Last Pay Date',
            'Last Pay Amount', 'Mobile', 'Date', 'Email',
            'Resident 1', 'Resident 2', 'Resident 3', 'Resident 4', 'Resident 5',
            'Resident 6', 'Resident 7', 'Resident 8', 'Resident 9', 'Resident 10',
            'Relationship 1', 'Relationship 2', 'Relationship 3', 'Relationship 4', 'Relationship 5',
            'Relationship 6', 'Relationship 7', 'Relationship 8', 'Relationship 9', 'Relationship 10',
            'Remarks'
        ];
        
        $csv = implode(',', $headers) . "\n";
        
        foreach ($members as $member) {
            $row = [
                $member->mem_id,
                $member->mem_transno,
                $member->mem_add_id,
                '"' . str_replace('"', '""', $member->mem_name) . '"',
                '"' . str_replace('"', '""', $member->mem_SPA_Tenant ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_type) . '"',
                $member->mem_monthlydues,
                $member->arrear_month,
                $member->arrear,
                $member->arrear_count,
                $member->arrear_interest,
                $member->last_or,
                $member->last_paydate,
                $member->last_payamount,
                '"' . str_replace('"', '""', $member->mem_mobile ?? '') . '"',
                $member->mem_date,
                '"' . str_replace('"', '""', $member->mem_email ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident1 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident2 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident3 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident4 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident5 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident6 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident7 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident8 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident9 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Resident10 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship1 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship2 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship3 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship4 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship5 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship6 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship7 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship8 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship9 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_Relationship10 ?? '') . '"',
                '"' . str_replace('"', '""', $member->mem_remarks ?? '') . '"'
            ];
            
            $csv .= implode(',', $row) . "\n";
        }
        
        // Generate filename with current date
        $filename = 'members_data_(' . $status . ')_' . date('Y-m-d') . '.csv';
        
        // Create download response
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Download car sticker data as CSV
     */
    public function downloadCarStickerData(Request $request)
    {
        $carStickers = DB::table('vw_car_sticker')->get();
        
        // Create CSV content
        $headers = [
            'Member ID', 'Address ID', 'Type', 'Name', 'SPA/Tenant',
            'Vehicle Maker', 'Vehicle Type', 'Vehicle Color',
            'Vehicle OR', 'Vehicle CR', 'Vehicle Plate',
            'Car Sticker', 'Vehicle Active', 'Remarks'
        ];
        
        $csv = implode(',', $headers) . "\n";
        
        foreach ($carStickers as $car) {
            $row = [
                $car->mem_id,
                $car->mem_add_id,
                '"' . str_replace('"', '""', $car->mem_typedescription) . '"',
                '"' . str_replace('"', '""', $car->mem_name) . '"',
                '"' . str_replace('"', '""', $car->mem_SPA_Tenant ?? '') . '"',
                '"' . str_replace('"', '""', $car->vehicle_maker ?? '') . '"',
                '"' . str_replace('"', '""', $car->vehicle_type ?? '') . '"',
                '"' . str_replace('"', '""', $car->vehicle_color ?? '') . '"',
                '"' . str_replace('"', '""', $car->vehicle_OR ?? '') . '"',
                '"' . str_replace('"', '""', $car->vehicle_CR ?? '') . '"',
                '"' . str_replace('"', '""', $car->vehicle_plate ?? '') . '"',
                '"' . str_replace('"', '""', $car->car_sticker ?? '') . '"',
                $car->vehicle_active,
                '"' . str_replace('"', '""', $car->remarks ?? '') . '"'
            ];
            
            $csv .= implode(',', $row) . "\n";
        }
        
        // Generate filename with current date
        $filename = 'members_car_sticker_' . date('Y-m-d') . '.csv';
        
        // Create download response
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get account payable data with date range filter
     */
    public function getPayableData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = DB::table('vw_acct_payable');
        
        // Apply date range filter
        if ($startDate && $endDate) {
            $query->whereBetween('ap_date', [$startDate, $endDate]);
        }
        
        $payables = $query->get();
        
        return response()->json($payables);
    }

    /**
     * Download account payable data as CSV
     */
    public function downloadPayableData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = DB::table('vw_acct_payable');
        
        // Apply date range filter
        if ($startDate && $endDate) {
            $query->whereBetween('ap_date', [$startDate, $endDate]);
        }
        
        $payables = $query->get();
        
        // Create CSV content
        $headers = [
            'Trans No.', 'Voucher No.', 'Date', 'Payee', 'Pay Type', 
            'Reference', 'Total', 'Particular', 'Amount', 
            'Account Type', 'Account Name', 'Remarks', 'User', 'Timestamp'
        ];
        
        $csv = implode(',', $headers) . "\n";
        
        foreach ($payables as $payable) {
            $row = [
                $payable->ap_transno,
                '"' . str_replace('"', '""', $payable->ap_voucherno) . '"',
                $payable->ap_date,
                '"' . str_replace('"', '""', $payable->ap_payee) . '"',
                '"' . str_replace('"', '""', $payable->ap_paytype) . '"',
                '"' . str_replace('"', '""', $payable->paytype_reference) . '"',
                $payable->ap_total,
                '"' . str_replace('"', '""', $payable->ap_particular) . '"',
                $payable->ap_amount,
                '"' . str_replace('"', '""', $payable->acct_type) . '"',
                '"' . str_replace('"', '""', $payable->acct_name) . '"',
                '"' . str_replace('"', '""', $payable->remarks) . '"',
                '"' . str_replace('"', '""', $payable->user_fullname) . '"',
                '"' . str_replace('"', '""', $payable->timestamp) . '"'
            ];
            
            $csv .= implode(',', $row) . "\n";
        }
        
        // Generate filename with date range
        $filename = 'account_payable_' . $startDate . '_to_' . $endDate . '.csv';
        
        // Create download response
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get account receivable data with date range filter
     */
    public function getReceivableData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = DB::table('vw_acct_receivable');
        
        // Apply date range filter
        if ($startDate && $endDate) {
            $query->whereBetween('ar_date', [$startDate, $endDate]);
        }
        
        $receivables = $query->get();
        
        return response()->json($receivables);
    }

    /**
     * Download account receivable data as CSV
     */
    public function downloadReceivableData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = DB::table('vw_acct_receivable');
        
        // Apply date range filter
        if ($startDate && $endDate) {
            $query->whereBetween('ar_date', [$startDate, $endDate]);
        }
        
        $receivables = $query->get();
        
        // Create CSV content
        $headers = [
            'Trans No.', 'OR Number', 'Date', 'Amount', 'Arrear Balance',
            'Account Description', 'Payor Name', 'Payor Address', 'Payment Type',
            'Payment Reference', 'Received By', 'Remarks', 'User', 'Timestamp'
        ];
        
        $csv = implode(',', $headers) . "\n";
        
        foreach ($receivables as $receivable) {
            $row = [
                $receivable->ar_transno,
                '"' . str_replace('"', '""', $receivable->or_number) . '"',
                $receivable->ar_date,
                $receivable->ar_amount,
                $receivable->arrear_bal,
                '"' . str_replace('"', '""', $receivable->acct_description) . '"',
                '"' . str_replace('"', '""', $receivable->payor_name) . '"',
                '"' . str_replace('"', '""', $receivable->payor_address) . '"',
                '"' . str_replace('"', '""', $receivable->payment_type) . '"',
                '"' . str_replace('"', '""', $receivable->payment_Ref) . '"',
                '"' . str_replace('"', '""', $receivable->receive_by) . '"',
                '"' . str_replace('"', '""', $receivable->ar_remarks) . '"',
                '"' . str_replace('"', '""', $receivable->user_fullname) . '"',
                '"' . str_replace('"', '""', $receivable->timestamp) . '"'
            ];
            
            $csv .= implode(',', $row) . "\n";
        }
        
        // Generate filename with date range
        $filename = 'account_receivable_' . $startDate . '_to_' . $endDate . '.csv';
        
        // Create download response
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}