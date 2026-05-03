<?php

namespace Tests\Feature\Auth;

use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        
        // Verify role selection dropdown is not present
        $response->assertDontSee('name="role"');
        $response->assertDontSee('Select Role');
        $response->assertSee('School');
        $response->assertSee('Department');
        $response->assertSee('Years of Service');
    }

    public function test_new_users_can_register_as_teacher_only(): void
    {
        // Create a school for testing
        $school = School::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'department' => 'Mathematics',
            'years_of_service' => 5,
            'employee_number' => 'DEPED-2023-001',
            'mobile_number' => '09123456789',
            'prc_license_number' => '1234567',
            'position' => 'Teacher I',
        ]);

        // User should be created but not verified
        $this->assertGuest();
        $response->assertRedirect('/verify-email');
        
        // Verify user was created with teacher role
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'teacher', // Must be teacher
            'school_id' => $school->id,
            'email_verified_at' => null,
        ]);

        // Verify teacher profile was created
        $this->assertDatabaseHas('teachers', [
            'department' => 'Mathematics',
            'years_of_service' => 5,
            'school_id' => $school->id,
            'employee_number' => 'DEPED-2023-001',
            'mobile_number' => '09123456789',
            'prc_license_number' => '1234567',
            'position' => 'Teacher I',
        ]);
    }

    public function test_registration_requires_school_id(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'department' => 'Mathematics',
            'years_of_service' => 5,
        ]);

        $response->assertSessionHasErrors('school_id');
        
        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_registration_requires_teacher_fields(): void
    {
        $school = School::factory()->create();

        // Test missing department
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'years_of_service' => 5,
        ]);

        $response->assertSessionHasErrors('department');

        // Test missing years of service
        $response = $this->post('/register', [
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'department' => 'Mathematics',
        ]);

        $response->assertSessionHasErrors('years_of_service');
    }

    public function test_registration_prevents_role_parameter(): void
    {
        $school = School::factory()->create();

        // Attempt to register with role parameter
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'department' => 'Mathematics',
            'years_of_service' => 5,
            'role' => 'admin', // Try to override role
        ]);

        $response->assertSessionHasErrors('role');
        
        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_registration_prevents_admin_identifiers(): void
    {
        $school = School::factory()->create();

        // Test with admin-like email
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'admin@school.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'department' => 'Mathematics',
            'years_of_service' => 5,
        ]);

        $response->assertSessionHasErrors('security');
        
        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'admin@school.com',
        ]);
    }

    public function test_registration_requires_valid_mobile_number(): void
    {
        $school = School::factory()->create();

        // Test with invalid mobile number
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'school_id' => $school->id,
            'department' => 'Mathematics',
            'years_of_service' => 5,
            'mobile_number' => '123456789', // Invalid - doesn't start with 09
        ]);

        $response->assertSessionHasErrors('mobile_number');
        
        // Verify user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }
}
