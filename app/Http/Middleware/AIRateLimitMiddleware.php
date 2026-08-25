<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AIRateLimitMiddleware
{
    /**
     * Global per-user AI limits. Task-specific limits are enforced
     * separately in AIService::checkRateLimit() using dedicated keys,
     * so one task never blocks unrelated tasks.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $userId = $user?->id ?? 'guest';

        $perMinuteKey = "ai:global:{$userId}";
        $perHourKey = "ai:global-hourly:{$userId}";

        $perMinute = config('ai.rate_limits.per_minute', 30);
        $perHour = config('ai.rate_limits.per_hour', 200);

        $blockedKey = null;
        $availableIn = 0;

        if (RateLimiter::tooManyAttempts($perMinuteKey, $perMinute)) {
            $blockedKey = $perMinuteKey;
            $availableIn = RateLimiter::availableIn($perMinuteKey);
        } elseif (RateLimiter::tooManyAttempts($perHourKey, $perHour)) {
            $blockedKey = $perHourKey;
            $availableIn = RateLimiter::availableIn($perHourKey);
        }

        if ($blockedKey !== null) {
            return $this->reject($request, $availableIn);
        }

        RateLimiter::hit($perMinuteKey, 60);
        RateLimiter::hit($perHourKey, 3600);

        return $next($request);
    }

    protected function reject(Request $request, int $availableIn): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'AI service is busy. Please try again in a moment.',
                'retry_after' => $availableIn,
            ], 429)->withHeaders([
                'Retry-After' => (string) $availableIn,
            ]);
        }

        return back()->withErrors([
            'rate_limit' => "AI service is busy. Please try again in {$availableIn} seconds.",
        ]);
    }
}
