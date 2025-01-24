<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function checkUsername(Request $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'exists' => false,
                'message' => 'Username not found in the system.'
            ]);
        }

        return response()->json([
            'exists' => true,
            'hasPassword' => !is_null($user->password)
        ]);
    }

    // LoginController.php
    public function setInitialPassword(Request $request)
    {
        Log::info('setInitialPassword called with:', [
            'username' => $request->username,
            'has_password' => isset($request->new_password)
        ]);

        $request->validate([
            'username' => 'required',
            'new_password' => 'required|min:5'
        ]);

        $user = User::where('username', $request->username)->first();

        Log::info('User found:', [
            'user_exists' => (bool)$user,
            'current_password' => $user ? (bool)$user->password : null
        ]);

        if (!$user || $user->password) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        Log::info('Password updated successfully');

        return response()->json([
            'success' => true,
            'message' => 'Password set successfully. Please login with your new credentials.'
        ]);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
