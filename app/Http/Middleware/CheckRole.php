<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }
            return redirect()->route('login');
        }

        // Check if user has role
        if (!auth()->user()->hasRole($role)) {
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
