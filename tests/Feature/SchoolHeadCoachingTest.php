<?php

namespace Tests\Feature;

use App\Models\CoachingAgreement;
use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\SupervisorProfile;
use App\Models\Teacher;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolHeadCoachingTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $schoolHeadUser;

    protected User $teacherUser;

    protected Teacher $teacher;

    protected Observation $observation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->schoolHeadUser = User::factory()->schoolHead()->create(['school_id' => $this->school->id]);
        $this->teacherUser = User::factory()->teacher()->create(['school_id' => $this->school->id]);
        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create([
            'user_id' => $this->teacherUser->id,
            'grade_level' => '7',
        ]);

        UserProfile::create(['user_id' => $this->schoolHeadUser->id, 'mobile_number' => '09170000011']);
        SchoolHeadProfile::create([
            'user_id' => $this->schoolHeadUser->id,
            'school_id' => $this->school->id,
            'position_level' => 'principal_i',
            'current_designation' => 'principal',
            'school_type' => 'elementary',
        ]);

        // Complete teacher profile so profile.complete middleware passes.
        UserProfile::create([
            'user_id' => $this->teacherUser->id,
            'mobile_number' => '09170000022',
            'employment_status' => 'permanent',
        ]);
        TeacherProfile::create(['user_id' => $this->teacherUser->id, 'default_room' => 'Room 101']);

        $this->observation = Observation::factory()->create([
            'observer_id' => $this->schoolHeadUser->id,
            'observer_type' => User::class,
            'observee_id' => $this->teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'completed',
        ]);
    }

    public function test_school_head_can_create_improvement_plan(): void
    {
        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.coaching.create', $this->observation))
            ->assertOk()
            ->assertSee('Create Coaching Agreement', false);

        $response = $this->actingAs($this->schoolHeadUser)
            ->post(route('school-head.coaching.store'), [
                'observation_id' => $this->observation->id,
                'focus_areas' => ['Classroom management'],
                'action_steps' => ['Use entry routine'],
                'timeline' => '4 weeks',
            ]);

        $agreement = CoachingAgreement::where('observation_id', $this->observation->id)->first();
        $this->assertNotNull($agreement);
        $this->assertSame('draft', $agreement->status);
        $this->assertSame($this->schoolHeadUser->id, (int) $agreement->supervisor_id);
        $response->assertRedirect(route('school-head.coaching.show', $agreement));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->teacherUser->id,
            'title' => 'New improvement plan needs your review',
        ]);
    }

    public function test_full_sign_complete_reopen_cycle(): void
    {
        $agreement = CoachingAgreement::create([
            'observation_id' => $this->observation->id,
            'teacher_id' => $this->teacher->id,
            'supervisor_id' => $this->schoolHeadUser->id,
            'focus_areas' => ['Pacing'],
            'status' => 'draft',
        ]);

        // School head signs first.
        $this->actingAs($this->schoolHeadUser)
            ->post(route('school-head.coaching.sign', $agreement), ['signature' => 'Principal Name'])
            ->assertRedirect(route('school-head.coaching.show', $agreement));

        $this->assertNotNull($agreement->fresh()->supervisor_signed_at);
        $this->assertSame('draft', $agreement->fresh()->status);

        // Teacher signs → plan becomes active.
        $this->actingAs($this->teacherUser)
            ->post(route('teacher.coaching.sign', $agreement), ['signature' => 'Teacher Name'])
            ->assertRedirect(route('teacher.coaching.show', $agreement));

        $this->assertSame('active', $agreement->fresh()->status);

        // Completed plans cannot be edited.
        $this->actingAs($this->schoolHeadUser)
            ->post(route('school-head.coaching.complete', $agreement))
            ->assertRedirect(route('school-head.coaching.show', $agreement));

        $this->assertSame('completed', $agreement->fresh()->status);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.coaching.edit', $agreement))
            ->assertRedirect();

        // Reopen for follow-up.
        $this->actingAs($this->schoolHeadUser)
            ->post(route('school-head.coaching.reopen', $agreement))
            ->assertRedirect(route('school-head.coaching.show', $agreement));

        $this->assertSame('active', $agreement->fresh()->status);
    }

    public function test_school_head_cannot_manage_other_schools_plan(): void
    {
        $otherSchool = School::factory()->create();
        $otherTeacherUser = User::factory()->teacher()->create(['school_id' => $otherSchool->id]);
        $otherTeacher = Teacher::factory()->forSchool($otherSchool->id)->create(['user_id' => $otherTeacherUser->id]);
        $otherObservation = Observation::factory()->create([
            'observer_id' => $this->schoolHeadUser->id,
            'observer_type' => User::class,
            'observee_id' => $otherTeacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'completed',
        ]);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.coaching.create', $otherObservation))
            ->assertForbidden();
    }

    public function test_teacher_cannot_create_school_head_plan(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('school-head.coaching.create', $this->observation))
            ->assertForbidden();
    }

    public function test_create_accepts_blank_dynamic_rows(): void
    {
        // The form always submits at least one empty row per dynamic list,
        // which the empty-string middleware turns into null.
        $response = $this->actingAs($this->schoolHeadUser)
            ->post(route('school-head.coaching.store'), [
                'observation_id' => $this->observation->id,
                'focus_areas' => [null],
                'action_steps' => [null],
                'timeline' => '4 weeks',
            ]);

        $agreement = CoachingAgreement::where('observation_id', $this->observation->id)->first();
        $this->assertNotNull($agreement);
        $this->assertSame([], $agreement->focus_areas ?? []);
        $this->assertSame([], $agreement->action_steps ?? []);
        $response->assertRedirect(route('school-head.coaching.show', $agreement));
    }

    public function test_supervisor_complete_and_reopen_cycle(): void
    {
        $supervisor = User::factory()->supervisor()->create(['school_id' => $this->school->id]);
        UserProfile::create(['user_id' => $supervisor->id, 'mobile_number' => '09170000033']);
        SupervisorProfile::create([
            'user_id' => $supervisor->id,
            'division_district_assigned' => 'District 1',
            'area_of_specialization' => 'Mathematics',
            'supervisory_level' => 'district',
        ]);

        $observation = Observation::factory()->create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $this->teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'completed',
        ]);

        $agreement = CoachingAgreement::create([
            'observation_id' => $observation->id,
            'teacher_id' => $this->teacher->id,
            'supervisor_id' => $supervisor->id,
            'focus_areas' => ['Pacing'],
            'teacher_signature' => 'Teacher Name',
            'teacher_signed_at' => now(),
            'supervisor_signature' => 'Supervisor Name',
            'supervisor_signed_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($supervisor)
            ->post(route('supervisor.coaching.complete', $agreement))
            ->assertRedirect(route('supervisor.coaching.show', $agreement));

        $this->assertSame('completed', $agreement->fresh()->status);

        $this->actingAs($supervisor)
            ->post(route('supervisor.coaching.reopen', $agreement))
            ->assertRedirect(route('supervisor.coaching.show', $agreement));

        $this->assertSame('active', $agreement->fresh()->status);
    }
}
