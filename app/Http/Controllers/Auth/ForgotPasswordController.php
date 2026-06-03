<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Display the forgot password form.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the forgot password request.
     * 
     * This re-triggers the invitation process for existing users.
     * Prevents timing attacks by always showing success message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Use a small delay to prevent timing attacks
        // This ensures the response time is similar whether the email exists or not
        usleep(500000); // 0.5 seconds

        // Find the user by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // User exists - generate new invitation
            DB::transaction(function () use ($user) {
                // Invalidate any previous unused invitations for this user
                Invitation::where('user_id', $user->id)
                    ->where('is_used', false)
                    ->update(['is_used' => true]);

                // Create new invitation
                $invitation = Invitation::create([
                    'user_id' => $user->id,
                    'invited_by' => null, // System-generated, not by a specific admin
                    'school_id' => $user->school_id,
                    'token' => Invitation::generateToken(),
                    'email' => $user->email,
                    'role' => $user->role,
                    'expires_at' => now()->addDays(7),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Send invitation email
                $user->notify(new UserInvitation($invitation));

                // Notify admins about password reset request
                $this->notifyAdmins($user, $invitation);
            });
        }

        // Always return success message to prevent timing attacks
        return back()
            ->with('status', 'If your email is registered, a new invitation has been sent to reset your password. Please check your inbox.');
    }

    /**
     * Notify admins about a password reset request.
     */
    protected function notifyAdmins(User $user, Invitation $invitation)
    {
        // Get all admin users
        $admins = User::where('role', 'admin')
            ->where('status', 'active')
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\PasswordResetRequestNotification($user, $invitation));
        }
    }
}
