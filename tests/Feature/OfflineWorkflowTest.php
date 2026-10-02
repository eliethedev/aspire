<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\School;
use App\Models\SupervisorProfile;
use App\Models\Teacher;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Offline clinical-supervision workflow (IT adviser mandate):
 * schedule → teacher confirm + DLL → AI prompts → package download →
 * offline encode → idempotent push onto the SAME observation.
 */
class OfflineWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $supervisor;

    protected User $teacherUser;

    protected Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->supervisor = User::factory()->supervisor()->create(['school_id' => $this->school->id]);
        $this->teacherUser = User::factory()->teacher()->create(['school_id' => $this->school->id]);
        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create(['user_id' => $this->teacherUser->id]);

        // Complete profiles so the profile.complete middleware lets the
        // requests through to the workflow itself.
        UserProfile::create(['user_id' => $this->supervisor->id, 'mobile_number' => '09170000001']);
        SupervisorProfile::create([
            'user_id' => $this->supervisor->id,
            'division_district_assigned' => 'District IV',
            'area_of_specialization' => 'Science',
            'supervisory_level' => 'district',
        ]);
        UserProfile::create([
            'user_id' => $this->teacherUser->id,
            'mobile_number' => '09170000002',
            'employment_status' => 'permanent',
        ]);
        TeacherProfile::create([
            'user_id' => $this->teacherUser->id,
            'grade_level' => 'junior_high',
            'default_room' => 'Room 101',
        ]);
    }

    protected function makeObservation(array $overrides = []): Observation
    {
        return Observation::factory()->create(array_merge([
            'observer_id' => $this->supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $this->teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'pending_teacher_confirmation',
            'confirmation_status' => 'pending',
        ], $overrides));
    }

    public function test_teacher_confirm_with_dll_transitions_to_ready(): void
    {
        Storage::fake('public');
        $observation = $this->makeObservation();

        // Multipart post (UploadedFile cannot ride postJson) + JSON accept header.
        $response = $this->actingAs($this->teacherUser)->post(
            route('teacher.observations.confirm-package', $observation),
            [
                'accept' => 'on',
                'lesson_plan_file' => UploadedFile::fake()->create('dll.pdf', 100, 'application/pdf'),
            ],
            ['Accept' => 'application/json']
        );

        $response->assertOk()->assertJsonPath('status', 'confirmed_ready_for_download');

        $this->assertDatabaseHas('observations', [
            'id' => $observation->id,
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
        ]);
        $this->assertNotNull($observation->fresh()->teacher_confirmed_at);
        $this->assertNotNull($observation->fresh()->lesson_plan_path);
        // AI is disabled in tests -> deterministic fallback still stored.
        $this->assertTrue($observation->fresh()->hasPreObservationPrompts());
    }

    public function test_teacher_cannot_confirm_other_teachers_observation(): void
    {
        $other = User::factory()->teacher()->create(['school_id' => $this->school->id]);
        $otherTeacher = Teacher::factory()->forSchool($this->school->id)->create(['user_id' => $other->id]);
        $observation = $this->makeObservation(['observee_id' => $otherTeacher->id]);

        $this->actingAs($this->teacherUser)
            ->postJson(route('teacher.observations.confirm-package', $observation), ['accept' => 'on'])
            ->assertForbidden();
    }

    public function test_package_locked_before_teacher_confirmation(): void
    {
        $observation = $this->makeObservation();

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'teacher_confirmation');
    }

    public function test_package_download_marks_downloaded_and_returns_bundle(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
            'lesson_plan_summary' => 'Objectives: fractions.',
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
            'ai_suggestions_approved_at' => now(),
            'ai_suggestions_approved_by' => $this->supervisor->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation));

        $response->assertOk()
            ->assertJsonPath('server_id', $observation->id)
            ->assertJsonPath('ai_ready', true)
            ->assertJsonPath('ai_approved', true)
            ->assertJsonStructure([
                'observation' => ['id', 'subject', 'teacher'],
                'rubric' => ['scale', 'scale_max', 'indicators'],
                'lesson_plan', 'ai_prompts',
            ]);

        $this->assertSame('downloaded_offline', $observation->fresh()->status);
        $this->assertNotNull($observation->fresh()->offline_downloaded_at);
    }

    public function test_push_to_scheduled_observation_syncs_idempotently(): void
    {
        $observation = $this->makeObservation([
            'status' => 'downloaded_offline',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
            'ai_suggestions_approved_at' => now(),
            'ai_suggestions_approved_by' => $this->supervisor->id,
        ]);

        $clientId = (string) Str::uuid();
        $item = [
            'client_id' => $clientId,
            'device_updated_at' => now()->toIso8601String(),
            'server_id' => $observation->id,
            'payload' => [
                'notes' => 'Overall solid lesson',
                'star_notes' => 'S: fractions intro. T: explain. A: used bars. R: 80% mastery.',
                'ratings' => [
                    ['client_id' => (string) Str::uuid(), 'indicator_code' => 'IND-1', 'domain' => 'CKP', 'rating' => 5, 'comments' => 'Good'],
                    ['client_id' => (string) Str::uuid(), 'indicator_code' => 'IND-2', 'domain' => 'CKP', 'rating' => 4],
                ],
            ],
        ];

        $first = $this->actingAs($this->supervisor)->postJson('/sync/push', [
            'device_id' => 'tablet-07',
            'items' => [$item],
        ]);

        $first->assertStatus(207);
        $this->assertCount(1, $first->json('synced'));
        $this->assertSame($observation->id, $first->json('synced.0.server_id'));
        // (5+4)/2 = 4.5 -> Very Satisfactory on the 2-6 scale.
        $this->assertSame(4.5, (float) $first->json('synced.0.overall_score'));
        $this->assertSame('Very Satisfactory', $first->json('synced.0.descriptive'));

        $this->assertDatabaseHas('observations', [
            'id' => $observation->id,
            'status' => 'synced',
            'sync_status' => 'synced',
            'client_id' => $clientId,
        ]);
        $this->assertDatabaseHas('cot_ratings', [
            'observation_id' => $observation->id,
            'indicator_code' => 'IND-1',
            'rating' => 5,
        ]);

        // Retry with the same client_id: no duplicates, already_synced.
        $second = $this->actingAs($this->supervisor)->postJson('/sync/push', [
            'device_id' => 'tablet-07',
            'items' => [$item],
        ]);
        $second->assertStatus(207);
        $this->assertSame('already_synced', $second->json('synced.0.status'));
        $this->assertSame(2, $observation->cotRatings()->count());
    }

    public function test_push_conflicts_when_server_already_rated(): void
    {
        $observation = $this->makeObservation([
            'status' => 'downloaded_offline',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
            'ai_suggestions_approved_at' => now(),
            'ai_suggestions_approved_by' => $this->supervisor->id,
        ]);
        $observation->cotRatings()->create([
            'indicator_code' => 'IND-1', 'domain' => 'CKP',
            'indicator' => 'Online rating', 'rating' => 6,
        ]);

        $response = $this->actingAs($this->supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) Str::uuid(),
                'server_id' => $observation->id,
                'payload' => [
                    'ratings' => [['indicator_code' => 'IND-1', 'rating' => 3]],
                ],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertSame('server_already_rated', $response->json('conflicts.0.reason'));
        $this->assertSame(6, $observation->cotRatings()->first()->rating);
    }

    public function test_push_rejected_for_unowned_observation(): void
    {
        $intruder = User::factory()->supervisor()->create(['school_id' => $this->school->id]);
        $observation = $this->makeObservation([
            'status' => 'downloaded_offline',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($intruder)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) Str::uuid(),
                'server_id' => $observation->id,
                'payload' => ['ratings' => [['indicator_code' => 'IND-1', 'rating' => 5]]],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertSame('not_observer', $response->json('conflicts.0.reason'));
    }

    public function test_push_rejected_before_teacher_confirmation(): void
    {
        $observation = $this->makeObservation(); // still pending

        $response = $this->actingAs($this->supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) Str::uuid(),
                'server_id' => $observation->id,
                'payload' => ['ratings' => [['indicator_code' => 'IND-1', 'rating' => 5]]],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertSame('not_confirmed', $response->json('conflicts.0.reason'));
    }

    public function test_prepare_requires_lesson_plan_review_checkbox(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->create('dll.pdf', 100, 'application/pdf')
            ->storeAs('lesson_plans', 'dll.pdf', 'public');
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_path' => $path,
        ]);

        // No checkbox -> 422 with a friendly message.
        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.prepare-package', $observation), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('lesson_plan_reviewed');

        $this->assertNull($observation->fresh()->lesson_plan_reviewed_at);
    }

    public function test_prepare_with_review_stores_it_and_generates_prompts(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->create('dll.pdf', 100, 'application/pdf')
            ->storeAs('lesson_plans', 'dll.pdf', 'public');
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_path' => $path,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.prepare-package', $observation), [
                'lesson_plan_reviewed' => '1',
            ]);

        $response->assertOk()->assertJsonPath('ai_ready', true);

        $fresh = $observation->fresh();
        $this->assertNotNull($fresh->lesson_plan_reviewed_at);
        $this->assertSame($this->supervisor->id, (int) $fresh->lesson_plan_reviewed_by);
        $this->assertTrue($fresh->hasPreObservationPrompts());
    }

    public function test_prepare_refresh_skips_checkbox_once_reviewed(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->create('dll.pdf', 100, 'application/pdf')
            ->storeAs('lesson_plans', 'dll.pdf', 'public');
        $observation = $this->makeObservation([
            'status' => 'downloaded_offline',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_path' => $path,
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
        ]);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.prepare-package', $observation), [])
            ->assertOk()
            ->assertJsonPath('ai_ready', true);
    }

    public function test_download_locked_without_lesson_plan_review(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            // reviewed fields intentionally absent
        ]);

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'lesson_plan_review');
    }

    public function test_insights_status_returns_organized_panel_html(): void
    {
        $observation = $this->makeObservation();
        $observation->preObservationPlanning()->updateOrCreate(
            ['observation_id' => $observation->id],
            ['ai_insights' => "## Lesson Focus\nFractions.\n## Key Things to Watch\n- Pacing\n## Pre-Conference Talking Points\n- Goals\n## Potential Challenges\n- Time\n"]
        );

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.ai-insights-status', $observation));

        $response->assertOk()->assertJsonPath('status', 'completed');
        $html = $response->json('panel_html');
        $this->assertNotEmpty($html);
        foreach (['Lesson Focus', 'Key Things to Watch', 'Pre-Conference Talking Points', 'Potential Challenges'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('ai-insights-text', $html);
    }

    protected function makeApprovedObservation(array $overrides = []): Observation
    {
        return $this->makeObservation(array_merge([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
            'ai_suggestions_approved_at' => now(),
            'ai_suggestions_approved_by' => $this->supervisor->id,
        ], $overrides));
    }

    public function test_download_locked_until_ai_suggestions_generated(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
            // prompts intentionally absent
        ]);

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'ai_suggestions');
    }

    public function test_download_locked_until_ai_suggestions_approved(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
            // approval intentionally absent
        ]);

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'ai_suggestions_approval');

        $this->assertSame('confirmed_ready_for_download', $observation->fresh()->status);
    }

    public function test_approve_requires_generated_suggestions(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
        ]);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.approve-suggestions', $observation), [
                'suggestions_reviewed' => '1',
            ])
            ->assertStatus(409)
            ->assertJsonPath('required', 'ai_suggestions');
    }

    public function test_approve_requires_review_checkbox(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
        ]);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.approve-suggestions', $observation), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('suggestions_reviewed');

        $this->assertNull($observation->fresh()->ai_suggestions_approved_at);
    }

    public function test_approve_records_approval_and_unlocks_download(): void
    {
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'lesson_plan_reviewed_at' => now(),
            'lesson_plan_reviewed_by' => $this->supervisor->id,
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
        ]);

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.approve-suggestions', $observation), [
                'suggestions_reviewed' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $fresh = $observation->fresh();
        $this->assertNotNull($fresh->ai_suggestions_approved_at);
        $this->assertSame($this->supervisor->id, (int) $fresh->ai_suggestions_approved_by);
        $this->assertTrue($fresh->hasApprovedAiSuggestions());

        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation))
            ->assertOk()
            ->assertJsonPath('ai_approved', true);

        $this->assertSame('downloaded_offline', $observation->fresh()->status);
    }

    public function test_approve_forbidden_for_non_observer(): void
    {
        $intruder = User::factory()->supervisor()->create(['school_id' => $this->school->id]);
        UserProfile::create(['user_id' => $intruder->id, 'mobile_number' => '09170000009']);
        SupervisorProfile::create([
            'user_id' => $intruder->id,
            'division_district_assigned' => 'District IV',
            'area_of_specialization' => 'Science',
            'supervisory_level' => 'district',
        ]);
        $observation = $this->makeObservation([
            'status' => 'confirmed_ready_for_download',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
        ]);

        $this->actingAs($intruder)
            ->postJson(route('supervisor.observations.approve-suggestions', $observation), [
                'suggestions_reviewed' => '1',
            ])
            ->assertForbidden();
    }

    public function test_regenerating_prompts_clears_approval(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->create('dll.pdf', 100, 'application/pdf')
            ->storeAs('lesson_plans', 'dll.pdf', 'public');
        $observation = $this->makeApprovedObservation([
            'lesson_plan_path' => $path,
            'status' => 'downloaded_offline',
        ]);

        $this->assertTrue($observation->fresh()->hasApprovedAiSuggestions());

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.prepare-package', $observation), [])
            ->assertOk();

        $fresh = $observation->fresh();
        $this->assertTrue($fresh->hasPreObservationPrompts());
        $this->assertFalse($fresh->hasApprovedAiSuggestions());

        // The stale bundle no longer unlocks a download.
        $this->actingAs($this->supervisor)
            ->getJson(route('supervisor.observations.offline-package', $observation))
            ->assertStatus(409)
            ->assertJsonPath('required', 'ai_suggestions_approval');
    }

    public function test_push_rejected_when_package_never_downloaded(): void
    {
        // Confirmed + reviewed + approved, but the bundle was never downloaded.
        $observation = $this->makeApprovedObservation();

        $response = $this->actingAs($this->supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) Str::uuid(),
                'server_id' => $observation->id,
                'payload' => ['ratings' => [['indicator_code' => 'IND-1', 'rating' => 5]]],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertSame('package_not_downloaded', $response->json('conflicts.0.reason'));
    }

    public function test_push_rejected_when_suggestions_not_approved(): void
    {
        // Downloaded (status) with prompts, but no approval on record.
        $observation = $this->makeObservation([
            'status' => 'downloaded_offline',
            'confirmation_status' => 'confirmed',
            'teacher_confirmed_at' => now(),
            'pre_observation_ai_prompts' => ['strategies' => ['Use manipulatives'], 'fallback' => true],
        ]);

        $response = $this->actingAs($this->supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) Str::uuid(),
                'server_id' => $observation->id,
                'payload' => ['ratings' => [['indicator_code' => 'IND-1', 'rating' => 5]]],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertSame('suggestions_not_approved', $response->json('conflicts.0.reason'));
    }
}
