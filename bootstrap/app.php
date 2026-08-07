<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\NoStoreHeaders::class,
        ]);

        $middleware->alias([
            'school' => \App\Http\Middleware\IdentifySchool::class,
            'require.school' => \App\Http\Middleware\RequireSchool::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'ai.rate.limit' => \App\Http\Middleware\AIRateLimitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // When a stale CSRF token from a previous session is submitted,
        // show a friendly page instead of a bare "419 Page Expired" error.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                ], 419);
            }

            // Submitting the login form itself with a stale token: send back
            // to login with a message so the user can simply try again.
            if ($request->isMethod('POST') && $request->route() && $request->route()->named('login')) {
                return redirect()->back()->withErrors([
                    'email' => 'Your session has expired. Please try signing in again.',
                ]);
            }

            return response()->view('errors.419', [], 419);
        });
    })->create();
