<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\SupervisorProfile;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupervisorObsRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'aspire-prototype']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
    }

    protected function tearDown(): void
    {
        foreach (Observation::where('observer_id', 7)->where('id', '>', 9)->get() as $x) {
            $x->delete();
        }
        parent::tearDown();
    }

    public function test_supervisor_observation_renders_for_school_head_observee()
    {
        $this->actingAs(User::find(7));
        $shObs = Observation::create([
            'observation_type' => 'school_head_observation',
            'observer_id' => 7,
            'observer_type' => SupervisorProfile::class,
            'observee_id' => 2,
            'observee_type' => SchoolHeadProfile::class,
            'school_id' => '',
            'school_head_id' => null,
            'teacher_id' => null,
            'subject' => 'English', 'grade_level' => 'junior_high',
            'school_year' => '2026-2027', 'observation_date' => '2025-09-10',
            'stage' => 'observation', 'status' => 'scheduled',
            'observation_mode' => 'in_person', 'quarter' => 1, 'observation_number' => 1,
        ]);
        $r = $this->get("/supervisor/observations/{$shObs->id}/observation");
        $r->assertStatus(200);
        $this->assertStringContainsString('Enhanced Post Observation Conference', $r->getContent());
        $this->assertStringNotContainsString('totalIndicators', $r->getContent());
    }

    public function test_supervisor_observation_renders_for_teacher_observee()
    {
        $this->actingAs(User::find(7));
        $tObs = Observation::create([
            'observation_type' => 'teacher_observation',
            'observer_id' => 7,
            'observer_type' => SupervisorProfile::class,
            'observee_id' => 1,
            'observee_type' => Teacher::class,
            'school_id' => '', 'school_head_id' => 8, 'teacher_id' => null,
            'subject' => 'Mathematics', 'grade_level' => 'junior_high',
            'school_year' => '2026-2027', 'observation_date' => '2025-09-10',
            'stage' => 'observation', 'status' => 'scheduled',
            'observation_mode' => 'in_person', 'quarter' => 1, 'observation_number' => 1,
        ]);
        $r = $this->get("/supervisor/observations/{$tObs->id}/observation");
        $r->assertStatus(200);
        $content = $r->getContent();
        $this->assertStringContainsString('totalIndicators', $content);
        $this->assertStringNotContainsString('EPOC Evaluation', $content);
    }

    public function test_epoc_routes_are_rejected_for_teacher_observations()
    {
        $this->actingAs(User::find(7));
        $tObs = Observation::create([
            'observation_type' => 'teacher_observation',
            'observer_id' => 7,
            'observer_type' => SupervisorProfile::class,
            'observee_id' => 1,
            'observee_type' => Teacher::class,
            'school_id' => '', 'school_head_id' => 8, 'teacher_id' => null,
            'subject' => 'Mathematics', 'grade_level' => 'junior_high',
            'school_year' => '2026-2027', 'observation_date' => '2025-09-10',
            'stage' => 'observation', 'status' => 'scheduled',
            'observation_mode' => 'in_person', 'quarter' => 1, 'observation_number' => 1,
        ]);

        $this->get("/supervisor/observations/{$tObs->id}/epoc")
            ->assertSessionHas('error');

        $this->post("/supervisor/observations/{$tObs->id}/epoc", [
            'ratings' => [],
        ])->assertSessionHas('error');

        $this->get("/supervisor/observations/{$tObs->id}/epoc/download")
            ->assertSessionHas('error');
    }
}
