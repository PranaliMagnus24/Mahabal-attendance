<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        // Check if user has any of the allowed roles
        if (! in_array($userRole, $roles)) {

            // Admin OR Manager → dashboard
            if (in_array($userRole, ['admin', 'manager'])) {
                return redirect()->route('dashboard');
            }

            // Normal user
            return redirect()->route('attendance.index');
        }

        return $next($request);
    }
}