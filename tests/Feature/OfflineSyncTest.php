<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_push_creates_observation_and_is_idempotent(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $clientId = (string) \Illuminate\Support\Str::uuid();
        $ratingClientId = (string) \Illuminate\Support\Str::uuid();

        $payload = [
            'device_id' => 'test-tablet',
            'items' => [[
                'client_id' => $clientId,
                'device_updated_at' => now()->toIso8601String(),
                'payload' => [
                    'observation_type' => 'teacher_observation',
                    'observee_id' => $teacher->id,
                    'observation_date' => now()->toDateString(),
                    'subject' => 'Mathematics',
                    'grade_level' => 'Grade 7',
                    'notes' => 'Offline evidence notes',
                    'ratings' => [[
                        'client_id' => $ratingClientId,
                        'indicator_code' => 'IND-1',
                        'domain' => 'Content Knowledge and Pedagogy',
                        'indicator' => 'Applies knowledge of content within and across curriculum teaching areas',
                        'rating' => 5,
                        'comments' => 'Good questioning',
                    ]],
                ],
            ]],
        ];

        $first = $this->actingAs($supervisor)->postJson('/sync/push', $payload);
        $first->assertStatus(207);
        $this->assertCount(1, $first->json('synced'));
        $this->assertCount(0, $first->json('conflicts'));
        $this->assertCount(0, $first->json('errors'));

        // In the test env the queue runs inline and AI is disabled, so the
        // dispatched feedback job fails and flips the flag pending → failed.
        $this->assertDatabaseHas('observations', [
            'client_id' => $clientId,
            'sync_source' => 'offline',
            'sync_status' => 'synced',
            'ai_status' => 'failed',
        ]);
        $this->assertDatabaseHas('cot_ratings', [
            'client_id' => $ratingClientId,
            'indicator_code' => 'IND-1',
            'rating' => 5,
        ]);

        // Retry with the same client_id must not duplicate.
        $second = $this->actingAs($supervisor)->postJson('/sync/push', $payload);
        $second->assertStatus(207);
        $this->assertSame('already_synced', $second->json('synced.0.status'));
        $this->assertSame(1, \App\Models\Observation::where('client_id', $clientId)->count());
    }

    public function test_push_rejects_teacher_from_other_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $schoolA->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $schoolB->id]);
        $teacher = Teacher::factory()->forSchool($schoolB->id)->create(['user_id' => $teacherUser->id]);

        $response = $this->actingAs($supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) \Illuminate\Support\Str::uuid(),
                'payload' => [
                    'observation_type' => 'teacher_observation',
                    'observee_id' => $teacher->id,
                    'observation_date' => now()->toDateString(),
                ],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertCount(0, $response->json('synced'));
        $this->assertSame('teacher_not_in_school', $response->json('conflicts.0.reason'));
    }

    public function test_bootstrap_returns_teachers_and_templates(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);

        $response = $this->actingAs($supervisor)->getJson('/sync/bootstrap');
        $response->assertOk();
        $response->assertJsonStructure(['school_year', 'server_time', 'teachers', 'school_heads', 'cot_templates', 'scheduled', 'history']);
    }

    public function test_push_creates_school_head_observation(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $headUser = User::factory()->schoolHead()->create(['school_id' => $school->id]);
        $head = \App\Models\SchoolHeadProfile::create([
            'user_id' => $headUser->id,
            'school_id' => $school->id,
            'position_level' => 'principal_i',
        ]);

        $clientId = (string) \Illuminate\Support\Str::uuid();

        $response = $this->actingAs($supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => $clientId,
                'payload' => [
                    'observation_type' => 'school_head_observation',
                    'observee_id' => $head->id,
                    'observation_date' => now()->toDateString(),
                    'subject' => 'Leadership walkthrough',
                    'notes' => 'Offline notes for school head',
                    // Ratings must be ignored for EPOC observations.
                    'ratings' => [[
                        'client_id' => (string) \Illuminate\Support\Str::uuid(),
                        'indicator_code' => 'IND-1',
                        'domain' => 'General',
                        'indicator' => 'IND-1',
                        'rating' => 5,
                    ]],
                ],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertCount(1, $response->json('synced'));
        $this->assertCount(0, $response->json('errors'));

        $this->assertDatabaseHas('observations', [
            'client_id' => $clientId,
            'observation_type' => 'school_head_observation',
            'observee_id' => $head->id,
            'observee_type' => \App\Models\SchoolHeadProfile::class,
            'sync_source' => 'offline',
            'ai_status' => 'none',
        ]);
        // No COT ratings for school-head (EPOC) observations.
        $observationId = \App\Models\Observation::where('client_id', $clientId)->value('id');
        $this->assertSame(0, \App\Models\CotRating::where('observation_id', $observationId)->count());
    }

    public function test_push_rejects_duplicate_of_existing_observation(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $item = fn (string $clientId) => [
            'client_id' => $clientId,
            'payload' => [
                'observation_type' => 'teacher_observation',
                'observee_id' => $teacher->id,
                'observation_date' => now()->toDateString(),
                'subject' => 'Mathematics',
            ],
        ];

        $first = $this->actingAs($supervisor)->postJson('/sync/push', ['items' => [$item((string) \Illuminate\Support\Str::uuid())]]);
        $first->assertStatus(207);
        $this->assertCount(1, $first->json('synced'));

        // Same observer + ratee + date + subject under a NEW client_id.
        $second = $this->actingAs($supervisor)->postJson('/sync/push', ['items' => [$item((string) \Illuminate\Support\Str::uuid())]]);
        $second->assertStatus(207);
        $this->assertSame('possible_duplicate', $second->json('conflicts.0.reason'));
        $this->assertSame(1, \App\Models\Observation::where('observer_id', $supervisor->id)->count());
    }

    public function test_bootstrap_lists_scheduled_observations(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $this->actingAs($supervisor)->postJson('/sync/push', ['items' => [[
            'client_id' => (string) \Illuminate\Support\Str::uuid(),
            'payload' => [
                'observation_type' => 'teacher_observation',
                'observee_id' => $teacher->id,
                'observation_date' => now()->toDateString(),
                'subject' => 'Science',
            ],
        ]]])->assertStatus(207);

        $bootstrap = $this->actingAs($supervisor)->getJson('/sync/bootstrap');
        $bootstrap->assertOk();
        $scheduled = $bootstrap->json('scheduled');
        $this->assertNotEmpty($scheduled);
        $this->assertSame($teacherUser->name, $scheduled[0]['observee_name']);
        $this->assertSame('Science', $scheduled[0]['subject']);
    }

    public function test_completed_ai_marks_observation_done(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $observation = \App\Models\Observation::create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->toDateString(),
            'school_year' => '2026-2027',
            'stage' => 'observation',
            'status' => 'in_progress',
            'sync_source' => 'offline',
            'sync_status' => 'synced',
            'ai_status' => 'pending',
            'server_version' => 1,
        ]);
        $rating = \App\Models\CotRating::create([
            'observation_id' => $observation->id,
            'indicator_code' => 'IND-1',
            'domain' => 'Content Knowledge and Pedagogy',
            'indicator' => 'Applies knowledge of content',
            'rating' => 5,
        ]);
        \App\Models\AiFeedback::create([
            'cot_rating_id' => $rating->id,
            'analysis' => 'Pre-existing AI analysis.',
        ]);

        // The job re-runs (AI disabled → null) but the recompute sees every
        // actionable rating already has feedback → done.
        \App\Jobs\GeneratePostObservationFeedback::dispatch($rating);

        $this->assertDatabaseHas('observations', [
            'id' => $observation->id,
            'ai_status' => 'done',
        ]);
    }

    public function test_index_and_detail_show_offline_badges(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $clientId = (string) \Illuminate\Support\Str::uuid();
        $this->actingAs($supervisor)->postJson('/sync/push', ['items' => [[
            'client_id' => $clientId,
            'payload' => [
                'observation_type' => 'teacher_observation',
                'observee_id' => $teacher->id,
                'observation_date' => now()->toDateString(),
                'subject' => 'Mathematics',
                'ratings' => [[
                    'client_id' => (string) \Illuminate\Support\Str::uuid(),
                    'indicator_code' => 'IND-1',
                    'domain' => 'Content Knowledge and Pedagogy',
                    'indicator' => 'Applies knowledge of content',
                    'rating' => 5,
                ]],
            ],
        ]]])->assertStatus(207);

        $observationId = \App\Models\Observation::where('client_id', $clientId)->value('id');

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        $this->actingAs($supervisor)
            ->get(route('supervisor.observations.index'))
            ->assertOk()
            ->assertSee('Captured offline')
            ->assertSee('AI failed'); // AI disabled in test env → inline job fails.

        $this->actingAs($supervisor)
            ->get(route('supervisor.observations.show', $observationId))
            ->assertOk()
            ->assertSee('Captured offline');
    }

    public function test_offline_page_renders_with_server_bundle(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        $response = $this->actingAs($supervisor)->get(route('supervisor.observations.offline'));
        $response->assertOk();
        // Server-rendered bundle: names present on first paint, no fetch needed.
        $response->assertSee($teacherUser->name);
        $response->assertSee('window.ASPIRE_BOOTSTRAP', false);
        // Offline engine inlined: no separate download that can 404 or go stale.
        $response->assertSee('window.AspireOffline = api', false);
        $response->assertDontSee('js/aspire-offline.js', false);
    }

    public function test_retry_ai_endpoint_regenerates_with_mock(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $clientId = (string) \Illuminate\Support\Str::uuid();
        $this->actingAs($supervisor)->postJson('/sync/push', ['items' => [[
            'client_id' => $clientId,
            'payload' => [
                'observation_type' => 'teacher_observation',
                'observee_id' => $teacher->id,
                'observation_date' => now()->toDateString(),
                'subject' => 'Mathematics',
                'ratings' => [[
                    'client_id' => (string) \Illuminate\Support\Str::uuid(),
                    'indicator_code' => 'IND-1',
                    'domain' => 'Content Knowledge and Pedagogy',
                    'indicator' => 'Applies knowledge of content',
                    'rating' => 5,
                ]],
            ],
        ]]])->assertStatus(207);

        $observationId = \App\Models\Observation::where('client_id', $clientId)->value('id');
        $ratingId = \App\Models\CotRating::where('observation_id', $observationId)->value('id');

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        // Retry button is visible while failed.
        $this->actingAs($supervisor)
            ->get(route('supervisor.observations.show', $observationId))
            ->assertOk()
            ->assertSee('Retry AI');

        // AI succeeds this time (mocked provider).
        $this->mock(\App\AI\Services\AIFeedbackService::class, function ($mock) {
            $mock->shouldReceive('generateFeedback')->andReturnUsing(function (int $cotRatingId) {
                return \App\Models\AiFeedback::create([
                    'cot_rating_id' => $cotRatingId,
                    'analysis' => 'Mocked AI analysis.',
                ]);
            });
        });

        $this->actingAs($supervisor)
            ->post(route('supervisor.observations.retry-ai', $observationId))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('observations', ['id' => $observationId, 'ai_status' => 'done']);
        $this->assertDatabaseHas('ai_feedback', ['cot_rating_id' => $ratingId]);

        // Button gone, AI ready badge shown.
        $this->actingAs($supervisor)
            ->get(route('supervisor.observations.show', $observationId))
            ->assertOk()
            ->assertSee('AI ready')
            ->assertDontSee('Retry AI');
    }

    public function test_retry_ai_rejected_when_not_failed(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $observation = \App\Models\Observation::create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->toDateString(),
            'school_year' => '2026-2027',
            'stage' => 'observation',
            'status' => 'in_progress',
            'sync_source' => 'online',
            'sync_status' => 'synced',
            'ai_status' => 'none',
            'server_version' => 1,
        ]);

        \Illuminate\Support\Facades\Queue::fake();
        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        $this->actingAs($supervisor)
            ->post(route('supervisor.observations.retry-ai', $observation->id))
            ->assertRedirect()
            ->assertSessionHas('info');

        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        $this->assertDatabaseHas('observations', ['id' => $observation->id, 'ai_status' => 'none']);
    }

    public function test_retry_ai_forbidden_for_other_supervisor(): void
    {
        $school = School::factory()->create();
        $owner = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $other = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $observation = \App\Models\Observation::create([
            'observer_id' => $owner->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->toDateString(),
            'school_year' => '2026-2027',
            'stage' => 'observation',
            'status' => 'in_progress',
            'sync_source' => 'offline',
            'sync_status' => 'synced',
            'ai_status' => 'failed',
            'server_version' => 1,
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        $this->actingAs($other)
            ->post(route('supervisor.observations.retry-ai', $observation->id))
            ->assertForbidden();
    }

    public function test_artisan_retry_failed_command(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $failed = \App\Models\Observation::create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->toDateString(),
            'school_year' => '2026-2027',
            'stage' => 'observation',
            'status' => 'in_progress',
            'sync_source' => 'offline',
            'sync_status' => 'synced',
            'ai_status' => 'failed',
            'server_version' => 1,
        ]);
        $rating = \App\Models\CotRating::create([
            'observation_id' => $failed->id,
            'indicator_code' => 'IND-1',
            'domain' => 'Content Knowledge and Pedagogy',
            'indicator' => 'Applies knowledge of content',
            'rating' => 5,
        ]);
        $untouched = \App\Models\Observation::create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->toDateString(),
            'school_year' => '2026-2027',
            'stage' => 'observation',
            'status' => 'in_progress',
            'sync_source' => 'online',
            'sync_status' => 'synced',
            'ai_status' => 'none',
            'server_version' => 1,
        ]);

        $this->mock(\App\AI\Services\AIFeedbackService::class, function ($mock) {
            $mock->shouldReceive('generateFeedback')->andReturnUsing(function (int $cotRatingId) {
                return \App\Models\AiFeedback::create([
                    'cot_rating_id' => $cotRatingId,
                    'analysis' => 'Mocked AI analysis.',
                ]);
            });
        });

        $this->artisan('ai:retry-failed')->assertSuccessful();

        $this->assertDatabaseHas('observations', ['id' => $failed->id, 'ai_status' => 'done']);
        $this->assertDatabaseHas('ai_feedback', ['cot_rating_id' => $rating->id]);
        $this->assertDatabaseHas('observations', ['id' => $untouched->id, 'ai_status' => 'none']);
    }

    public function test_bootstrap_history_holds_finished_work_only(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $teacherUser = User::factory()->teacher()->create(['school_id' => $school->id]);
        $teacher = Teacher::factory()->forSchool($school->id)->create(['user_id' => $teacherUser->id]);

        $make = fn (string $status) => \App\Models\Observation::create([
            'observer_id' => $supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'observation_date' => now()->toDateString(),
            'school_year' => '2026-2027',
            'subject' => 'Mathematics',
            'notes' => 'Finished classroom evidence notes.',
            'stage' => $status === 'completed' ? 'post_conference' : 'observation',
            'status' => $status,
            'sync_source' => 'online',
            'sync_status' => 'synced',
            'ai_status' => 'none',
            'server_version' => 1,
        ]);

        $done = $make('completed');
        \App\Models\CotRating::create([
            'observation_id' => $done->id,
            'indicator_code' => 'IND-1',
            'domain' => 'Content Knowledge and Pedagogy',
            'indicator' => 'Applies knowledge of content',
            'rating' => 5,
            'comments' => 'Strong questioning.',
        ]);
        $ongoing = $make('in_progress');

        $history = $this->actingAs($supervisor)->getJson('/sync/bootstrap')->json('history');
        $ids = array_column($history, 'server_id');

        $this->assertContains($done->id, $ids);
        $this->assertNotContains($ongoing->id, $ids); // in-progress lives under `scheduled`.

        $entry = collect($history)->firstWhere('server_id', $done->id);
        $this->assertSame($teacherUser->name, $entry['observee_name']);
        $this->assertSame('Mathematics', $entry['subject']);
        $this->assertSame('Finished classroom evidence notes.', $entry['notes']);
        $this->assertCount(1, $entry['ratings']);
        $this->assertSame('IND-1', $entry['ratings'][0]['indicator_code']);
        $this->assertSame(5, $entry['ratings'][0]['rating']);
        $this->assertSame('Strong questioning.', $entry['ratings'][0]['comments']);
    }

    public function test_school_head_shows_user_school_when_profile_school_missing(): void
    {
        $school = School::factory()->create(['name' => 'Sagay National High School']);
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);
        $headUser = User::factory()->schoolHead()->create(['school_id' => $school->id, 'name' => 'Juan Dela Cruz']);
        // Profile created without its own school (e.g. user assigned later).
        \App\Models\SchoolHeadProfile::create(['user_id' => $headUser->id, 'school_id' => null]);

        $this->withoutMiddleware(\App\Http\Middleware\EnsureProfileComplete::class);

        // Wizard co-observer dropdown + preview cards.
        $this->actingAs($supervisor)->get(route('supervisor.observations.create'))
            ->assertOk()
            ->assertSee('Juan Dela Cruz')
            ->assertSee('Sagay National High School')
            ->assertDontSee('No school assigned');

        // Offline bundle carries the same resolved name.
        $bundle = $this->actingAs($supervisor)->getJson('/sync/bootstrap')->json();
        $head = collect($bundle['school_heads'])->firstWhere('name', 'Juan Dela Cruz');
        $this->assertNotNull($head);
        $this->assertSame('Sagay National High School', $head['school_name']);
    }

    public function test_push_rejects_unknown_observee(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);

        $response = $this->actingAs($supervisor)->postJson('/sync/push', [
            'items' => [[
                'client_id' => (string) \Illuminate\Support\Str::uuid(),
                'payload' => [
                    'observation_type' => 'teacher_observation',
                    'observee_id' => 999999,
                    'observation_date' => now()->toDateString(),
                ],
            ]],
        ]);

        $response->assertStatus(207);
        $this->assertSame('observee_not_found', $response->json('conflicts.0.reason'));
    }
}
