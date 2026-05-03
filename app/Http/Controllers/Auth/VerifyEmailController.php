<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use App\Http\Requests\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email has already been verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'Your email has been successfully verified! You can now log in.');
    }
}
