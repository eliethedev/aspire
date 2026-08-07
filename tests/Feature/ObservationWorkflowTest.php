<?php

namespace Tests\Feature;

use App\Models\CotRating;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObservationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::factory()->create([
            'user_id' => $this->teacherUser->id,
        ]);
    }

    public function test_supervisor_can_schedule_an_observation(): void
    {
        $response = $this->actingAs($this->supervisor)->post(route('supervisor.observations.store'), [
            'observation_type' => 'teacher_observation',
            'observee_id' => $this->teacher->id,
            'observation_date' => now()->addDay()->format('Y-m-d'),
            'schedule_type' => 'scheduled',
            'subject' => 'Mathematics',
            'grade_level' => '7',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('observations', [
            'observer_id' => $this->supervisor->id,
            'observer_type' => User::class,
            'observee_id' => $this->teacher->id,
            'observee_type' => Teacher::class,
            'observation_type' => 'teacher_observation',
            'status' => 'scheduled',
            'stage' => 'pre_observation_planning',
            'confirmation_status' => 'pending',
            'subject' => 'Mathematics',
        ]);
    }

    public function test_supervisor_can_start_an_immediate_observation(): void
    {
        $response = $this->actingAs($this->supervisor)->post(route('supervisor.observations.store'), [
            'observation_type' => 'teacher_observation',
            'observee_id' => $this->teacher->id,
            'observation_date' => now()->format('Y-m-d'),
            'schedule_type' => 'immediate',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('observations', [
            'observee_id' => $this->teacher->id,
            'status' => 'in_progress',
            'stage' => 'observation',
        ]);
    }

    public function test_teacher_can_confirm_a_scheduled_observation(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create();

        $this->actingAs($this->teacherUser)
            ->post(route('teacher.observations.confirm', $observation))
            ->assertRedirect();

        $this->assertSame('confirmed', $observation->fresh()->confirmation_status);
        $this->assertNotNull($observation->fresh()->confirmed_at);
    }

    public function test_teacher_cannot_confirm_another_teachers_observation(): void
    {
        $otherTeacher = Teacher::factory()->create();
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($otherTeacher)
            ->create();

        $this->actingAs($this->teacherUser)
            ->post(route('teacher.observations.confirm', $observation))
            ->assertForbidden();

        $this->assertSame('pending', $observation->fresh()->confirmation_status);
    }

    public function test_supervisor_can_store_cot_ratings_and_complete_observation(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create([
                'status' => 'in_progress',
                'stage' => 'observation',
            ]);

        $response = $this->actingAs($this->supervisor)->post(
            route('supervisor.observations.storeObservationData', $observation),
            $this->ratingPayload()
        );

        $response->assertRedirect(route('supervisor.observations.postConference', $observation));

        $this->assertDatabaseCount('cot_ratings', 3);

        $observation->refresh();

        $this->assertSame('cot_completed', $observation->status);
        $this->assertSame('post_conference', $observation->stage);
        $this->assertEquals(4.50, (float) $observation->overall_score);
    }

    public function test_store_cot_ratings_replaces_existing_ratings(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create([
                'status' => 'in_progress',
                'stage' => 'observation',
            ]);

        CotRating::factory()->count(2)->create(['observation_id' => $observation->id]);

        $this->actingAs($this->supervisor)->post(
            route('supervisor.observations.storeObservationData', $observation),
            $this->ratingPayload()
        );

        $this->assertDatabaseCount('cot_ratings', 3);
        $this->assertSame('cot_completed', $observation->fresh()->status);
    }

    public function test_supervisor_cannot_access_another_supervisors_observation(): void
    {
        $otherSupervisor = User::factory()->create(['role' => 'supervisor']);
        $observation = Observation::factory()
            ->forObserver($otherSupervisor)
            ->forObservee($this->teacher)
            ->create();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.storeObservationData', $observation), $this->ratingPayload())
            ->assertForbidden();
    }

    public function test_supervisor_can_cancel_an_observation(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.cancel', $observation), [
                'cancellation_reason' => 'conflict_in_schedule',
            ])
            ->assertRedirect(route('supervisor.observations.index'));

        $observation->refresh();

        $this->assertSame('cancelled', $observation->status);
        $this->assertSame('conflict_in_schedule', $observation->cancellation_reason);
        $this->assertSame($this->supervisor->id, $observation->cancelled_by);
        $this->assertNotNull($observation->cancelled_at);
    }

    public function test_cannot_cancel_an_already_cancelled_observation(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->cancelled()
            ->create();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.cancel', $observation), [
                'cancellation_reason' => 'conflict_in_schedule',
            ])
            ->assertRedirect(route('supervisor.observations.show', $observation));

        $this->assertSame('cancelled', $observation->fresh()->status);
    }

    private function ratingPayload(): array
    {
        return [
            'ratings' => [
                [
                    'indicator_code' => '1.1.1',
                    'domain' => 'Content Knowledge and Pedagogy',
                    'indicator' => 'Applies knowledge of content within and across curriculum areas',
                    'rating' => 6,
                    'not_observed' => false,
                    'has_rating' => 'true',
                    'comments' => 'Excellent delivery.',
                ],
                [
                    'indicator_code' => '1.1.2',
                    'domain' => 'Content Knowledge and Pedagogy',
                    'indicator' => 'Uses strategies to develop critical and creative thinking',
                    'rating' => 3,
                    'not_observed' => false,
                    'has_rating' => 'true',
                ],
                [
                    'indicator_code' => '1.1.3',
                    'domain' => 'Content Knowledge and Pedagogy',
                    'indicator' => 'Integrates ICT in the teaching-learning process',
                    'rating' => null,
                    'not_observed' => true,
                    'has_rating' => 'false',
                ],
            ],
            'other_comments' => 'Great overall lesson.',
            'star_notes' => 'Good pacing and rapport.',
            'supervisor_notes' => 'Focus on higher-order questioning.',
        ];
    }
}
