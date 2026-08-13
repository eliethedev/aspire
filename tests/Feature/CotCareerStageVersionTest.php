<?php

namespace Tests\Feature;

use App\Models\CotIndicatorVersion;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CotIndicatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CotCareerStageVersionTest extends TestCase
{
    use RefreshDatabase;

    private const SY = '2026-2027';

    private CotIndicatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->service = app(CotIndicatorService::class);
    }

    private function publishedVersion(array $overrides = []): CotIndicatorVersion
    {
        return CotIndicatorVersion::factory()->published()->create(array_merge([
            'school_year' => self::SY,
        ], $overrides));
    }

    public function test_resolver_prefers_the_exact_career_stage_version(): void
    {
        $this->publishedVersion(['career_stage' => null, 'label' => 'Generic']);
        $mt = $this->publishedVersion([
            'career_stage' => 'master_teacher_i_ii',
            'label' => 'MT I-II',
        ]);

        $version = $this->service->resolveVersionForObservee(self::SY, 'teacher', 'master_teacher_i_ii');

        $this->assertSame($mt->id, $version->id);
    }

    public function test_resolver_falls_back_to_the_stage_agnostic_version(): void
    {
        $generic = $this->publishedVersion(['career_stage' => null, 'label' => 'Generic']);

        $version = $this->service->resolveVersionForObservee(self::SY, 'teacher', 'master_teacher_iii_v');

        $this->assertSame($generic->id, $version->id);
    }

    public function test_resolver_ignores_drafts_and_other_roles(): void
    {
        $this->publishedVersion([
            'ratee_role' => 'school_head',
            'career_stage' => null,
            'label' => 'School Head Instrument',
        ]);
        CotIndicatorVersion::factory()->create([
            'school_year' => self::SY,
            'ratee_role' => 'teacher',
            'career_stage' => 'master_teacher_i_ii',
            'status' => CotIndicatorVersion::STATUS_DRAFT,
            'is_default' => false,
            'label' => 'Draft MT I-II',
        ]);
        $generic = $this->publishedVersion([
            'ratee_role' => 'teacher',
            'career_stage' => null,
            'label' => 'Generic Teacher',
        ]);

        $version = $this->service->resolveVersionForObservee(self::SY, 'teacher', 'teacher_i_iii');

        $this->assertSame($generic->id, $version->id);
    }

    public function test_get_version_for_observee_uses_the_versions_rating_scale(): void
    {
        $scale = ['6' => 'Outstanding', '5' => 'Very Satisfactory'];
        $version = $this->publishedVersion([
            'career_stage' => 'teacher_iv_vii',
            'rating_scale' => $scale,
        ]);

        $teacher = Teacher::factory()->create(['career_stage' => 'teacher_iv_vii']);

        $result = $this->service->getVersionForObservee($teacher, self::SY);

        $this->assertSame($version->id, $result['id']);
        $this->assertSame($scale, $result['rating_scale']);
        $this->assertSame('Teacher IV-VII', $result['career_stage_label']);
    }

    public function test_new_observation_is_pinned_to_the_teachers_stage_version(): void
    {
        $this->publishedVersion(['career_stage' => null, 'label' => 'Generic']);
        $mt = $this->publishedVersion([
            'career_stage' => 'master_teacher_i_ii',
            'label' => 'MT I-II',
        ]);

        $schoolHead = User::factory()->create(['role' => 'school_head']);
        $teacher = Teacher::factory()->create(['career_stage' => 'master_teacher_i_ii']);

        $this->actingAs($schoolHead)
            ->post(route('school-head.observations.store'), [
                'observee_id' => $teacher->id,
                'observation_date' => now()->addDay()->format('Y-m-d'),
                'schedule_type' => 'scheduled',
                'school_year' => self::SY,
                'subject' => 'Mathematics',
                'grade_level' => '7',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('observations', [
            'observee_id' => $teacher->id,
            'school_year' => self::SY,
            'cot_indicator_version_id' => $mt->id,
        ]);
    }

    public function test_rendering_uses_the_pinned_version_even_after_the_stage_changes(): void
    {
        $generic = $this->publishedVersion(['career_stage' => null, 'label' => 'Generic']);
        $this->publishedVersion([
            'career_stage' => 'master_teacher_i_ii',
            'label' => 'MT I-II',
        ]);

        $teacher = Teacher::factory()->create(['career_stage' => 'teacher_i_iii']);
        $observation = Observation::factory()
            ->forObservee($teacher)
            ->create([
                'school_year' => self::SY,
                'cot_indicator_version_id' => $generic->id,
            ]);

        $teacher->career_stage = 'master_teacher_i_ii';
        $teacher->save();

        $result = $this->service->getVersionForObservation($observation);

        $this->assertSame($generic->id, $result['id']);
    }
}
