<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitInvitation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts   Default: 5 attempts
     * @param  int  $decayMinutes  Default: 1 minute
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 5, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }

    /**
     * Create a unique rate limit key for invitations.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $email = $request->input('email') ?? $request->input('invitee_email');
        $token = $request->input('token') ?? '';

        // Prioritize email if available (best for invitation sending)
        if ($email) {
            return sha1("invitation:send:{$email}");
        }

        // Fallback to IP + token + path
        return sha1(
            "invitation:{$request->ip()}:{$request->path()}:{$token}"
        );
    }

    /**
     * Build rate limit exceeded response.
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $seconds = RateLimiter::availableIn($key);

        return response()->json([
            'message' => 'Too many invitation attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
            'retry_after' => $seconds,
        ], 429);
    }
}