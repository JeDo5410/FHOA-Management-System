<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::hasUser()) {
            return redirect('login');
        }

        if (Auth::user()->role !== 1) {
            return redirect('dashboard');
        }
        
        return $next($request);
    }
}