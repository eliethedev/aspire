<?php

namespace Tests\Feature;

use App\Models\CareerProgressionAssessment;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerProgressionReadinessTest extends TestCase
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

    private function completedObservation(string $date, array $ratings): Observation
    {
        $observation = Observation::factory()->forObservee($this->teacher)->completed()->create([
            'observation_date' => $date,
            'overall_score' => round(collect($ratings)->avg(), 2),
        ]);

        foreach ($ratings as $code => $rating) {
            CotRating::factory()->for($observation)->create([
                'indicator_code' => $code,
                'rating' => $rating,
                'not_observed' => false,
            ]);
        }

        return $observation;
    }

    public function test_current_career_stage_is_displayed_correctly(): void
    {
        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Teacher III')
            ->assertSee('Career Stage I')
            ->assertSee('PPST')
            ->assertSee('Classroom Teaching')
            ->assertSee('Not Yet Assessed');
    }

    public function test_cot_evidence_is_summarized_from_existing_observations(): void
    {
        $this->completedObservation('2026-07-01', ['COT-1' => 5]);
        $this->completedObservation('2026-08-01', ['COT-1' => 6, 'COT-3' => 3]);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Total Observations')
            ->assertSee('4.75 / 6', false)
            ->assertSee('COT-1')
            ->assertSee('COT-3');
    }

    public function test_supervisor_can_create_and_update_a_readiness_assessment(): void
    {
        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'for_review', 'remarks' => 'Consistent classroom management.', 'assessed_at' => '2026-08-01']
        )->assertSessionHasNoErrors();

        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'ready_for_consideration', 'remarks' => 'Evidence suggests readiness.', 'assessed_at' => '2026-08-10']
        )->assertSessionHasNoErrors();

        $this->assertSame(2, CareerProgressionAssessment::count());

        $latest = CareerProgressionAssessment::latest('assessed_at')->first();
        $this->assertSame('ready_for_consideration', $latest->status);
        $this->assertSame($this->teacher->getMorphClass(), $latest->ratee_type);
        $this->assertSame($this->teacher->id, $latest->ratee_id);
        $this->assertSame($this->supervisor->id, $latest->evaluator_id);
        $this->assertSame('Teacher III', $latest->position);
        $this->assertSame('Career Stage I', $latest->career_stage);
        $this->assertSame('PPST', $latest->framework);

        $this->actingAs($this->supervisor)
            ->get(route('supervisor.teachers.show', $this->teacher))
            ->assertSee('Ready for Consideration');
    }

    public function test_admin_can_save_a_readiness_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(
            route('admin.teachers.career-assessment', $this->teacher),
            ['status' => 'needs_development']
        )->assertSessionHasNoErrors();

        $this->assertSame(1, CareerProgressionAssessment::count());
    }

    public function test_admin_teacher_page_renders_readiness_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.teachers.show', $this->teacher))
            ->assertOk()
            ->assertSee('Career Progression Readiness')
            ->assertSee('Career Stage I')
            ->assertSee('PPST')
            ->assertSee('Save Assessment');
    }

    public function test_unauthorized_users_cannot_modify_the_assessment(): void
    {
        $teacherUser = $this->teacher->user;
        $this->actingAs($teacherUser)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'for_review']
        )->assertForbidden();

        $otherSchool = School::factory()->create();
        $otherSupervisor = User::factory()->create(['role' => 'supervisor', 'school_id' => $otherSchool->id]);
        $this->actingAs($otherSupervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'for_review']
        )->assertForbidden();

        $this->assertSame(0, CareerProgressionAssessment::count());
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'promote_now']
        )->assertSessionHasErrors('status');

        $this->assertSame(0, CareerProgressionAssessment::count());
    }

    public function test_assessment_history_is_preserved_when_the_teacher_is_promoted(): void
    {
        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'for_review', 'assessed_at' => '2026-08-01']
        )->assertSessionHasNoErrors();

        $this->teacher->update([
            'position' => 'Teacher IV',
            'career_stage' => 'teacher_iv_vii',
        ]);

        $first = CareerProgressionAssessment::first();
        $this->assertSame('Teacher III', $first->position);
        $this->assertSame('Career Stage I', $first->career_stage);
        $this->assertSame('for_review', $first->status);

        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'ready_for_consideration', 'assessed_at' => '2026-09-01']
        )->assertSessionHasNoErrors();

        $latest = CareerProgressionAssessment::latest('assessed_at')->first();
        $this->assertSame('Teacher IV', $latest->position);
        $this->assertSame('Career Stage II', $latest->career_stage);
        $this->assertSame(2, CareerProgressionAssessment::count());
    }

    public function test_saving_an_assessment_does_not_change_position_or_career_stage(): void
    {
        $before = [
            'position' => $this->teacher->position,
            'career_stage' => $this->teacher->career_stage,
        ];

        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'for_review']
        )->assertSessionHasNoErrors();

        $this->teacher->refresh();
        $this->assertSame($before['position'], $this->teacher->position);
        $this->assertSame($before['career_stage'], $this->teacher->career_stage);
    }

    public function test_existing_cot_observations_remain_unchanged(): void
    {
        $observation = $this->completedObservation('2026-08-01', ['COT-1' => 5, 'COT-2' => 6]);
        $ratingBefore = $observation->cotRatings()->orderBy('id')->first()->rating;

        $this->actingAs($this->supervisor)->post(
            route('supervisor.teachers.career-assessment', $this->teacher),
            ['status' => 'for_review']
        )->assertSessionHasNoErrors();

        $observation->refresh();
        $this->assertSame('completed', $observation->status);
        $this->assertSame(2, $observation->cotRatings()->count());
        $this->assertSame($ratingBefore, $observation->cotRatings()->orderBy('id')->first()->rating);
        $this->assertSame(1, Observation::count());
    }
}
