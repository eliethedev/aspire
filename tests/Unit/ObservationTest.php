<?php

namespace Tests\Unit;

use App\Models\CotRating;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_observation_belongs_to_observer_and_observee(): void
    {
        $observer = User::factory()->create(['role' => 'supervisor']);
        $teacher = Teacher::factory()->create();

        $observation = Observation::factory()
            ->forObserver($observer)
            ->forObservee($teacher)
            ->create();

        $this->assertInstanceOf(User::class, $observation->observer);
        $this->assertSame($observer->id, $observation->observer->id);
        $this->assertInstanceOf(Teacher::class, $observation->observee);
        $this->assertSame($teacher->id, $observation->observee->id);
    }

    public function test_is_teacher_observation(): void
    {
        $this->assertTrue(Observation::factory()->create()->isTeacherObservation());
        $this->assertFalse(Observation::factory()->create(['observation_type' => 'school_head_observation'])->isTeacherObservation());
    }

    public function test_is_school_head_observation(): void
    {
        $this->assertTrue(Observation::factory()->create(['observation_type' => 'school_head_observation'])->isSchoolHeadObservation());
    }

    public function test_is_completed_when_stage_is_post_conference(): void
    {
        $this->assertTrue(Observation::factory()->completed()->create()->isCompleted());
        $this->assertFalse(Observation::factory()->create()->isCompleted());
    }

    public function test_has_ratings(): void
    {
        $observation = Observation::factory()->create();

        $this->assertFalse($observation->hasRatings());

        CotRating::factory()->create(['observation_id' => $observation->id]);

        $this->assertTrue($observation->hasRatings());
    }

    public function test_can_cancel_allowed_stages(): void
    {
        foreach (['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'] as $stage) {
            $observation = Observation::factory()->create(['stage' => $stage]);
            $this->assertTrue($observation->canCancel(), "Should allow cancelling at stage {$stage}");
        }
    }

    public function test_cannot_cancel_when_already_cancelled(): void
    {
        $observation = Observation::factory()->cancelled()->create();

        $this->assertFalse($observation->canCancel());
    }

    public function test_cancel_marks_observation_as_cancelled(): void
    {
        $observation = Observation::factory()->create();
        $observer = $observation->observer;

        $this->actingAs($observer);

        $observation->cancel('conflict_in_schedule', 'Internal note');

        $observation->refresh();

        $this->assertSame('cancelled', $observation->status);
        $this->assertSame('conflict_in_schedule', $observation->cancellation_reason);
        $this->assertSame($observer->id, $observation->cancelled_by);
        $this->assertNotNull($observation->cancelled_at);
        $this->assertCount(1, $observation->logs);
    }

    public function test_can_confirm_pending_planning_observation(): void
    {
        $observation = Observation::factory()->create([
            'confirmation_status' => 'pending',
            'stage' => 'pre_observation_planning',
        ]);

        $this->assertTrue($observation->canConfirm());
    }

    public function test_cannot_confirm_after_confirming(): void
    {
        $observation = Observation::factory()->confirmed()->create();

        $this->assertFalse($observation->canConfirm());
    }

    public function test_confirm_sets_confirmation_status(): void
    {
        $observation = Observation::factory()->create();
        $observation->confirm();

        $observation->refresh();

        $this->assertSame('confirmed', $observation->confirmation_status);
        $this->assertNotNull($observation->confirmed_at);
    }

    public function test_reject_sets_rejection_reason(): void
    {
        $observation = Observation::factory()->create();
        $observation->reject('scheduling_conflict', 'Has prior commitment');

        $observation->refresh();

        $this->assertSame('rejected', $observation->confirmation_status);
        $this->assertSame('scheduling_conflict', $observation->rejection_reason);
        $this->assertSame('Has prior commitment', $observation->rejection_notes);
        $this->assertNotNull($observation->rejected_at);
    }

    public function test_pending_scope_includes_incomplete_stages(): void
    {
        Observation::factory()->create(['stage' => 'pre_observation_planning']);
        Observation::factory()->create(['stage' => 'observation']);
        Observation::factory()->completed()->create();

        $this->assertSame(2, Observation::pending()->count());
    }

    public function test_completed_scope(): void
    {
        Observation::factory()->completed()->create();
        Observation::factory()->create();

        $this->assertSame(1, Observation::completed()->count());
    }

    public function test_teacher_observations_scope(): void
    {
        Observation::factory()->create();
        Observation::factory()->create(['observation_type' => 'school_head_observation']);

        $this->assertSame(1, Observation::teacherObservations()->count());
    }

    public function test_pending_confirmation_scope(): void
    {
        Observation::factory()->create(['confirmation_status' => 'pending']);
        Observation::factory()->confirmed()->create();

        $this->assertSame(1, Observation::pendingConfirmation()->count());
    }

    public function test_confirmed_and_rejected_scopes(): void
    {
        Observation::factory()->confirmed()->create();
        Observation::factory()->create(['confirmation_status' => 'rejected']);

        $this->assertSame(1, Observation::confirmed()->count());
        $this->assertSame(1, Observation::rejected()->count());
    }

    public function test_cot_ratings_relationship(): void
    {
        $observation = Observation::factory()->create();
        CotRating::factory()->count(3)->create(['observation_id' => $observation->id]);

        $this->assertCount(3, $observation->cotRatings);
    }
}
