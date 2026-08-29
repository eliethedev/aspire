<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->bound('current_school')) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'School required'], 400);
            }

            abort(400, 'School context is required.');
        }

        return $next($request);
    }
}
