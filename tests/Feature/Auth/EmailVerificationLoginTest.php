<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a school for testing
        School::factory()->create();
    }

    public function test_unverified_user_cannot_login()
    {
        // Create an unverified user
        $user = User::factory()->unverified()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('email', 'Please verify your email address first. Check your inbox for the verification link.');
        
        // Ensure user is not authenticated
        $this->assertGuest();
    }

    public function test_verified_user_can_login()
    {
        // Create a verified admin user
        $user = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));

        // Ensure user is authenticated
        $this->assertAuthenticatedAs($user);
    }
}
