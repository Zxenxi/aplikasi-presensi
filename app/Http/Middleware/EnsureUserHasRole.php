<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string $roles Comma-separated list of allowed roles
     */
    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        $user = $request->user();
        
        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access this resource.');
        }
        
        // If no specific roles required, just check authentication
        if (empty($roles)) {
            return $next($request);
        }
        
        $allowedRoles = array_map('trim', explode(',', $roles));
        
        // Check if user has one of the allowed roles
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized access. Required role: ' . implode(' or ', $allowedRoles));
        }
        
        // Check if user account is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact administrator.');
        }
        
        return $next($request);
    }
}
