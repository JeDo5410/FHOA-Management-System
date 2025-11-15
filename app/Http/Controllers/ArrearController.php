<?php

namespace App\Http\Controllers;

use App\Models\MemberSum;
use Illuminate\Http\Request;

class ArrearController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $membersQuery = MemberSum::with('memberData');

        if ($search) {
            $membersQuery->where(function($query) use ($search) {
                $query->where('mem_id', 'LIKE', "%{$search}%")
                      ->orWhere('mem_add_id', 'LIKE', "%{$search}%");
            });
        }

        $members = $membersQuery->orderBy('mem_id')->get();

        // Pass single member data if exactly one result is found
        $singleMember = ($search && $members->count() == 1) ? $members->first() : null;

        return view('arrears.index', compact('members', 'search', 'singleMember'));
    }

    public function edit(Request $request, $id)
    {
        $member = MemberSum::with('memberData')->findOrFail($id);
        $search = $request->get('search');
        $returnAnchor = $request->get('return_anchor');
        return view('arrears.edit', compact('member', 'search', 'returnAnchor'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'arrear' => 'required|numeric',
            'arrear_interest' => 'required|numeric',
        ]);

        $member = MemberSum::findOrFail($id);
        $member->update([
            'arrear' => $request->arrear,
            'arrear_interest' => $request->arrear_interest,
            'arrear_total' => $request->arrear + $request->arrear_interest,
        ]);

        // Preserve search parameter and anchor when redirecting back
        $redirectUrl = route('arrears.index');
        $queryParams = [];
        
        if ($request->has('search') && !empty($request->search)) {
            $queryParams[] = 'search=' . urlencode($request->search);
        }
        
        if (!empty($queryParams)) {
            $redirectUrl .= '?' . implode('&', $queryParams);
        }
        
        // Add anchor for scroll position
        if ($request->has('return_anchor') && !empty($request->return_anchor)) {
            $redirectUrl .= '#' . $request->return_anchor;
        }

        return redirect($redirectUrl)->with('success', 'Arrear updated successfully.');
    }
}