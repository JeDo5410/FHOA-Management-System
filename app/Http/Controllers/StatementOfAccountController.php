<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementOfAccountController extends Controller
{
    /**
     * Get users by designation (case-insensitive, latest created_at)
     */
    private function getUsersByDesignations()
    {
        $designations = ['Admin Assistant', 'Treasurer', 'Auditor', 'Secretary', 'Vice President', 'President'];
        $users = [];
        
        foreach ($designations as $designation) {
            $user = User::whereRaw('LOWER(designation) = ?', [strtolower($designation)])
                       ->orderBy('created_at', 'desc')
                       ->first();
            
            if ($user) {
                $users[strtolower(str_replace(' ', '_', $designation))] = $user;
            }
        }
        
        return $users;
    }
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
        $memberStatus = $request->input('member_status', 'all');
        
        // Build the query for vw_arrear_staging
        $query = DB::table('vw_arrear_staging')->orderBy('mem_id', 'asc');
        
        // Apply filters if provided
        if ($addressId) {
            $query->where('mem_add_id', $addressId);
        }
        
        // Filter by member status
        if ($memberStatus !== 'all') {
            if ($memberStatus === 'delinquent') {
                $query->where('hoa_status', 'DELINQUENT');
            } elseif ($memberStatus === 'active') {
                $query->where('hoa_status', '!=', 'DELINQUENT');
            }
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
        $statusText = $memberStatus === 'all' ? 'all members' : ($memberStatus === 'delinquent' ? 'delinquent members' : 'active members');
        $message = "{$count} record" . ($count != 1 ? "s" : "") . " found for {$statusText}";
        
        // Add filter information to the message if filters were applied
        if ($addressId) {
            $message .= " with Address ID: {$addressId}";
        }
        
        // Use session flash for toast notification
        session()->flash('success', $message);
        
        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $arrears->toArray(),
                'count' => $count
            ]);
        }
        
        return view('accounts.soa.index', compact('arrears'));
    }

    /**
     * Get member counts by status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberCounts()
    {
        // Get all members count
        $allCount = DB::table('vw_arrear_staging')->count();
        
        // Get active members count (not delinquent)
        $activeCount = DB::table('vw_arrear_staging')
            ->where('hoa_status', '!=', 'DELINQUENT')
            ->count();
        
        // Get delinquent members count
        $delinquentCount = DB::table('vw_arrear_staging')
            ->where('hoa_status', 'DELINQUENT')
            ->count();
        
        return response()->json([
            'all' => $allCount,
            'active' => $activeCount,
            'delinquent' => $delinquentCount
        ]);
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
        
        // Get users by designations for dynamic signatures
        $designationUsers = $this->getUsersByDesignations();
        
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
        
        return view('accounts.soa.print', compact('member', 'documentTypes', 'designationUsers'));
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
        
        // Get users by designations for dynamic signatures
        $designationUsers = $this->getUsersByDesignations();
        
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
        
        return view('accounts.soa.print-multiple', compact('members', 'documentTypes', 'designationUsers'));
    }
}