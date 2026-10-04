<?php

namespace Tests\Feature;

use App\Models\AiFeedback;
use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolHeadFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $schoolHeadUser;

    protected Teacher $teacher;

    protected Observation $observation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->schoolHeadUser = User::factory()->schoolHead()->create(['school_id' => $this->school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $this->school->id]);
        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create(['user_id' => $teacherUser->id]);

        UserProfile::create(['user_id' => $this->schoolHeadUser->id, 'mobile_number' => '09170000011']);
        SchoolHeadProfile::create([
            'user_id' => $this->schoolHeadUser->id,
            'school_id' => $this->school->id,
            'position_level' => 'principal_i',
            'current_designation' => 'principal',
            'school_type' => 'elementary',
        ]);

        $this->observation = Observation::factory()->create([
            'observer_id' => $this->schoolHeadUser->id,
            'observer_type' => User::class,
            'observee_id' => $this->teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'completed',
        ]);
    }

    public function test_school_head_can_view_feedback_details(): void
    {
        $feedback = AiFeedback::create([
            'observation_id' => $this->observation->id,
            'feedback_type' => 'post_observation',
            'analysis' => 'Detailed analysis here.',
            'strengths' => ['Clear objectives'],
            'areas_for_improvement' => [],
            'recommendations' => ['Keep it up'],
            'confidence_score' => 0.9,
            'generated_by' => 'ai',
            'status' => 'published',
        ]);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.list'))
            ->assertOk()
            ->assertSee($this->teacher->user->name, false)
            ->assertSee('1 feedback', false)
            ->assertSee('Post-Observation', false)
            ->assertSee('View Feedback', false);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.list', ['teacher' => $this->teacher->id]))
            ->assertOk()
            ->assertSee('View Details', false)
            ->assertSee('Feedback Management', false)
            ->assertSee('Create Improvement Plan', false);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.show', $feedback))
            ->assertOk()
            ->assertSee('Detailed analysis here.', false)
            ->assertSee('Clear objectives', false);
    }

    public function test_grouped_list_filters_by_feedback_type(): void
    {
        $otherUser = User::factory()->teacher()->create(['school_id' => $this->school->id, 'name' => 'Filter Target Teacher']);
        $otherTeacher = Teacher::factory()->forSchool($this->school->id)->create(['user_id' => $otherUser->id]);
        $otherObservation = Observation::factory()->create([
            'observer_id' => $this->schoolHeadUser->id,
            'observer_type' => User::class,
            'observee_id' => $otherTeacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'completed',
        ]);

        AiFeedback::create([
            'observation_id' => $this->observation->id,
            'feedback_type' => 'post_observation',
            'analysis' => 'Post analysis.',
            'generated_by' => 'ai',
            'status' => 'published',
        ]);
        AiFeedback::create([
            'observation_id' => $otherObservation->id,
            'feedback_type' => 'pre_observation',
            'analysis' => 'Pre analysis.',
            'generated_by' => 'ai',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.list', ['type' => 'pre_observation']))
            ->assertOk();

        $response->assertSee($otherUser->name, false);
        // Exactly one teacher row (sidebar nav may repeat names, so count rows).
        $this->assertSame(1, substr_count($response->getContent(), 'View Feedback'));
    }

    public function test_school_head_cannot_view_other_schools_feedback_details(): void
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
        $otherFeedback = AiFeedback::create([
            'observation_id' => $otherObservation->id,
            'feedback_type' => 'post_observation',
            'analysis' => 'Other school analysis.',
            'generated_by' => 'ai',
            'status' => 'published',
        ]);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.show', $otherFeedback))
            ->assertForbidden();
    }

    public function test_school_head_can_open_feedback_management_for_own_observation(): void
    {
        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.index', $this->observation))
            ->assertOk()
            ->assertSee('Feedback Management', false);
    }

    public function test_school_head_can_create_edit_and_publish_feedback(): void
    {
        // Create a manual draft entry.
        $create = $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.feedback.create', [$this->observation, 'type' => 'post_observation']));

        $feedback = AiFeedback::where('observation_id', $this->observation->id)->first();
        $this->assertNotNull($feedback);
        $this->assertSame('school_head', $feedback->generated_by);
        $create->assertRedirect(route('school-head.feedback.edit', [$this->observation, $feedback]));

        // Update the draft.
        $this->actingAs($this->schoolHeadUser)
            ->patch(route('school-head.feedback.update', [$this->observation, $feedback]), [
                'analysis' => 'Strong lesson delivery observed.',
                'strengths' => ['Clear objectives'],
                'areas_for_improvement' => ['Pacing'],
                'recommendations' => ['Use timers'],
            ])
            ->assertRedirect(route('school-head.feedback.index', $this->observation));

        $this->assertSame('Strong lesson delivery observed.', $feedback->fresh()->analysis);

        // Publish it.
        $this->actingAs($this->schoolHeadUser)
            ->post(route('school-head.feedback.publish', [$this->observation, $feedback]))
            ->assertRedirect(route('school-head.feedback.index', $this->observation));

        $this->assertSame('published', $feedback->fresh()->status);
    }

    public function test_school_head_cannot_manage_feedback_for_other_schools_observation(): void
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
            ->get(route('school-head.feedback.index', $otherObservation))
            ->assertForbidden();
    }

    public function test_teacher_cannot_access_school_head_feedback_management(): void
    {
        $teacherUser = $this->teacher->user;

        UserProfile::create(['user_id' => $teacherUser->id, 'mobile_number' => '09170000022']);

        $this->actingAs($teacherUser)
            ->get(route('school-head.feedback.index', $this->observation))
            ->assertForbidden();
    }
}
