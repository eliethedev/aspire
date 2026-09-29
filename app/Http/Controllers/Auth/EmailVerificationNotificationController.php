<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Seconds a user must wait between verification code resends.
     */
    public const RESEND_COOLDOWN_SECONDS = 25;

    /**
     * Send a new 6-digit email verification code (max once per cooldown).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user && $request->filled('email')) {
            $user = User::where('email', $request->input('email'))->first();
        }

        if (! $user) {
            return back()->withErrors(['email' => 'We could not find an account for that email address.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $cooldownKey = 'verification-resend:'.$user->getKey();

        if (Cache::has($cooldownKey)) {
            return back()->with('status', 'verification-code-cooldown');
        }

        $user->sendEmailVerificationNotification();

        Cache::put($cooldownKey, true, self::RESEND_COOLDOWN_SECONDS);

        return back()->with('status', 'verification-code-sent');
    }
}
