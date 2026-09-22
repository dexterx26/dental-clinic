<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        // Administrator has unrestricted access across Phase 1
        if ($user->role === 'administrator') {
            return $next($request);
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized action. You do not have permission to access this module.');
        }

        return $next($request);
    }
}
