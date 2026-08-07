<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_to_five_attempts_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_password_reset_is_rate_limited_to_three_requests_per_fifteen_minutes(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('password.email'), [
                'email' => 'reset@example.com',
            ])->assertStatus(302);
        }

        $this->post(route('password.email'), [
            'email' => 'reset@example.com',
        ])->assertStatus(429);
    }

    public function test_verification_email_resend_is_rate_limited_to_five_per_hour(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'email_verified_at' => null,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->post(route('verification.send'))
                ->assertStatus(302);
        }

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertStatus(429);
    }

    public function test_admin_invitation_creation_is_rate_limited_to_ten_per_hour(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($admin)->post(route('admin.invitations.store'), [
                'name' => "Invitee {$i}",
                'email' => "invitee{$i}@example.com",
                'role' => 'teacher',
            ])->assertRedirect(route('admin.invitations.index'));
        }

        $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'name' => 'Invitee 10',
            'email' => 'invitee10@example.com',
            'role' => 'teacher',
        ])->assertStatus(429);
    }

    public function test_lesson_plan_uploads_are_rate_limited_to_twenty_per_hour(): void
    {
        Storage::fake('public');

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $observer = User::factory()->create(['role' => 'supervisor']);

        $observation = Observation::factory()
            ->forObserver($observer)
            ->forObservee($teacher)
            ->create(['stage' => 'pre_observation_planning']);

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($teacherUser)->post(
                route('teacher.observations.upload-lesson-plan', $observation),
                ['lesson_plan_file' => UploadedFile::fake()->create("plan{$i}.pdf", 10)]
            )->assertSessionHas('success');
        }

        $this->actingAs($teacherUser)->post(
            route('teacher.observations.upload-lesson-plan', $observation),
            ['lesson_plan_file' => UploadedFile::fake()->create('plan-exceeded.pdf', 10)]
        )->assertStatus(429);
    }

    public function test_report_exports_are_rate_limited_to_ten_per_ten_minutes(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($supervisor)
                ->get(route('supervisor.reports.export'))
                ->assertStatus(200);
        }

        $this->actingAs($supervisor)
            ->get(route('supervisor.reports.export'))
            ->assertStatus(429);
    }

    public function test_observation_search_is_rate_limited_to_sixty_per_minute(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($supervisor)
                ->get(route('supervisor.observations.index'))
                ->assertStatus(200);
        }

        $this->actingAs($supervisor)
            ->get(route('supervisor.observations.index'))
            ->assertStatus(429);
    }
}
