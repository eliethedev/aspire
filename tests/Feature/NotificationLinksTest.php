<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_head_gets_accessible_lesson_plan_link(): void
    {
        Storage::fake('public');

        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);
        $headUser = User::factory()->schoolHead()->create(['school_id' => $school->id]);

        $observation = \App\Models\Observation::create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->addWeek()->toDateString(),
            'school_year' => '2026-2027',
            'subject' => 'Mathematics',
            'stage' => 'pre_observation_planning',
            'status' => 'scheduled',
            'sync_source' => 'online',
            'sync_status' => 'synced',
            'ai_status' => 'none',
            'server_version' => 1,
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        $this->actingAs($teacherUser)
            ->post(route('teacher.observations.upload-lesson-plan', $observation), [
                'lesson_plan_file' => UploadedFile::fake()->create('lesson-plan.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        // School head link must stay inside school-head routes — the supervisor
        // route bounces them on the role middleware.
        $headNotification = \App\Models\Notification::where('user_id', $headUser->id)
            ->where('title', 'Lesson Plan Uploaded')
            ->latest('id')
            ->firstOrFail();
        $this->assertStringContainsString('school-head/observations', $headNotification->link);
        $this->assertStringNotContainsString('supervisor/observations', $headNotification->link);

        // Supervisor keeps the supervisor link.
        $supNotification = \App\Models\Notification::where('user_id', $supervisor->id)
            ->where('title', 'Lesson Plan Uploaded')
            ->latest('id')
            ->firstOrFail();
        $this->assertStringContainsString('supervisor/observations', $supNotification->link);

        // And the school head can actually open it (read-only, same school).
        $this->actingAs($headUser)
            ->get($headNotification->link)
            ->assertOk();
    }

    public function test_career_assessment_links_match_recipient_roles(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);
        $headUser = User::factory()->schoolHead()->create(['school_id' => $school->id]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        $this->actingAs($supervisor)
            ->post(route('supervisor.teachers.career-assessment', $teacher), [
                'status' => \App\Models\CareerProgressionAssessment::STATUS_FOR_REVIEW,
                'remarks' => 'Links test.',
            ])
            ->assertRedirect();

        $headNotification = \App\Models\Notification::where('user_id', $headUser->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertStringContainsString('school-head/teachers', $headNotification->link);
        $this->assertStringNotContainsString('supervisor/teachers', $headNotification->link);

        $teacherNotification = \App\Models\Notification::where('user_id', $teacherUser->id)
            ->latest('id')
            ->firstOrFail();
        $this->assertStringNotContainsString('supervisor/teachers', $teacherNotification->link);

        // Both recipients can open their own links.
        $this->actingAs($headUser)->get($headNotification->link)->assertOk();
        $this->actingAs($teacherUser)->get($teacherNotification->link)->assertOk();
    }
}
