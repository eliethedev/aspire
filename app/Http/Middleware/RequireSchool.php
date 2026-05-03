<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->bound('current_school')) {
            return response()->json(['message' => 'School required'], 400);
        }

        return $next($request);
    }
}
