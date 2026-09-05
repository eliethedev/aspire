<?php

namespace Tests\Feature;

use App\Models\CareerAdvancement;
use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\SupervisorProfile;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerAdvancementCancelTest extends TestCase
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

        $this->completeProfile($this->supervisor);

        $this->teacher = Teacher::factory()->forSchool($this->school->id)->create([
            'position' => 'Teacher III',
            'career_stage' => 'teacher_i_iii',
            'user_id' => User::factory()->create([
                'role' => 'teacher',
                'school_id' => $this->school->id,
            ])->id,
        ]);
    }

    private function completeProfile(User $user): void
    {
        $user->profile()->create([
            'mobile_number' => '09171234567',
        ]);

        if ($user->role === 'supervisor') {
            $user->supervisorProfile()->create([
                'division_district_assigned' => 'Division of Test District',
                'area_of_specialization' => 'Mathematics',
                'supervisory_level' => 'division',
            ]);
        }

        if ($user->role === 'school_head') {
            $user->schoolHeadProfile()->create([
                'school_id' => $user->school_id,
                'school_type' => 'secondary',
            ]);
        }
    }

    private function pendingAdvancement(): CareerAdvancement
    {
        return CareerAdvancement::factory()->create([
            'teacher_id' => $this->teacher->id,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_supervisor_can_cancel_their_own_pending_advancement(): void
    {
        $advancement = $this->pendingAdvancement();

        $this->actingAs($this->supervisor)->post(
            route('supervisor.career.cancel', $advancement)
        )->assertSessionHas('success');

        $advancement->refresh();
        $this->assertTrue($advancement->isCancelled());
        $this->assertSame('teacher_i_iii', $this->teacher->fresh()->career_stage);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->teacher->user_id,
            'title' => 'Career advancement recommendation withdrawn',
        ]);
    }

    public function test_supervisor_cannot_cancel_another_supervisors_advancement(): void
    {
        $otherSchool = School::factory()->create();
        $otherSupervisor = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $otherSchool->id,
        ]);

        $this->completeProfile($otherSupervisor);

        $advancement = $this->pendingAdvancement();

        $this->actingAs($otherSupervisor)->post(
            route('supervisor.career.cancel', $advancement)
        )->assertForbidden();

        $this->assertTrue($advancement->fresh()->isPendingApproval());
    }

    public function test_supervisor_cannot_cancel_an_already_reviewed_advancement(): void
    {
        $advancement = CareerAdvancement::factory()->approved()->create([
            'teacher_id' => $this->teacher->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->actingAs($this->supervisor)->post(
            route('supervisor.career.cancel', $advancement)
        )->assertSessionHas('error');

        $this->assertSame('approved', $advancement->fresh()->status);
    }

    public function test_supervisor_can_create_a_new_recommendation_after_cancelling_the_previous_one(): void
    {
        $advancement = $this->pendingAdvancement();

        $this->actingAs($this->supervisor)->post(
            route('supervisor.career.cancel', $advancement)
        )->assertSessionHas('success');

        $this->actingAs($this->supervisor)->post(
            route('supervisor.career.allow', $this->teacher),
            ['remarks' => 'Revised recommendation.']
        )->assertSessionHas('success');

        $this->assertSame(2, CareerAdvancement::count());
        $this->assertSame(1, CareerAdvancement::where('status', 'pending_approval')->count());
    }

    public function test_monitor_shows_cancel_button_for_pending_and_not_for_cancelled(): void
    {
        $advancement = $this->pendingAdvancement();

        $response = $this->actingAs($this->supervisor)->get(route('supervisor.career.monitor'));
        $response->assertOk();
        $this->assertStringContainsString(
            route('supervisor.career.cancel', $advancement),
            $response->getContent()
        );

        $advancement->update(['status' => CareerAdvancement::STATUS_CANCELLED]);

        $response = $this->actingAs($this->supervisor)->get(route('supervisor.career.monitor'));
        $this->assertStringNotContainsString(
            route('supervisor.career.cancel', $advancement),
            $response->getContent()
        );
    }

    public function test_cancelled_advancement_moves_to_school_head_history_tab(): void
    {
        $schoolHead = User::factory()->create([
            'role' => 'school_head',
            'school_id' => $this->school->id,
        ]);

        $this->completeProfile($schoolHead);

        CareerAdvancement::factory()->cancelled()->create([
            'teacher_id' => $this->teacher->id,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $this->actingAs($schoolHead)
            ->get(route('school-head.career.advancements.index', ['tab' => 'pending']))
            ->assertOk()
            ->assertDontSee('Cancelled');

        $this->actingAs($schoolHead)
            ->get(route('school-head.career.advancements.index', ['tab' => 'history']))
            ->assertOk()
            ->assertSee('Cancelled');
    }
}