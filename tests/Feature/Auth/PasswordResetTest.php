<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, UserInvitation::class);

        $this->assertDatabaseHas('invitations', [
            'user_id' => $user->id,
            'email' => $user->email,
            'is_used' => false,
        ]);
    }

    public function test_reset_password_link_for_unknown_email_does_not_fail(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertSessionHas('status');
        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $token = Password::broker()->createToken($user);

        $response = $this->get('/reset-password/'.$token);

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(auth()->attempt([
            'email' => $user->email,
            'password' => 'new-password',
        ]));
    }
}
