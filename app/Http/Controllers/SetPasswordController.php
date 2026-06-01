<?php

namespace App\Http\Controllers;

use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class SetPasswordController extends Controller
{
    protected InvitationService $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Show the form for setting password via invitation.
     */
    public function show(string $token)
    {
        try {
            $invitation = $this->invitationService->validateToken($token);
            $invitation->load(['school', 'invitedBy']);

            return view('auth.set-password', [
                'token' => $token,
                'email' => $invitation->email,
                'name' => $invitation->user->name,
                'role' => $invitation->role,
                'school' => $invitation->school,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return view('auth.invitation-error', [
                'error' => $e->errors()['token'][0] ?? 'Invalid invitation',
            ]);
        }
    }

    /**
     * Set the user's password and accept the invitation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        try {
            $user = $this->invitationService->acceptInvitation(
                $request->token,
                $request->password,
                $request->ip(),
                $request->userAgent()
            );

            return redirect()
                ->route('dashboard')
                ->with('success', 'Your account has been set up successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
}
