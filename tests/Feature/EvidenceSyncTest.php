<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceSyncTest extends TestCase
{
    use RefreshDatabase;

    protected string $clientId;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * @return array{supervisor: User, clientId: string}
     */
    protected function syncedObservation(): array
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
            ],
        ]]])->assertStatus(207);

        return ['supervisor' => $supervisor, 'clientId' => $clientId];
    }

    public function test_push_files_stores_evidence(): void
    {
        ['supervisor' => $supervisor, 'clientId' => $clientId] = $this->syncedObservation();
        $fileId = (string) \Illuminate\Support\Str::uuid();

        $response = $this->actingAs($supervisor)->post('/sync/push-files', [
            'observation_client_id' => $clientId,
            'file_id' => $fileId,
            'file' => UploadedFile::fake()->image('board-work.jpg', 800, 600)->size(500),
        ]);

        $response->assertCreated();
        $response->assertJson(['status' => 'uploaded']);

        $observation = \App\Models\Observation::where('client_id', $clientId)->firstOrFail();
        $files = $observation->evidence_files;
        $this->assertCount(1, $files);
        $this->assertSame('board-work.jpg', $files[0]['original_name']);
        $this->assertSame($fileId, $files[0]['client_file_id']);
        $this->assertArrayHasKey('path', $files[0]);
        $this->assertArrayHasKey('size', $files[0]);
        Storage::disk('public')->assertExists($files[0]['path']);
    }

    public function test_push_files_is_idempotent(): void
    {
        ['supervisor' => $supervisor, 'clientId' => $clientId] = $this->syncedObservation();
        $fileId = (string) \Illuminate\Support\Str::uuid();
        $payload = [
            'observation_client_id' => $clientId,
            'file_id' => $fileId,
            'file' => UploadedFile::fake()->image('board-work.jpg', 800, 600)->size(500),
        ];

        $this->actingAs($supervisor)->post('/sync/push-files', $payload)->assertCreated();
        // Tablet retries the same file (new upload bytes, same file_id).
        $this->actingAs($supervisor)->post('/sync/push-files', $payload)
            ->assertOk()
            ->assertJson(['status' => 'already_uploaded']);

        $observation = \App\Models\Observation::where('client_id', $clientId)->firstOrFail();
        $this->assertCount(1, $observation->evidence_files);
    }

    public function test_push_files_rejects_unknown_observation(): void
    {
        $school = School::factory()->create();
        $supervisor = User::factory()->supervisor()->create(['school_id' => $school->id]);

        $this->actingAs($supervisor)->post('/sync/push-files', [
            'observation_client_id' => (string) \Illuminate\Support\Str::uuid(),
            'file_id' => (string) \Illuminate\Support\Str::uuid(),
            'file' => UploadedFile::fake()->image('board-work.jpg')->size(100),
        ])->assertNotFound();
    }

    public function test_push_files_forbidden_for_other_supervisor(): void
    {
        ['clientId' => $clientId] = $this->syncedObservation();
        $school = School::first();
        $other = User::factory()->supervisor()->create(['school_id' => $school->id]);

        $this->actingAs($other)->post('/sync/push-files', [
            'observation_client_id' => $clientId,
            'file_id' => (string) \Illuminate\Support\Str::uuid(),
            'file' => UploadedFile::fake()->image('board-work.jpg')->size(100),
        ])->assertForbidden();
    }

    public function test_push_files_rejects_disallowed_type(): void
    {
        ['supervisor' => $supervisor, 'clientId' => $clientId] = $this->syncedObservation();

        // The tablet client sends Accept: application/json (like here), so
        // validation failures come back as JSON 422 instead of a redirect.
        $this->withHeaders(['Accept' => 'application/json']);
        $this->actingAs($supervisor)->post('/sync/push-files', [
            'observation_client_id' => $clientId,
            'file_id' => (string) \Illuminate\Support\Str::uuid(),
            'file' => UploadedFile::fake()->create('notes.txt', 100, 'text/plain'),
        ])->assertStatus(422);

        $observation = \App\Models\Observation::where('client_id', $clientId)->firstOrFail();
        $this->assertEmpty($observation->evidence_files ?? []);
    }
}
