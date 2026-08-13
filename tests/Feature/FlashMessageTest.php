<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticatedAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * The toast region is rendered on every authenticated layout, so a
     * flashed message is visible without any page-level markup.
     */
    public function test_success_flash_message_is_rendered_as_toast(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['success' => 'Observation saved successfully.'])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Observation saved successfully.');
        $response->assertSee('data-flash-toast-region');
        $response->assertSessionHas('success');
    }

    public function test_error_flash_message_is_rendered_as_toast(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['error' => 'Unable to save the observation. Please try again.'])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Unable to save the observation. Please try again.');
    }

    public function test_warning_flash_message_is_rendered_as_toast(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['warning' => 'Please complete all required fields.'])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Please complete all required fields.');
    }

    public function test_info_flash_message_is_rendered_as_toast(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['info' => 'AI feedback is being generated.'])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('AI feedback is being generated.');
    }

    public function test_generic_message_key_is_supported(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['message' => 'Your changes have been saved.'])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Your changes have been saved.');
    }

    public function test_multiple_flash_messages_are_rendered_together(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession([
                'success' => 'Teacher created successfully.',
                'warning' => 'Please verify the teacher email.',
            ])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Teacher created successfully.');
        $response->assertSee('Please verify the teacher email.');
    }

    public function test_flash_message_is_available_in_the_session_for_compatibility(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['success' => 'School updated successfully.'])
            ->get(route('admin.dashboard'));

        $response->assertSessionHas('success', 'School updated successfully.');
    }

    public function test_toast_component_is_registered_in_every_authenticated_layout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        \App\Models\Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-flash-toast-region');

        $this->actingAs($teacherUser)->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('data-flash-toast-region');

        $this->actingAs($supervisor)->get(route('supervisor.dashboard'))
            ->assertOk()
            ->assertSee('data-flash-toast-region');
    }

    public function test_frontend_toast_helper_is_exposed_for_ajax_flows(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('window.showToast');
        $response->assertSee('flashToasts');
    }

    public function test_flash_message_content_is_escaped_against_xss(): void
    {
        $admin = $this->authenticatedAdmin();

        $response = $this->actingAs($admin)
            ->withSession(['error' => '<script>alert("xss")</script>'])
            ->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
    }
}
