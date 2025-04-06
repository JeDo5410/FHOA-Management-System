<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementOfAccountController extends Controller
{
    /**
     * Display the Statement of Account for members
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $addressId = $request->input('address_id');
        $delinquent = $request->has('delinquent');
        
        // Build the query for vw_arrear_staging
        $query = DB::table('vw_arrear_staging')->orderBy('mem_id', 'asc');
        
        // Apply filters if provided
        if ($addressId) {
            $query->where('mem_add_id', $addressId);
        }
        
        // Filter for delinquent accounts (current_arrear_count >= 3)
        if ($delinquent) {
            $query->where('current_arrear_count', '>=', 3);
        }
        
        // Execute query
        $arrears = $query->get();
        
        // Prepare success message with count information
        $count = $arrears->count();
        $message = "{$count} record" . ($count != 1 ? "s" : "") . " found";
        
        // Add filter information to the message if filters were applied
        if ($addressId || $delinquent) {
            $message .= " with filters:";
            if ($addressId) {
                $message .= " Address ID: {$addressId}";
            }
            if ($delinquent) {
                $message .= " Delinquent Only";
            }
        }
        
        // Use session flash for toast notification
        session()->flash('success', $message);
        
        return view('accounts.soa.index', compact('arrears'));
    }

    /**
     * Get statement of account details for a specific member
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDetails(Request $request)
    {
        $memberId = $request->input('member_id');
        
        if (!$memberId) {
            return response()->json(['error' => 'Member ID is required'], 400);
        }
        
        // Get member details from vw_arrear_staging
        $member = DB::table('vw_arrear_staging')
            ->where('mem_id', $memberId)
            ->first();
        
        if (!$member) {
            return response()->json(['error' => 'Member not found'], 404);
        }
        
        return view('accounts.soa.details', compact('member'));
    }

    /**
     * Print statement of account for a specific member
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printStatement($id)
    {
        // Get member details from vw_arrear_staging
        $member = DB::table('vw_arrear_staging')
            ->where('mem_id', $id)
            ->first();
        
        if (!$member) {
            return back()->with('error', 'Member not found');
        }
        
        // Flash success message for confirmation
        session()->flash('success', "Statement generated for {$member->mem_name}");
        
        return view('accounts.soa.print', compact('member'));
    }

    /**
     * Print multiple statements of account
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printMultiple(Request $request)
    {
        $memberIds = explode(',', $request->input('member_ids'));
        
        if (empty($memberIds)) {
            return back()->with('error', 'No members selected');
        }
        
        // Get members details from vw_arrear_staging
        $members = DB::table('vw_arrear_staging')
            ->whereIn('mem_id', $memberIds)
            ->orderBy('mem_id', 'asc')
            ->get();
        
        if ($members->isEmpty()) {
            return back()->with('error', 'No members found');
        }
        
        // Flash success message with count
        $count = $members->count();
        session()->flash('success', "Generated {$count} statement" . ($count > 1 ? "s" : ""));
        
        return view('accounts.soa.print-multiple', compact('members'));
    }
}