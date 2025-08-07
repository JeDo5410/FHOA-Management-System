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
        $member = DB::table('vw_member_data')
            ->where('mem_id', $id)
            ->first();
        
        if (!$member) {
            return back()->with('error', 'Member not found');
        }
        
        // Add property for backward compatibility
        $member->current_arrear_count = $member->arrear_count;
        $member->current_arrear = $member->arrear;
        
        // Get document types from request (default to 'soa' if not specified)
        $documentTypes = $request->input('document_types', 'soa');
        $documentTypes = explode(',', $documentTypes);
        
        // Flash success message for confirmation
        if (count($documentTypes) > 1) {
            session()->flash('success', "Multiple documents generated for {$member->mem_name}");
        } else {
            $docTypeName = 'Document';
            if ($documentTypes[0] === 'soa') {
                $docTypeName = 'Statement of Account';
            } elseif ($documentTypes[0] === 'demand') {
                $docTypeName = 'Demand Letter';
            } elseif ($documentTypes[0] === 'nncv1') {
                $docTypeName = 'Notice of Non-Compliance/Violation';
            }
            session()->flash('success', "{$docTypeName} generated for {$member->mem_name}");
        }
        
        return view('accounts.soa.print', compact('member', 'documentTypes'));
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
        $members = DB::table('vw_member_data')
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
        
        // Get document types from request (default to 'soa' if not specified)
        $documentTypes = $request->input('document_types', 'soa');
        $documentTypes = explode(',', $documentTypes);
        
        // Flash success message with count
        $count = $members->count();
        if (count($documentTypes) > 1) {
            session()->flash('success', "Generated multiple document types for {$count} member(s)");
        } else {
            $docTypeName = 'Documents';
            if ($documentTypes[0] === 'soa') {
                $docTypeName = 'Statements of Account';
            } elseif ($documentTypes[0] === 'demand') {
                $docTypeName = 'Demand Letters';
            } elseif ($documentTypes[0] === 'nncv1') {
                $docTypeName = 'Notices of Non-Compliance/Violation';
            }
            session()->flash('success', "Generated {$count} {$docTypeName}");
        }
        
        return view('accounts.soa.print-multiple', compact('members', 'documentTypes'));
    }
}