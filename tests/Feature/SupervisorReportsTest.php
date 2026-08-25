<?php

namespace Tests\Feature;

use App\Models\CotRating;
use App\Models\Observation;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorReportsTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Teacher $teacher;

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
    }

    private function scoredObservation(string $date, float $score): Observation
    {
        return Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($this->teacher)
            ->completed()
            ->create([
                'observation_date' => $date,
                'overall_score' => $score,
            ]);
    }

    public function test_overview_tab_renders_stats_and_recent_observations(): void
    {
        $this->scoredObservation('2026-08-01', 5.5);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.reports.index'))
            ->assertOk()
            ->assertSee('Observation Reports')
            ->assertSee('Total Teachers')
            ->assertSee($this->teacher->user->name);
    }

    public function test_analytics_tab_renders_charts_and_domain_breakdown(): void
    {
        $observation = $this->scoredObservation('2026-08-01', 4.5);

        CotRating::factory()->for($observation)->create([
            'indicator_code' => 'COT-1',
            'indicator' => 'Apply knowledge of content',
            'domain' => 'Content Knowledge and Pedagogy',
            'rating' => 5,
            'not_observed' => false,
        ]);

        CotRating::factory()->for($observation)->create([
            'indicator_code' => 'COT-2',
            'indicator' => 'Use research-based knowledge',
            'domain' => 'Content Knowledge and Pedagogy',
            'rating' => 3,
            'not_observed' => false,
        ]);

        CotRating::factory()->for($observation)->create([
            'indicator_code' => 'COT-3',
            'indicator' => 'Positive use of ICT',
            'domain' => 'Learning Environment',
            'rating' => null,
            'not_observed' => true,
        ]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.reports.index', ['view' => 'analytics']))
            ->assertOk()
            ->assertSee('Analytics', false)
            ->assertSee('Observations per Month')
            ->assertSee('Rating Distribution')
            ->assertSee('Content Knowledge and Pedagogy')
            ->assertSee('Strongest Indicators')
            ->assertSee('COT-1');
    }

    public function test_performance_tab_lists_teacher_rows_with_trend(): void
    {
        $this->scoredObservation('2026-07-01', 4.0);
        $this->scoredObservation('2026-08-01', 5.0);

        // A second, never-observed teacher should appear with no data.
        $unobserved = Teacher::factory()->forSchool($this->school->id)->create([
            'position' => 'Teacher I',
            'career_stage' => 'teacher_i_iii',
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'school_id' => $this->school->id,
            ])->id,
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get(route('supervisor.reports.index', ['view' => 'performance']));

        $response->assertOk()
            ->assertSee('Teacher Performance')
            ->assertSee('School Average')
            ->assertSee('Very Satisfactory');

        $content = $response->getContent();
        $this->assertStringContainsString($this->teacher->user->name, $content);
        $this->assertStringContainsString($unobserved->user->name, $content);
        $this->assertStringContainsString('+1.00', $content);
        $this->assertStringContainsString('No data yet', $content);
    }

    public function test_performance_excludes_other_schools_teachers(): void
    {
        $otherSchool = School::factory()->create();
        $otherTeacher = Teacher::factory()->forSchool($otherSchool->id)->create([
            'position' => 'Master Teacher I',
            'career_stage' => 'master_teacher_i_ii',
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'school_id' => $otherSchool->id,
            ])->id,
        ]);

        Observation::factory()
            ->forObserver($this->supervisor)
            ->forObservee($otherTeacher)
            ->completed()
            ->create(['observation_date' => '2026-08-01', 'overall_score' => 6.0]);

        $content = $this->actingAs($this->supervisor)
            ->get(route('supervisor.reports.index', ['view' => 'performance']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString($otherTeacher->user->name, $content);
        $this->assertStringNotContainsString('Outstanding', $content);
    }

    public function test_invalid_view_param_falls_back_to_overview(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('supervisor.reports.index', ['view' => 'hacked']))
            ->assertOk()
            ->assertSee('Total Teachers');
    }
}
