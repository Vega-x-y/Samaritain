<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has the given role (via Spatie permissions).
     * Also accepts an artisan-specific check: if the role is "artisan",
     * the user must have an artisan profile linked.
     */
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if ($user->hasRole($role)) {
            return $next($request);
        }

        // Special case: "artisan" role also accepts users who own an artisan profile
        if ($role === 'artisan' && $user->artisan()->exists()) {
            return $next($request);
        }

        abort(403, 'Accès réservé aux artisans.');
    }
}
