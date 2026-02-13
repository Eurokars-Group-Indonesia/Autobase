<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, return null to trigger 401 response
        if ($request->expectsJson()) {
            return null;
        }

        // For web requests, redirect to login
        return route('login');
    }

    /**
     * Handle an unauthenticated user.
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API or AJAX requests, return JSON response
        if ($request->expectsJson() || $request->ajax()) {
            abort(401, 'Unauthenticated.');
        }

        // For web requests, redirect to login
        return redirect()->guest(route('login'));
    }
}
