<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return redirect()->route('login');
        }

        // Check global role first
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Check tenant-specific role if tenant is available
        if (app()->bound('current_tenant')) {
            $tenant = app('current_tenant');

            foreach ($roles as $role) {
                if ($user->hasTenantRole($tenant, $role)) {
                    return $next($request);
                }
            }
        }

        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        abort(403, 'Unauthorized.');
    }
}
