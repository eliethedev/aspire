<?php

namespace Tests\Feature;

use App\Models\CotRating;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObservationAutosaveTest extends TestCase
{
    use RefreshDatabase;

    private User $supervisor;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->teacher = Teacher::factory()->create();
    }

    private function observation(array $attributes = []): Observation
    {
        return Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->create(array_merge([
                'status' => 'in_progress',
                'stage' => 'observation',
            ], $attributes));
    }

    public function test_supervisor_can_autosave_observation_ratings(): void
    {
        $observation = $this->observation();

        $response = $this->actingAs($this->supervisor)->postJson(
            route('supervisor.observations.autosave', $observation),
            [
                'stage' => 'observation',
                'ratings' => [
                    [
                        'indicator_code' => '1.1.1',
                        'domain' => 'Content Knowledge and Pedagogy',
                        'indicator' => 'Applies knowledge of content',
                        'rating' => 5,
                        'not_observed' => false,
                    ],
                ],
            ]
        );

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('cot_ratings', [
            'observation_id' => $observation->id,
            'indicator_code' => '1.1.1',
            'rating' => 5,
        ]);

        $this->assertSame('observation', $observation->fresh()->stage);
    }

    public function test_autosave_does_not_overwrite_untouched_ratings(): void
    {
        $observation = $this->observation();

        CotRating::factory()->create([
            'observation_id' => $observation->id,
            'indicator_code' => '1.1.1',
        ])->update(['rating' => 5, 'not_observed' => false]);

        $this->actingAs($this->supervisor)->postJson(
            route('supervisor.observations.autosave', $observation),
            [
                'stage' => 'observation',
                // Row present but untouched: no rating, no not_observed.
                'ratings' => [
                    ['indicator_code' => '1.1.1', 'domain' => 'Domain', 'indicator' => 'Indicator'],
                ],
            ]
        )->assertOk();

        $this->assertSame(5, CotRating::where('observation_id', $observation->id)
            ->where('indicator_code', '1.1.1')
            ->value('rating'));
    }

    public function test_supervisor_can_autosave_pre_conference_fields(): void
    {
        $observation = $this->observation(['stage' => 'pre_conference']);

        $this->actingAs($this->supervisor)->postJson(
            route('supervisor.observations.autosave', $observation),
            [
                'stage' => 'pre_conference',
                'discussion_notes' => 'Draft of the discussion.',
                'finalized_focus' => 'Higher-order thinking skills',
            ]
        )->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('pre_conferences', [
            'observation_id' => $observation->id,
            'discussion_notes' => 'Draft of the discussion.',
            'finalized_focus' => 'Higher-order thinking skills',
        ]);
    }

    public function test_supervisor_cannot_autosave_another_supervisors_observation(): void
    {
        $otherSupervisor = User::factory()->create(['role' => 'supervisor']);
        $observation = Observation::factory()
            ->forObserver($otherSupervisor)
            ->forObservee($this->teacher)
            ->create();

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.autosave', $observation), ['stage' => 'observation'])
            ->assertForbidden();
    }

    public function test_unknown_autosave_stage_is_rejected(): void
    {
        $observation = $this->observation();

        $this->actingAs($this->supervisor)
            ->postJson(route('supervisor.observations.autosave', $observation), ['stage' => 'not_a_stage'])
            ->assertStatus(422);
    }
}
