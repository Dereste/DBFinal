<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!session('is_admin')) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }
        return $next($request);
    }
}

