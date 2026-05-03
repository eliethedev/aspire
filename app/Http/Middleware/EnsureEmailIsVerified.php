<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        if ($request->user() && !$request->user()->hasVerifiedEmail()) {
            return $request->user()->email 
                ? Redirect::route('verification.notice')
                : Redirect::route('login');
        }

        return $next($request);
    }
}
