<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AIRateLimitMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $userId = $user?->id ?? 'guest';
        $key = "ai:global:{$userId}";

        $perMinute = config('ai.rate_limits.per_minute', 30);

        if (RateLimiter::tooManyAttempts($key, $perMinute)) {
            $availableIn = RateLimiter::availableIn($key);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'AI service is busy. Please try again in a moment.',
                    'retry_after' => $availableIn,
                ], 429);
            }

            return back()->withErrors([
                'rate_limit' => "AI service is busy. Please try again in {$availableIn} seconds.",
            ]);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
