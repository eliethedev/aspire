<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SingleEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a school for testing
        School::factory()->create();
        
        // Fake notifications to count how many are sent
        Notification::fake();
    }

    public function test_registration_sends_only_one_verification_email()
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
        
        // Assert that only one verification notification was sent
        Notification::assertSentToTimes(
            User::where('email', 'teacher@example.com')->first(),
            \App\Notifications\VerifyEmailPHPMailer::class,
            1 // Should be exactly 1 time
        );
        
        // Ensure user was created but not verified
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.com',
            'email_verified_at' => null,
        ]);
    }
}
