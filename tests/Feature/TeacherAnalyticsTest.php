<?php

namespace Tests\Feature;

use App\Models\CotRating;
use App\Models\Observation;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::factory()->create();

        $this->teacherUser = User::factory()->create([
            'role' => 'teacher',
            'school_id' => $school->id,
        ]);

        $this->teacher = Teacher::factory()->forSchool($school->id)->create([
            'position' => 'Teacher II',
            'career_stage' => 'teacher_i_iii',
            'user_id' => $this->teacherUser->id,
        ]);
    }

    private function scoredObservation(string $date, float $score): Observation
    {
        return Observation::factory()
            ->forObservee($this->teacher)
            ->completed()
            ->create([
                'observation_date' => $date,
                'overall_score' => $score,
            ]);
    }

    public function test_analytics_page_renders_stats_charts_and_indicators(): void
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
            'indicator_code' => 'COT-RP',
            'indicator' => 'Manage classroom structure',
            'domain' => 'Learning Environment',
            'rating' => 3,
            'not_observed' => false,
        ]);

        $response = $this->actingAs($this->teacherUser)
            ->get(route('teacher.analytics'));

        $response->assertOk()
            ->assertSee('Performance Analytics')
            ->assertSee('Total Observations')
            ->assertSee('Observations per Month')
            ->assertSee('Rating Distribution')
            ->assertSee('Domain Averages')
            ->assertSee('Your Strengths')
            ->assertSee('Growth Opportunities')
            ->assertSee('Content Knowledge and Pedagogy')
            ->assertSee('COT-1')
            ->assertSee('4.50');
    }

    public function test_analytics_excludes_other_teachers_data(): void
    {
        $otherSchool = School::factory()->create();
        $otherTeacher = Teacher::factory()->forSchool($otherSchool->id)->create([
            'position' => 'Teacher I',
            'career_stage' => 'teacher_i_iii',
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'school_id' => $otherSchool->id,
            ])->id,
        ]);

        Observation::factory()
            ->forObservee($otherTeacher)
            ->completed()
            ->create(['observation_date' => '2026-08-01', 'overall_score' => 6.0]);

        $content = $this->actingAs($this->teacherUser)
            ->get(route('teacher.analytics'))
            ->assertOk()
            ->getContent();

        // Only the other teacher has scored observations, so this teacher's
        // trend chart must stay empty (all nulls) and counts all zero.
        $this->assertStringContainsString('[null,null,null,null,null,null,null,null,null,null,null,null]', $content);
        $this->assertStringContainsString('[0,0,0,0,0,0,0,0,0,0,0,0]', $content);
    }

    public function test_sidebar_links_to_analytics_page(): void
    {
        Observation::factory()->forObservee($this->teacher)->completed()->create([
            'observation_date' => '2026-08-01',
            'overall_score' => 4.0,
        ]);

        $this->actingAs($this->teacherUser)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee(route('teacher.analytics'));
    }
}
