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
        // Create a verified user
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        
        // Ensure user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_redirects_to_verification_notice()
    {
        $school = School::factory()->create();
        
        $response = $this->post('/register', [
            'name' => 'Test Teacher',
            'email' => 'teacher@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'department' => 'Science',
            'years_of_service' => 5,
            'employee_number' => 'EMP001',
            'mobile_number' => '09123456789',
            'prc_license_number' => 'PRC123456',
            'position' => 'Teacher I',
        ]);

        $response->assertRedirect('/verify-email');
        
        // Ensure user is not authenticated after registration
        $this->assertGuest();
        
        // Verify user was created but not verified
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.com',
            'email_verified_at' => null,
        ]);
    }
}
