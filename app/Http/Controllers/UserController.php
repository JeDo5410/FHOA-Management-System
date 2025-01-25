<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = User::whereRaw('BINARY username = ?', [$value])->exists();
                    if ($exists) {
                        $fail('This username has already been taken.');
                    }
                }
            ],
            'role' => 'required|in:1,2'
        ]);
    
        User::create($validated);
        return response()->json(['message' => 'User created successfully']);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'is_active' => 'required|boolean',
            'clear_password' => 'boolean',
            'role' => 'required|in:1,2'
        ];
    
        // Only validate username if it has changed
        if ($request->username !== $user->username) {
            $rules['username'] = [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    $exists = User::where('id', '!=', $user->id)
                        ->whereRaw('BINARY username = ?', [$value])
                        ->exists();
                    if ($exists) {
                        $fail('This username has already been taken.');
                    }
                }
            ];
        }
    
        $validated = $request->validate($rules);
    
        $updateData = [
            'is_active' => $validated['is_active'],
            'role' => $validated['role']
        ];
    
        if (isset($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }
    
        if ($validated['clear_password']) {
            $updateData['password'] = null;
        }
    
        $user->update($updateData);
        return response()->json(['message' => 'User updated successfully']);
    }
}