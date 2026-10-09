<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureProfileComplete;
use App\Models\CotIndicatorVersion;
use App\Models\Notification;
use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkedPpsshObservationTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private Teacher $teacher;

    private User $shUser;

    private SchoolHeadProfile $shProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureProfileComplete::class);

        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);

        $school = School::factory()->create();
        $this->shUser = User::factory()->create(['role' => 'school_head']);
        $this->shProfile = SchoolHeadProfile::create([
            'user_id' => $this->shUser->id,
            'school_id' => $school->id,
            'position_level' => 'principal_i',
            'current_designation' => 'principal',
            'position' => 'Principal I',
        ]);

        CotIndicatorVersion::factory()->published()->create([
            'school_year' => '2026-2027',
            'ratee_role' => 'school_head',
            'label' => 'School Head Instrument',
        ]);
    }

    private function teacherObservation(array $overrides = []): Observation
    {
        return Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create(array_merge([
                'observation_type' => 'teacher_observation',
                'stage' => 'observation',
                'status' => 'in_progress',
                'school_year' => '2026-2027',
                'school_head_id' => $this->shUser->id,
            ], $overrides));
    }

    public function test_supervisor_can_create_linked_ppssh_observation(): void
    {
        $observation = $this->teacherObservation();

        $response = $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id));

        $linked = Observation::where('related_observation_id', $observation->id)->first();

        $this->assertNotNull($linked);
        $this->assertSame('school_head_observation', $linked->observation_type);
        $this->assertSame($this->shProfile->id, (int) $linked->observee_id);
        $this->assertSame('App\Models\SchoolHeadProfile', $linked->observee_type);
        $this->assertSame('pre_observation_planning', $linked->stage);
        // No co-observer of its own: the supervisor observes the school head
        // directly, so the same person is not listed twice.
        $this->assertNull($linked->school_head_id);
        // Parent co-observe confirmation is still pending: child stays pending.
        $this->assertSame('pending', $linked->confirmation_status);

        $response->assertRedirect(route('supervisor.observations.preObservationPlanning', $linked->id));

        // Both parties are notified.
        $this->assertTrue(
            Notification::where('user_id', $this->shUser->id)
                ->where('title', 'School Head Evaluation Scheduled')
                ->exists()
        );
        $this->assertTrue(
            Notification::where('user_id', $this->teacher->user->id)
                ->where('title', 'Linked PPSSH Observation Created')
                ->exists()
        );
    }

    public function test_linked_observation_inherits_confirmed_co_observe_schedule(): void
    {
        $observation = $this->teacherObservation([
            'school_head_confirmation_status' => 'confirmed',
            'school_head_confirmed_at' => now()->subHour(),
        ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertRedirect();

        $linked = Observation::where('related_observation_id', $observation->id)->first();

        $this->assertNotNull($linked);
        $this->assertSame('confirmed', $linked->confirmation_status);
        $this->assertNotNull($linked->confirmed_at);
        $this->assertNull($linked->school_head_id);
        $this->assertTrue($linked->hasBothConfirmations());
    }

    public function test_pending_child_can_still_be_confirmed_by_school_head_as_observee(): void
    {
        $observation = $this->teacherObservation();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertRedirect();

        $linked = Observation::where('related_observation_id', $observation->id)->first();
        $this->assertNotNull($linked);

        $this->actingAs($this->shUser)
            ->post(route('school-head.observations.confirm', $linked->id))
            ->assertRedirect();

        $linked->refresh();
        $this->assertSame('confirmed', $linked->confirmation_status);
    }

    public function test_duplicate_linked_observation_is_blocked(): void
    {
        $observation = $this->teacherObservation();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertRedirect();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertRedirect();

        $this->assertSame(
            1,
            Observation::where('related_observation_id', $observation->id)->count()
        );
    }

    public function test_linked_observation_requires_teacher_observation(): void
    {
        $shObservation = Observation::factory()
            ->forObserver($this->supervisor)
            ->create([
                'observation_type' => 'school_head_observation',
                'observee_id' => $this->shProfile->id,
                'observee_type' => SchoolHeadProfile::class,
                'school_head_id' => $this->shUser->id,
            ]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $shObservation->id))
            ->assertRedirect();

        $this->assertSame(
            0,
            Observation::where('related_observation_id', $shObservation->id)->count()
        );
    }

    public function test_linked_observation_requires_assigned_school_head(): void
    {
        $observation = $this->teacherObservation(['school_head_id' => null]);

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertRedirect();

        $this->assertSame(
            0,
            Observation::where('related_observation_id', $observation->id)->count()
        );
    }

    public function test_other_supervisor_cannot_create_linked_observation(): void
    {
        $observation = $this->teacherObservation();
        $other = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($other)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertForbidden();
    }

    public function test_show_page_offers_evaluate_co_observer_action(): void
    {
        $observation = $this->teacherObservation();

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.show', $observation->id))
            ->assertOk()
            ->assertSee('Evaluate co-observer (PPSSH)', false);
    }

    public function test_show_page_links_existing_linked_observation(): void
    {
        $observation = $this->teacherObservation();

        $this->actingAs($this->supervisor)
            ->post(route('supervisor.observations.linked-ppssh', $observation->id))
            ->assertRedirect();

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.observations.show', $observation->id))
            ->assertOk()
            ->assertSee('View linked principal evaluation', false)
            ->assertDontSee('Evaluate co-observer (PPSSH)', false);
    }
}
