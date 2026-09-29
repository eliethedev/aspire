<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified_with_valid_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $user->generateEmailVerificationCode();

        Event::fake();

        $response = $this->actingAs($user)->post(route('verification.verify'), [
            'code' => $code,
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull($user->fresh()->email_verification_code);
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Your email has been successfully verified! You can now log in.');
    }

    public function test_email_is_not_verified_with_wrong_code(): void
    {
        $user = User::factory()->unverified()->create();
        $user->generateEmailVerificationCode();

        $this->actingAs($user)
            ->post(route('verification.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_code_is_rejected_and_a_fresh_one_is_sent(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $user->generateEmailVerificationCode();
        $user->forceFill(['email_verification_expires_at' => now()->subMinute()])->save();

        $this->actingAs($user)
            ->post(route('verification.verify'), ['code' => $code])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        // A fresh code replaces the expired one.
        $this->assertTrue($user->fresh()->email_verification_expires_at->isFuture());
        $this->assertFalse(Hash::check($code, $user->fresh()->email_verification_code));
    }

    public function test_guest_can_verify_with_email_and_code(): void
    {
        $user = User::factory()->unverified()->create();
        $code = $user->generateEmailVerificationCode();

        Event::fake();

        $response = $this->post(route('verification.verify'), [
            'email' => $user->email,
            'code' => $code,
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('login'));
    }

    public function test_verification_code_can_be_resent(): void
    {
        $user = User::factory()->unverified()->create();
        $oldCode = $user->generateEmailVerificationCode();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-code-sent');

        $this->assertFalse(Hash::check($oldCode, $user->fresh()->email_verification_code));
    }

    public function test_resend_is_limited_to_once_per_25_seconds(): void
    {
        $user = User::factory()->unverified()->create();
        $user->generateEmailVerificationCode();

        $this->actingAs($user)->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-code-sent');

        $hashAfterFirst = $user->fresh()->email_verification_code;

        // Immediate second resend is blocked — no new code is issued.
        $this->actingAs($user)->post(route('verification.send'))
            ->assertSessionHas('status', 'verification-code-cooldown');

        $this->assertSame($hashAfterFirst, $user->fresh()->email_verification_code);
    }
}
