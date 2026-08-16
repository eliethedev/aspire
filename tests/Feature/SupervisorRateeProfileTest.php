<?php

namespace Tests\Feature;

use App\Models\CotRating;
use App\Models\Observation;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorRateeProfileTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Teacher $teacher;

    private SchoolHeadProfile $schoolHead;

    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();

        $this->supervisor = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $this->school->id,
        ]);

        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create([
            'position' => 'Teacher III',
            'career_stage' => 'teacher_i_iii',
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'school_id' => $this->school->id,
            ])->id,
        ]);

        $this->schoolHead = SchoolHeadProfile::create([
            'user_id' => User::factory()->create([
                'role' => 'school_head',
                'school_id' => $this->school->id,
            ])->id,
            'school_id' => $this->school->id,
            'position_level' => 'principal_ii',
            'current_designation' => 'principal',
        ]);
    }

    private function completedObservationFor(Model $observee, string $date, array $ratings, array $extra = []): Observation
    {
        $observation = Observation::factory()
            ->forObservee($observee)
            ->forObserver($this->supervisor)
            ->completed()
            ->create(array_merge([
                'observation_date' => $date,
                'observee_type' => $observee::class,
                'observation_type' => $observee instanceof Teacher ? 'teacher_observation' : 'school_head_observation',
                'overall_score' => round(collect($ratings)->avg(), 2),
            ], $extra));

        foreach ($ratings as $domain => $rating) {
            CotRating::factory()->for($observation)->create([
                'domain' => $domain,
                'rating' => $rating,
                'not_observed' => false,
            ]);
        }

        return $observation;
    }

    public function test_teacher_profile_renders_ratee_sections_in_order(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Observation Summary')
            ->assertSee('Performance by Domain')
            ->assertSee('Areas Requiring Attention')
            ->assertSee('Recent Observations')
            ->assertSee('Supervisor Actions')
            ->assertSee('Career Progression Readiness')
            ->assertSee('Teacher')
            ->assertSee('Teacher III');
    }

    public function test_teacher_without_observations_shows_empty_states(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Not enough observation data yet.')
            ->assertSee('No scored observations yet.')
            ->assertSee('No observations recorded yet.')
            ->assertSee('Average Rating')
            ->assertSee('None yet')
            ->assertSee('None scheduled');
    }

    public function test_domain_summary_and_areas_requiring_attention_are_shown(): void
    {
        $this->completedObservationFor($this->teacher, '2026-07-01', [
            'Assessment and Reporting' => 3,
            'Content Knowledge and Pedagogy' => 5,
        ]);
        $this->completedObservationFor($this->teacher, '2026-08-01', [
            'Assessment and Reporting' => 4,
            'Content Knowledge and Pedagogy' => 6,
        ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Performance by Domain')
            ->assertSee('Content Knowledge and Pedagogy')
            ->assertSee('Assessment and Reporting')
            ->assertSee('Areas Requiring Attention')
            ->assertSee('Average across 2 observations')
            ->assertSee('3.50 / 6', false)
            ->assertSee('5.50');
    }

    public function test_areas_requiring_attention_requires_two_observations(): void
    {
        $this->completedObservationFor($this->teacher, '2026-07-01', [
            'Assessment and Reporting' => 3,
        ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Not enough observation data yet.');
    }

    public function test_upcoming_observation_is_shown_in_summary(): void
    {
        Observation::factory()
            ->forObservee($this->teacher)
            ->forObserver($this->supervisor)
            ->create([
                'observation_date' => now()->addDays(5)->toDateString(),
                'stage' => 'pre_observation_planning',
                'status' => 'scheduled',
            ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Upcoming Observation')
            ->assertSee(now()->addDays(5)->format('M d, Y'));
    }

    public function test_pending_post_conference_action_is_shown(): void
    {
        Observation::factory()
            ->forObservee($this->teacher)
            ->forObserver($this->supervisor)
            ->create([
                'observation_date' => now()->addDay()->toDateString(),
                'stage' => 'post_conference',
                'status' => 'in_progress',
            ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Start / View Post-Observation Conference');
    }

    public function test_school_head_profile_renders(): void
    {
        $this->completedObservationFor($this->schoolHead, '2026-07-01', [
            'Assessment and Reporting' => 3,
            'Content Knowledge and Pedagogy' => 5,
        ]);
        $this->completedObservationFor($this->schoolHead, '2026-08-01', [
            'Assessment and Reporting' => 4,
            'Content Knowledge and Pedagogy' => 6,
        ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.school-heads.show', $this->schoolHead))
            ->assertOk()
            ->assertSee('School Head')
            ->assertSee('Principal II')
            ->assertSee('Observation Summary')
            ->assertSee('Performance by Domain')
            ->assertSee('Areas Requiring Attention')
            ->assertSee('Recent Observations')
            ->assertSee('Supervisor Actions')
            ->assertSee('Average across 2 observations');
    }

    public function test_school_head_without_observations_shows_empty_states(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('supervisor.school-heads.show', $this->schoolHead))
            ->assertOk()
            ->assertSee('Not enough observation data yet.')
            ->assertSee('No observations recorded yet.');
    }

    public function test_cross_school_teacher_is_forbidden(): void
    {
        $otherSchool = School::factory()->create();
        $otherSupervisor = User::factory()->create(['role' => 'supervisor', 'school_id' => $otherSchool->id]);

        $this->actingAs($otherSupervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertForbidden();
    }

    public function test_cross_school_school_head_is_forbidden(): void
    {
        $otherSchool = School::factory()->create();
        $otherSupervisor = User::factory()->create(['role' => 'supervisor', 'school_id' => $otherSchool->id]);

        $this->actingAs($otherSupervisor)
            ->get(route('supervisor.school-heads.show', $this->schoolHead))
            ->assertForbidden();

        $this->actingAs($otherSupervisor)
            ->get(route('supervisor.school-heads.observations', $this->schoolHead))
            ->assertForbidden();
    }

    public function test_viewing_ratee_profile_does_not_modify_data(): void
    {
        $observation = $this->completedObservationFor($this->teacher, '2026-07-01', [
            'Assessment and Reporting' => 3,
            'Content Knowledge and Pedagogy' => 5,
        ]);
        $ratingBefore = $observation->cotRatings()->orderBy('id')->first()->rating;
        $positionBefore = $this->teacher->position;
        $stageBefore = $this->teacher->career_stage;

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk();

        $observation->refresh();
        $this->teacher->refresh();

        $this->assertSame('completed', $observation->status);
        $this->assertSame(2, $observation->cotRatings()->count());
        $this->assertSame($ratingBefore, $observation->cotRatings()->orderBy('id')->first()->rating);
        $this->assertSame($positionBefore, $this->teacher->position);
        $this->assertSame($stageBefore, $this->teacher->career_stage);
        $this->assertSame(1, Observation::count());
    }
}
