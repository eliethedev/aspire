<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Offline clinical-supervision workflow for school-head (principal) observers,
 * mirroring the supervisor flow: capture page → package download →
 * offline encode → idempotent push.
 */
class SchoolHeadOfflineTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $schoolHeadUser;

    protected User $teacherUser;

    protected Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->schoolHeadUser = User::factory()->schoolHead()->create(['school_id' => $this->school->id]);
        $this->teacherUser = User::factory()->teacher()->create(['school_id' => $this->school->id]);
        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create(['user_id' => $this->teacherUser->id]);

        // Complete profiles so the profile.complete middleware lets the
        // requests through to the workflow itself.
        UserProfile::create(['user_id' => $this->schoolHeadUser->id, 'mobile_number' => '09170000011']);
        SchoolHeadProfile::create([
            'user_id' => $this->schoolHeadUser->id,
            'school_id' => $this->school->id,
            'position_level' => 'principal_i',
            'current_designation' => 'principal',
            'school_type' => 'elementary',
        ]);
    }

    protected function makeObservation(array $overrides = []): Observation
    {
        return Observation::factory()->create(array_merge([
            'observer_id' => $this->schoolHeadUser->id,
            'observer_type' => User::class,
            'observee_id' => $this->teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'pending_teacher_confirmation',
            'confirmation_status' => 'pending',
        ], $overrides));
    }

    public function test_school_head_offline_capture_page_renders(): void
    {
        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.observations.offline'))
            ->assertOk()
            ->assertSee('Offline observation visits')
            ->assertSee($this->teacherUser->name)
            ->assertSee('school-head/observations', false);
    }

    public function test_school_head_package_locked_before_teacher_confirmation(): void
    {
        $observation = $this->makeObservation();

        $this->actingAs($this->schoolHeadUser)
            ->getJson(route('school-head.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'teacher_confirmation');
    }

    protected function makeApprovableObservation(array $overrides = []): Observation
    {
        return $this->makeObservation(array_merge([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->schoolHeadUser->id,
            'lesson_plan_summary' => 'Objectives: fractions.',
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
        ], $overrides));
    }

    public function test_school_head_can_approve_suggestions(): void
    {
        $observation = $this->makeApprovableObservation();

        $this->actingAs($this->schoolHeadUser)
            ->postJson(route('school-head.observations.approve-suggestions', $observation), [
                'suggestions_reviewed' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $fresh = $observation->fresh();
        $this->assertNotNull($fresh->ai_suggestions_approved_at);
        $this->assertSame($this->schoolHeadUser->id, (int) $fresh->ai_suggestions_approved_by);
    }

    public function test_school_head_download_locked_until_approved(): void
    {
        $observation = $this->makeApprovableObservation();

        $this->actingAs($this->schoolHeadUser)
            ->getJson(route('school-head.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'ai_suggestions_approval');
    }

    public function test_school_head_package_download_returns_bundle(): void
    {
        $observation = $this->makeApprovableObservation([
            'ai_suggestions_approved_at' => now(),
            'ai_suggestions_approved_by' => $this->schoolHeadUser->id,
        ]);

        $response = $this->actingAs($this->schoolHeadUser)
            ->getJson(route('school-head.observations.offline-package', $observation));

        $response->assertOk()
            ->assertJsonPath('server_id', $observation->id)
            ->assertJsonPath('ai_approved', true)
            ->assertJsonStructure([
                'observation' => ['id', 'subject', 'teacher'],
                'rubric' => ['scale', 'scale_max', 'indicators'],
                'lesson_plan', 'ai_prompts',
            ]);

        $this->assertSame('downloaded_offline', $observation->fresh()->status);
    }

    public function test_school_head_offline_workspace_renders(): void
    {
        $observation = $this->makeApprovableObservation([
            'ai_suggestions_approved_at' => now(),
            'ai_suggestions_approved_by' => $this->schoolHeadUser->id,
        ]);

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.observations.offline-workspace', $observation))
            ->assertOk()
            ->assertSee('Offline Observation #'.$observation->id, false);
    }

    public function test_school_head_workspace_locked_until_approved(): void
    {
        $observation = $this->makeApprovableObservation();

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.observations.offline-workspace', $observation))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_school_head_push_creates_observation(): void
    {
        $clientId = (string) Str::uuid();
        $ratingClientId = (string) Str::uuid();

        $response = $this->actingAs($this->schoolHeadUser)->postJson('/sync/push', [
            'device_id' => 'test-tablet',
            'items' => [[
                'client_id' => $clientId,
                'device_updated_at' => now()->toIso8601String(),
                'payload' => [
                    'observation_type' => 'teacher_observation',
                    'observee_id' => $this->teacher->id,
                    'observation_date' => now()->toDateString(),
                    'subject' => 'Mathematics',
                    'ratings' => [[
                        'client_id' => $ratingClientId,
                        'indicator_code' => 'IND-1',
                        'domain' => 'Content Knowledge and Pedagogy',
                        'indicator' => 'Applies knowledge of content within and across curriculum teaching areas',
                        'rating' => 5,
                    ]],
                ],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertCount(1, $response->json('synced'));
        $this->assertSame($this->schoolHeadUser->id, Observation::where('client_id', $clientId)->first()->observer_id);
        $this->assertDatabaseHas('cot_ratings', [
            'client_id' => $ratingClientId,
            'rating' => 5,
        ]);
    }

    public function test_school_head_show_page_has_offline_entry_points(): void
    {
        $observation = $this->makeApprovableObservation();

        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.observations.show', $observation))
            ->assertOk()
            ->assertSee('Offline package')
            ->assertSee('Prepare for Offline')
            ->assertSee('Approve for offline use');
    }

    public function test_school_head_index_links_offline_capture(): void
    {
        $this->actingAs($this->schoolHeadUser)
            ->get(route('school-head.observations.index'))
            ->assertOk()
            ->assertSee(route('school-head.observations.offline'), false);
    }
}
