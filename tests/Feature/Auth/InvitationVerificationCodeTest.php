<?php

namespace Tests\Feature\Auth;

use App\Models\Invitation;
use App\Models\User;
use App\Notifications\VerifyEmailPHPMailer;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_accept_sends_initial_code_that_verifies_without_resend(): void
    {
        $user = User::factory()->unverified()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'maria@example.com',
            'role' => 'teacher',
        ]);

        Notification::fake();
        Event::fake();

        // Regression: no code was emailed at invitation-accept time, so the
        // code entry page was useless until "Resend" was clicked.
        $this->post(route('auth.set-password.store'), [
            'token' => $invitation->token,
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
            'terms' => '1',
        ])->assertRedirect(route('dashboard'));

        $sentCode = null;
        Notification::assertSentTo(
            $user->fresh(),
            VerifyEmailPHPMailer::class,
            function (VerifyEmailPHPMailer $notification) use (&$sentCode) {
                $sentCode = $notification->getCode();
                return $sentCode !== null;
            }
        );
        $this->assertNotNull($user->fresh()->email_verification_code);

        // The FIRST code works straight away — no resend needed.
        $this->post(route('verification.verify'), [
            'email' => 'maria@example.com',
            'code' => $sentCode,
        ])->assertRedirect(route('login'));

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
