<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified using a 6-digit code.
     *
     * Works for signed-in users (code only) and guests (email + code),
     * mirroring the old signed-link flow which required no session.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
        ]);

        $user = $request->user();

        if (! $user) {
            if (! $request->filled('email')) {
                return back()->withErrors(['email' => 'Please enter the email address the code was sent to.'])->withInput();
            }

            $user = User::where('email', $request->input('email'))->first();

            if (! $user) {
                return back()->withErrors(['code' => 'We could not find an account for that email and code combination.'])->withInput();
            }
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email has already been verified.');
        }

        if (! $user->hasValidEmailVerificationCode($request->input('code'))) {
            $expired = $user->email_verification_expires_at && $user->email_verification_expires_at->isPast();

            $message = $expired
                ? 'That code has expired. We have sent you a fresh one — please check your inbox.'
                : 'That code is incorrect. Please check your email and try again.';

            if ($expired) {
                $user->sendEmailVerificationNotification();
            }

            return back()->withErrors(['code' => $message])->withInput();
        }

        $user->clearEmailVerificationCode();

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'Your email has been successfully verified! You can now log in.');
    }
}
