<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolHeadCoObserveConfirmTest extends TestCase
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
        $this->shUser = User::factory()->create(['role' => 'school_head']);
        SchoolHeadProfile::create(['user_id' => $this->shUser->id]);
    }

    private function coObservation(array $overrides = []): Observation
    {
        return Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create(array_merge([
                'observation_type' => 'teacher_observation',
                'stage' => 'pre_observation_planning',
                'status' => 'scheduled',
                'confirmation_status' => 'pending',
                'school_head_id' => $this->shUser->id,
            ], $overrides));
    }

    public function test_assigned_school_head_can_open_co_observation(): void
    {
        $observation = $this->coObservation();

        $this->actingAs($this->shUser)
            ->get(route('school-head.observations.show', $observation->id))
            ->assertOk()
            ->assertSee('Confirm your attendance', false);
    }

    public function test_assigned_school_head_can_confirm_co_observation(): void
    {
        $observation = $this->coObservation();

        $this->actingAs($this->shUser)
            ->post(route('school-head.observations.confirm', $observation->id))
            ->assertRedirect();

        $observation->refresh();
        $this->assertSame('confirmed', $observation->school_head_confirmation_status);
        $this->assertNotNull($observation->school_head_confirmed_at);

        // The supervisor is notified.
        $this->assertTrue(
            Notification::where('user_id', $this->supervisor->id)
                ->where('title', 'Observation Confirmed')
                ->exists()
        );
    }

    public function test_assigned_school_head_can_reject_co_observation(): void
    {
        $observation = $this->coObservation();

        $this->actingAs($this->shUser)
            ->post(route('school-head.observations.reject', $observation->id), [
                'rejection_reason' => 'scheduling_conflict',
                'rejection_notes' => 'Division meeting.',
            ])
            ->assertRedirect();

        $observation->refresh();
        $this->assertSame('rejected', $observation->school_head_confirmation_status);
        $this->assertSame('scheduling_conflict', $observation->school_head_rejection_reason);
        $this->assertNotNull($observation->school_head_rejected_at);
    }

    public function test_unassigned_school_head_cannot_confirm(): void
    {
        $observation = $this->coObservation();
        $other = User::factory()->create(['role' => 'school_head']);
        SchoolHeadProfile::create(['user_id' => $other->id]);

        $this->actingAs($other)
            ->post(route('school-head.observations.confirm', $observation->id))
            ->assertForbidden();
    }

    public function test_supervisor_pre_observation_highlights_both_confirmations(): void
    {
        $observation = $this->coObservation([
            'confirmation_status' => 'confirmed',
            'confirmed_at' => now(),
            'school_head_confirmation_status' => 'confirmed',
            'school_head_confirmed_at' => now(),
        ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.preObservationPlanning', $observation->id))
            ->assertOk()
            ->assertSee('Schedule Confirmations', false)
            ->assertSee('Both confirmed', false);
    }

    public function test_supervisor_pre_observation_shows_pending_school_head(): void
    {
        $observation = $this->coObservation();

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.preObservationPlanning', $observation->id))
            ->assertOk()
            ->assertSee('Waiting for school head', false)
            ->assertSee('Waiting for teacher', false);
    }
}
