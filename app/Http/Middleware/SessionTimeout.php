<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only check timeout for authenticated users and on routes that are not auth routes
        if (Auth::check() && !$request->routeIs('login') && !$request->routeIs('check-username') && !$request->routeIs('refresh-csrf')) {
            $lastActivity = session('last_activity');
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds
    
            if ($lastActivity && time() - $lastActivity > $timeout) {
                Auth::logout();
                session()->flush();
                
                // If this is an AJAX request, return 401 response
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['authenticated' => false], 401);
                }
                
                return redirect()->route('login')
                    ->with('message', 'Your session has expired. Please login again.');
            }
    
            session(['last_activity' => time()]);
        }
    
        return $next($request);
    }
}