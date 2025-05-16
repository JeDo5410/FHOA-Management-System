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
        
        // Filter for delinquent accounts using hoa_status column
        if ($delinquent) {
            $query->where('hoa_status', 'DELIQUENT');
        }
        
        // Execute query and modify the result set to ensure consistent property naming
        $arrears = $query->get()->map(function($item) {
            // Make sure to map the arrear_count field to match the view expectations
            $item->current_arrear_count = $item->arrear_count;
            // Add a current_arrear field for backward compatibility with the view
            $item->current_arrear = $item->arrear;
            return $item;
        });
        
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
     * Print statement of account for a specific member
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function printStatement(Request $request, $id)
    {
        // Get member details from vw_arrear_staging
        $member = DB::table('vw_arrear_staging')
            ->where('mem_id', $id)
            ->first();
        
        if (!$member) {
            return back()->with('error', 'Member not found');
        }
        
        // Add property for backward compatibility
        $member->current_arrear_count = $member->arrear_count;
        $member->current_arrear = $member->arrear;
        
        // Get document type from request (default to 'soa' if not specified)
        $documentType = $request->input('document_type', 'soa');
        
        // Flash success message for confirmation
        $docTypeName = $documentType === 'soa' ? 'Statement of Account' : 'Demand Letter';
        session()->flash('success', "{$docTypeName} generated for {$member->mem_name}");
        
        return view('accounts.soa.print', compact('member', 'documentType'));
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
            ->get()
            ->map(function($member) {
                // Add property for backward compatibility
                $member->current_arrear_count = $member->arrear_count;
                $member->current_arrear = $member->arrear;
                return $member;
            });
        
        if ($members->isEmpty()) {
            return back()->with('error', 'No members found');
        }
        
        // Get document type from request (default to 'soa' if not specified)
        $documentType = $request->input('document_type', 'soa');
        
        // Flash success message with count
        $docTypeName = $documentType === 'soa' ? 'Statements of Account' : 'Demand Letters';
        $count = $members->count();
        session()->flash('success', "Generated {$count} {$docTypeName}");
        
        return view('accounts.soa.print-multiple', compact('members', 'documentType'));
    }
}