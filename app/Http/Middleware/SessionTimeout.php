<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');
            $timeout = config('session.lifetime') * 60; // Convert minutes to seconds

            if ($lastActivity && time() - $lastActivity > $timeout) {
                Auth::logout();
                session()->flush();
                return redirect()->route('login')
                    ->with('message', 'Your session has expired. Please login again.');
            }

            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}