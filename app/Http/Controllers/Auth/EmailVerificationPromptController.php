<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // If user is authenticated, check if they're verified
        if ($request->user()) {
            if ($request->user()->hasVerifiedEmail()) {
                // User is verified, redirect to dashboard
                return redirect()->route('dashboard');
            } else {
                // User is authenticated but not verified, show verification page
                return view('auth.verify-email');
            }
        }
        
        // If user is not authenticated, show verification notice page
        // This handles the case where user just registered but isn't logged in yet
        return view('auth.verify-email');
    }
}
