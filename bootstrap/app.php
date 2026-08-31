<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthorizationException;

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
            'profile.complete' => \App\Http\Middleware\EnsureProfileComplete::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'ai.rate.limit' => \App\Http\Middleware\AIRateLimitMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // CSRF token mismatch — show friendly session-expired page.
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                ], 419);
            }

            if ($request->isMethod('POST') && $request->route() && $request->route()->named('login')) {
                return redirect()->back()->withErrors([
                    'email' => 'Your session has expired. Please try signing in again.',
                ]);
            }

            return response()->view('errors.419', [], 419);
        });

        // 404 — Model not found or route not matched.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'The requested resource was not found.'], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'The requested resource was not found.'], 404);
            }

            return response()->view('errors.404', [], 404);
        });

        // 403 — Authorization denied.
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'You are not authorized to perform this action.'], 403);
            }

            return response()->view('errors.403', [], 403);
        });

        // HTTP exceptions (403, 404, 405, 429, 503, etc.) — use the
        // status-specific error page when one exists, otherwise fall back
        // to the generic 500 page.
        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            $view = view()->exists("errors.{$status}") ? "errors.{$status}" : 'errors.500';

            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => $e->getMessage() ?: 'An error occurred.'], $status);
            }

            return response()->view($view, [], $status);
        });

        // Catch-all: any unhandled exception shows the friendly 500 page
        // in production. Details are still logged by Laravel's log channel.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (config('app.debug')) {
                return null; // Let Laravel's Ignition/Whoops handle it in dev.
            }

            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Something went wrong. Please try again later.'], 500);
            }

            return response()->view('errors.500', [], 500);
        });
    })->create();
