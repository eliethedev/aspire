<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleConfirmationGateTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private Teacher $teacher;

    private User $shUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);

        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $school = School::factory()->create();
        $this->shUser = User::factory()->create(['role' => 'school_head']);
        SchoolHeadProfile::create([
            'user_id' => $this->shUser->id,
            'school_id' => $school->id,
        ]);
    }

    private function planningObservation(array $overrides = []): Observation
    {
        return Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create(array_merge([
                'observation_type' => 'teacher_observation',
                'stage' => 'pre_observation_planning',
                'status' => 'scheduled',
                'confirmation_status' => 'pending',
            ], $overrides));
    }

    public function test_supervisor_cannot_advance_while_teacher_has_not_confirmed(): void
    {
        $observation = $this->planningObservation();

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.storePreObservationPlanning', $observation->id), [
                'supervisor_notes' => 'Draft notes.',
                'continue' => 'observation',
            ]);

        $response->assertRedirect(route('supervisor.observations.preObservationPlanning', $observation->id));
        $response->assertSessionHas('error');

        $observation->refresh();
        $this->assertSame('pre_observation_planning', $observation->stage);

        // Planning notes are still saved; only the advance is held.
        $this->assertSame('Draft notes.', $observation->preObservationPlanning?->supervisor_notes);
    }

    public function test_supervisor_cannot_advance_while_school_head_has_not_confirmed(): void
    {
        $observation = $this->planningObservation([
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
            'school_head_id' => $this->shUser->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.storePreObservationPlanning', $observation->id), [
                'continue' => 'observation',
            ]);

        $response->assertRedirect(route('supervisor.observations.preObservationPlanning', $observation->id));

        $observation->refresh();
        $this->assertSame('pre_observation_planning', $observation->stage);
    }

    public function test_supervisor_advances_once_both_confirmations_are_in(): void
    {
        $observation = $this->planningObservation([
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
            'school_head_id' => $this->shUser->id,
            'school_head_confirmation_status' => 'confirmed',
            'school_head_confirmed_at' => now(),
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.storePreObservationPlanning', $observation->id), [
                'continue' => 'observation',
            ])
            ->assertRedirect(route('supervisor.observations.observation', $observation->id));

        $observation->refresh();
        $this->assertSame('observation', $observation->stage);
    }

    public function test_supervisor_advances_without_school_head_once_teacher_confirms(): void
    {
        $observation = $this->planningObservation([
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.storePreObservationPlanning', $observation->id), [
                'continue' => 'observation',
            ])
            ->assertRedirect(route('supervisor.observations.observation', $observation->id));

        $observation->refresh();
        $this->assertSame('observation', $observation->stage);
    }

    public function test_rejected_teacher_confirmation_also_blocks_advance(): void
    {
        $observation = $this->planningObservation([
            'confirmation_status' => 'rejected',
            'rejection_reason' => 'scheduling_conflict',
            'rejected_at' => now(),
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.storePreObservationPlanning', $observation->id), [
                'continue' => 'observation',
            ])
            ->assertRedirect(route('supervisor.observations.preObservationPlanning', $observation->id));

        $observation->refresh();
        $this->assertSame('pre_observation_planning', $observation->stage);
    }

    public function test_school_head_role_is_gated_the_same_way(): void
    {
        $observation = Observation::factory()
            ->forObserver($this->shUser)
            ->forObservee($this->teacher)
            ->create([
                'observation_type' => 'teacher_observation',
                'stage' => 'pre_observation_planning',
                'status' => 'scheduled',
                'confirmation_status' => 'pending',
            ]);

        $this->actingAs($this->shUser)
            ->post(route('school-head.observations.storePreObservationPlanning', $observation->id), [
                'continue' => 'observation',
            ])
            ->assertRedirect(route('school-head.observations.preObservationPlanning', $observation->id));

        $observation->refresh();
        $this->assertSame('pre_observation_planning', $observation->stage);

        $observation->update(['confirmation_status' => 'confirmed', 'confirmed_at' => now()]);

        $this->actingAs($this->shUser)
            ->post(route('school-head.observations.storePreObservationPlanning', $observation->id), [
                'continue' => 'observation',
            ])
            ->assertRedirect(route('school-head.observations.observation', $observation->id));

        $observation->refresh();
        $this->assertSame('observation', $observation->stage);
    }

    public function test_missing_confirmations_lists_pending_parties(): void
    {
        $observation = $this->planningObservation(['school_head_id' => $this->shUser->id]);

        $this->assertSame(['Teacher', 'School head (co-observer)'], $observation->missingConfirmations());
        $this->assertFalse($observation->canAdvanceFromPlanning());

        $observation->update(['confirmation_status' => 'confirmed']);

        $this->assertSame(['School head (co-observer)'], $observation->fresh()->missingConfirmations());
    }
}
