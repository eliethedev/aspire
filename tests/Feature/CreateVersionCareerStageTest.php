<?php

namespace Tests\Feature;

use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CareerStageResolver;
use App\Services\CotIndicatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateVersionCareerStageTest extends TestCase
{
    use RefreshDatabase;

    private const SY = '2026-2027';

    private User $admin;

    private CareerStageResolver $resolver;

    private CotIndicatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->resolver = app(CareerStageResolver::class);
        $this->service = app(CotIndicatorService::class);
    }

    private function createVersion(array $overrides = []): CotIndicatorVersion
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.cot-indicators.store'), array_merge([
            'school_year' => self::SY,
            'label' => 'Version',
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => 'teacher_i_iii',
            'instrument' => 'cot',
        ], $overrides))->assertSessionHasNoErrors();

        return CotIndicatorVersion::latest('id')->firstOrFail();
    }

    public static function ppstStageProvider(): array
    {
        return [
            'Teacher I-III' => ['teacher_i_iii', 'teacher_i_iii', 'Career Stage I'],
            'Teacher IV-VII' => ['teacher_iv_vii', 'teacher_iv_vii', 'Career Stage II'],
            'Master Teacher I-II' => ['master_teacher_i_ii', 'master_teacher_i_ii', 'Career Stage III'],
            'Master Teacher III-V' => ['master_teacher_iii_v', 'master_teacher_iii_v', 'Career Stage IV'],
        ];
    }

    public static function ppsshStageProvider(): array
    {
        return [
            'Aspiring School Head' => ['aspiring_school_head', 'career_stage_i', 'Career Stage I'],
            'School Principal I-II' => ['school_principal_i_ii', 'career_stage_ii', 'Career Stage II'],
            'School Principal III' => ['school_principal_iii', 'career_stage_iii', 'Career Stage III'],
            'School Principal IV' => ['school_principal_iv', 'career_stage_iv', 'Career Stage IV'],
        ];
    }

    #[DataProvider('ppstStageProvider')]
    public function test_ppst_position_resolves_to_the_correct_career_stage(string $position, string $stage, string $stageLabel): void
    {
        $this->assertSame($stage, $this->resolver->resolve('ppst', 'classroom_teaching', $position));
        $this->assertSame($stageLabel, $this->resolver->stageLabel($stage));

        $this->createVersion(['ratee_position' => $position]);

        $this->assertDatabaseHas('cot_indicator_versions', [
            'school_year' => self::SY,
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => $position,
            'career_stage' => $stage,
            'ratee_role' => 'teacher',
        ]);
    }

    #[DataProvider('ppsshStageProvider')]
    public function test_ppssh_position_resolves_to_the_correct_career_stage(string $position, string $stage, string $stageLabel): void
    {
        $this->assertSame($stage, $this->resolver->resolve('ppssh', 'school_administration', $position));
        $this->assertSame($stageLabel, $this->resolver->stageLabel($stage));

        $this->createVersion([
            'framework' => 'ppssh',
            'career_track' => 'school_administration',
            'ratee_position' => $position,
        ]);

        $this->assertDatabaseHas('cot_indicator_versions', [
            'school_year' => self::SY,
            'framework' => 'ppssh',
            'career_track' => 'school_administration',
            'ratee_position' => $position,
            'career_stage' => $stage,
            'ratee_role' => 'school_head',
        ]);
    }

    public function test_invalid_framework_track_position_combinations_are_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.create'))
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => self::SY,
                'label' => 'PPST Principal (invalid)',
                'framework' => 'ppst',
                'career_track' => 'school_administration',
                'ratee_position' => 'school_principal_i_ii',
            ])
            ->assertSessionHasErrors('career_track');

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.create'))
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => self::SY,
                'label' => 'PPSSH Teacher (invalid)',
                'framework' => 'ppssh',
                'career_track' => 'classroom_teaching',
                'ratee_position' => 'teacher_i_iii',
            ])
            ->assertSessionHasErrors('career_track');

        $this->assertSame(0, CotIndicatorVersion::count());
    }

    public function test_manipulated_career_stage_in_the_request_is_ignored(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => self::SY,
                'label' => 'Teacher I-III COT',
                'framework' => 'ppst',
                'career_track' => 'classroom_teaching',
                'ratee_position' => 'teacher_i_iii',
                'career_stage' => 'master_teacher_iii_v',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cot_indicator_versions', [
            'school_year' => self::SY,
            'framework' => 'ppst',
            'ratee_position' => 'teacher_i_iii',
            'career_stage' => 'teacher_i_iii',
        ]);
    }

    public function test_edit_recalculates_the_career_stage_from_the_new_context(): void
    {
        $version = CotIndicatorVersion::factory()->create([
            'school_year' => self::SY,
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => 'teacher_i_iii',
            'career_stage' => 'teacher_i_iii',
            'status' => CotIndicatorVersion::STATUS_DRAFT,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.cot-indicators.update', $version), [
                'school_year' => self::SY,
                'label' => 'Principal III instrument',
                'framework' => 'ppssh',
                'career_track' => 'school_administration',
                'ratee_position' => 'school_principal_iii',
            ])
            ->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseHas('cot_indicator_versions', [
            'id' => $version->id,
            'framework' => 'ppssh',
            'career_track' => 'school_administration',
            'ratee_position' => 'school_principal_iii',
            'career_stage' => 'career_stage_iii',
            'ratee_role' => 'school_head',
        ]);
    }

    public function test_identity_change_is_blocked_when_the_version_has_observations(): void
    {
        $version = CotIndicatorVersion::factory()->create([
            'school_year' => self::SY,
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => 'teacher_i_iii',
            'career_stage' => 'teacher_i_iii',
            'status' => CotIndicatorVersion::STATUS_DRAFT,
        ]);

        $teacher = Teacher::factory()->create(['career_stage' => 'teacher_i_iii']);
        Observation::factory()
            ->forObserver($this->admin)
            ->forObservee($teacher)
            ->create([
                'school_year' => self::SY,
                'cot_indicator_version_id' => $version->id,
            ]);

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.edit', $version))
            ->put(route('admin.cot-indicators.update', $version), [
                'school_year' => self::SY,
                'label' => 'Should not change identity',
                'framework' => 'ppst',
                'career_track' => 'classroom_teaching',
                'ratee_position' => 'master_teacher_i_ii',
            ])
            ->assertSessionHasErrors('framework');

        $this->assertDatabaseHas('cot_indicator_versions', [
            'id' => $version->id,
            'ratee_position' => 'teacher_i_iii',
            'career_stage' => 'teacher_i_iii',
        ]);
    }

    public function test_versions_are_identified_by_full_context_not_school_year_and_stage(): void
    {
        $this->createVersion();
        $this->createVersion([
            'framework' => 'ppssh',
            'career_track' => 'school_administration',
            'ratee_position' => 'school_principal_i_ii',
        ]);

        $this->assertSame(2, CotIndicatorVersion::count());
        $this->assertDatabaseHas('cot_indicator_versions', [
            'school_year' => self::SY,
            'framework' => 'ppst',
            'career_stage' => 'teacher_i_iii',
        ]);
        $this->assertDatabaseHas('cot_indicator_versions', [
            'school_year' => self::SY,
            'framework' => 'ppssh',
            'career_stage' => 'career_stage_ii',
        ]);
    }

    public function test_duplicate_context_is_rejected_for_the_same_school_year(): void
    {
        $this->createVersion();

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.create'))
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => self::SY,
                'label' => 'Duplicate Teacher I-III COT',
                'framework' => 'ppst',
                'career_track' => 'classroom_teaching',
                'ratee_position' => 'teacher_i_iii',
            ])
            ->assertSessionHasErrors('school_year');

        $this->assertSame(1, CotIndicatorVersion::count());
    }

    public function test_existing_teacher_i_iii_cot_versions_still_resolve(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create([
            'school_year' => self::SY,
            'label' => 'COT 2026-2027',
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => 'teacher_i_iii',
            'career_stage' => null,
        ]);
        CotIndicator::factory()->count(3)->create(['version_id' => $version->id]);

        $resolved = $this->service->resolveVersionForObservee(self::SY, 'teacher', 'teacher_i_iii');

        $this->assertSame($version->id, $resolved->id);
        $this->assertSame(3, $resolved->indicators->count());
        $this->assertSame('PPST', $version->frameworkLabel());
        $this->assertSame('Classroom Teaching', $version->careerTrackLabel());
        $this->assertSame('Teacher I-III', $version->rateePositionLabel());
        $this->assertSame('COT', $version->instrumentLabel());
    }

    public function test_creating_or_editing_a_version_never_touches_historical_observations(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create([
            'school_year' => self::SY,
            'label' => 'COT 2026-2027',
            'framework' => 'ppst',
            'career_track' => 'classroom_teaching',
            'ratee_position' => 'teacher_i_iii',
            'career_stage' => null,
        ]);

        $teacher = Teacher::factory()->create(['career_stage' => 'teacher_i_iii']);
        $observation = Observation::factory()
            ->forObserver($this->admin)
            ->forObservee($teacher)
            ->create([
                'school_year' => self::SY,
                'cot_indicator_version_id' => $version->id,
            ]);

        $this->createVersion([
            'label' => 'A different version for the same year',
            'ratee_position' => 'teacher_iv_vii',
        ]);

        $this->assertDatabaseHas('observations', [
            'id' => $observation->id,
            'cot_indicator_version_id' => $version->id,
        ]);

        $rendered = $this->service->getVersionForObservation($observation);
        $this->assertSame($version->id, $rendered['id']);
        $this->assertSame('COT 2026-2027', $rendered['label']);
    }
}
