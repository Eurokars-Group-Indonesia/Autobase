<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserRole;

class CheckUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if ($user->is_active === '0') {
            Log::warning('Access attempt with inactive account', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'route' => $request->path()
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Please contact administrator.'
            ]);
        }

        // Check if user has active role
        $hasRole = UserRole::where('user_id', $user->user_id)
            ->where('is_active', '1')
            ->exists();

        if (!$hasRole) {
            Log::warning('Access attempt without role', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'route' => $request->path()
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'You don\'t have a role in this system. Please contact IT.');
        }

        return $next($request);
    }
}
