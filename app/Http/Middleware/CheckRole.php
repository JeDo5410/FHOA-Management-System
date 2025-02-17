<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role === 1) {  // Administrator always has access
            return $next($request);
        }

        if (in_array(auth()->user()->role, array_map('intval', $roles))) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
