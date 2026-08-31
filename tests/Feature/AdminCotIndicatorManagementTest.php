<?php

namespace Tests\Feature;

use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\Observation;
use App\Models\PpstStandard;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCotIndicatorManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeDraftVersion(): CotIndicatorVersion
    {
        return CotIndicatorVersion::factory()->create([
            'school_year' => '2026-2027',
            'label' => 'COT for Proficient Teachers',
            'status' => CotIndicatorVersion::STATUS_DRAFT,
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)
            ->get(route('admin.cot-indicators.index'))
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => '2026-2027',
                'label' => 'Nope',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_list_versions(): void
    {
        $draft = $this->makeDraftVersion();
        $published = CotIndicatorVersion::factory()->published()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cot-indicators.index'))
            ->assertOk();

        $response->assertSee($draft->label);
        $response->assertSee($published->label);
    }

    public function test_admin_can_view_the_create_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.cot-indicators.create'))
            ->assertOk()
            ->assertSee('name="ratee_role"', false)
            ->assertSee('name="career_stage"', false);
    }

    public function test_admin_can_view_the_edit_page_for_a_draft_version(): void
    {
        $version = $this->makeDraftVersion();
        $indicator = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.1',
            'description' => 'Applies content knowledge across curriculum areas.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cot-indicators.edit', $version))
            ->assertOk()
            ->assertSee('1.1.1')
            ->assertSee('Applies content knowledge across curriculum areas.')
            ->assertSee('name="ratee_role"', false)
            ->assertSee('name="career_stage"', false);
    }

    public function test_admin_can_view_the_edit_page_for_a_published_version(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create();
        CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '2.1.1',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cot-indicators.edit', $version))
            ->assertOk()
            ->assertSee('2.1.1');
    }

    public function test_admin_can_create_a_draft_version(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.cot-indicators.store'), [
            'school_year' => '2027-2028',
            'label' => 'COT for Highly Proficient Teachers',
        ]);

        $version = CotIndicatorVersion::where('school_year', '2027-2028')->firstOrFail();

        $response->assertRedirect(route('admin.cot-indicators.edit', $version));
        $this->assertSame(CotIndicatorVersion::STATUS_DRAFT, $version->status);
        $this->assertFalse($version->is_default);
    }

    public function test_school_year_must_be_unique(): void
    {
        $this->makeDraftVersion();

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.create'))
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => '2026-2027',
                'label' => 'Duplicate',
            ])
            ->assertSessionHasErrors('school_year');
    }

    public function test_multiple_versions_can_share_a_school_year_for_different_stages(): void
    {
        $this->makeDraftVersion();

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.create'))
            ->post(route('admin.cot-indicators.store'), [
                'school_year' => '2026-2027',
                'label' => 'COT for Master Teacher I-II',
                'ratee_role' => 'teacher',
                'career_stage' => 'master_teacher_i_ii',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cot_indicator_versions', [
            'school_year' => '2026-2027',
            'ratee_role' => 'teacher',
            'career_stage' => 'master_teacher_i_ii',
        ]);

        $this->assertSame(2, CotIndicatorVersion::where('school_year', '2026-2027')->count());
    }

    public function test_admin_can_add_an_indicator_to_a_draft(): void
    {
        $version = $this->makeDraftVersion();

        $this->actingAs($this->admin)->post(
            route('admin.cot-indicators.indicators.store', $version),
            [
                'code' => '1.1.1',
                'domain' => 'Content Knowledge and Pedagogy',
                'description' => 'Applies content knowledge within and across curriculum areas.',
                'is_active' => '1',
            ]
        )->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseHas('cot_indicators', [
            'version_id' => $version->id,
            'code' => '1.1.1',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_duplicate_indicator_code_is_rejected_within_a_version(): void
    {
        $version = $this->makeDraftVersion();
        CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.1',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.edit', $version))
            ->post(
                route('admin.cot-indicators.indicators.store', $version),
                [
                    'code' => '1.1.1',
                    'domain' => 'Content Knowledge and Pedagogy',
                    'description' => 'Duplicated code.',
                ]
            )
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_an_indicator(): void
    {
        $version = $this->makeDraftVersion();
        $indicator = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.1',
        ]);

        $this->actingAs($this->admin)->put(
            route('admin.cot-indicators.indicators.update', $version),
            [
                'id' => $indicator->id,
                'code' => '1.1.2',
                'domain' => 'Learning Environment',
                'description' => 'Updated description.',
                'is_active' => '1',
            ]
        )->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseHas('cot_indicators', [
            'id' => $indicator->id,
            'code' => '1.1.2',
            'domain' => 'Learning Environment',
            'description' => 'Updated description.',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_remove_an_indicator(): void
    {
        $version = $this->makeDraftVersion();
        $indicator = CotIndicator::factory()->create([
            'version_id' => $version->id,
        ]);

        $this->actingAs($this->admin)->delete(
            route('admin.cot-indicators.indicators.destroy', $version),
            ['id' => $indicator->id]
        )->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseMissing('cot_indicators', ['id' => $indicator->id]);
    }

    public function test_admin_can_reorder_indicators(): void
    {
        $version = $this->makeDraftVersion();
        $first = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.1',
            'sort_order' => 0,
        ]);
        $second = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.2',
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin)->post(
            route('admin.cot-indicators.indicators.reorder', $version),
            [
                'reorder_orders' => [
                    ['id' => $first->id, 'sort_order' => 1],
                    ['id' => $second->id, 'sort_order' => 0],
                ],
            ]
        )->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseHas('cot_indicators', ['id' => $first->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('cot_indicators', ['id' => $second->id, 'sort_order' => 0]);
    }

    public function test_admin_can_move_an_indicator_down(): void
    {
        $version = $this->makeDraftVersion();
        $first = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.1',
            'sort_order' => 0,
        ]);
        $second = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.2',
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin)->post(
            route('admin.cot-indicators.indicators.move', [$version, $first]),
            ['direction' => 'down']
        )->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseHas('cot_indicators', ['id' => $first->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('cot_indicators', ['id' => $second->id, 'sort_order' => 0]);
    }

    public function test_move_is_rejected_on_immutable_versions(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create();
        $indicator = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin)
            ->post(
                route('admin.cot-indicators.indicators.move', [$version, $indicator]),
                ['direction' => 'up']
            )
            ->assertRedirect();

        $this->assertSame(0, (int) $indicator->fresh()->sort_order);
    }

    public function test_admin_can_see_editable_rows_on_the_edit_page_for_a_draft(): void
    {
        $version = $this->makeDraftVersion();
        CotIndicator::factory()->count(2)->create(['version_id' => $version->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.cot-indicators.edit', $version))
            ->assertOk()
            ->assertDontSee('template x-for="(item, index) in items"', false)
            ->assertSee('name="direction" value="up"', false)
            ->assertSee('name="direction" value="down"', false)
            ->assertSee('>Save</button>', false);
    }

    public function test_publish_requires_at_least_one_indicator(): void
    {
        $version = $this->makeDraftVersion();

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.index'))
            ->post(route('admin.cot-indicators.publish', $version))
            ->assertRedirect(route('admin.cot-indicators.index'));

        $this->assertSame(CotIndicatorVersion::STATUS_DRAFT, $version->fresh()->status);
    }

    public function test_admin_can_publish_a_version_with_indicators(): void
    {
        $version = $this->makeDraftVersion();
        CotIndicator::factory()->create(['version_id' => $version->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.cot-indicators.publish', $version))
            ->assertRedirect(route('admin.cot-indicators.index'));

        $version->refresh();

        $this->assertSame(CotIndicatorVersion::STATUS_PUBLISHED, $version->status);
        $this->assertTrue($version->is_default);
        $this->assertFalse($version->canEdit());
    }

    public function test_publishing_one_version_unflags_others_as_default(): void
    {
        $previous = CotIndicatorVersion::factory()->published()->create([
            'school_year' => '2025-2026',
        ]);
        $draft = $this->makeDraftVersion();
        CotIndicator::factory()->create(['version_id' => $draft->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.cot-indicators.publish', $draft))
            ->assertRedirect(route('admin.cot-indicators.index'));

        $this->assertFalse($previous->fresh()->is_default);
        $this->assertTrue($draft->fresh()->is_default);
    }

    public function test_published_version_is_immutable(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create();
        $indicator = CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.1',
        ]);

        $this->actingAs($this->admin)->post(
            route('admin.cot-indicators.indicators.store', $version),
            ['code' => '9.9.9', 'domain' => 'D', 'description' => 'Nope.']
        )->assertRedirect();

        $this->actingAs($this->admin)->put(
            route('admin.cot-indicators.indicators.update', $version),
            ['id' => $indicator->id, 'code' => '9.9.9', 'domain' => 'D', 'description' => 'Nope.']
        )->assertRedirect();

        $this->actingAs($this->admin)->delete(
            route('admin.cot-indicators.indicators.destroy', $version),
            ['id' => $indicator->id]
        )->assertRedirect();

        $this->assertDatabaseHas('cot_indicators', [
            'id' => $indicator->id,
            'code' => '1.1.1',
        ]);
    }

    public function test_admin_can_unpublish_a_published_version(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.cot-indicators.unpublish', $version))
            ->assertRedirect(route('admin.cot-indicators.edit', $version));

        $version->refresh();

        $this->assertSame(CotIndicatorVersion::STATUS_DRAFT, $version->status);
        $this->assertFalse($version->is_default);
        $this->assertTrue($version->canEdit());
    }

    public function test_version_referenced_by_observations_cannot_be_deleted(): void
    {
        $version = $this->makeDraftVersion();
        $teacher = Teacher::factory()->create();

        Observation::factory()
            ->forObserver($this->admin)
            ->forObservee($teacher)
            ->create(['cot_indicator_version_id' => $version->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.index'))
            ->delete(route('admin.cot-indicators.destroy', $version))
            ->assertRedirect(route('admin.cot-indicators.index'));

        $this->assertDatabaseHas('cot_indicator_versions', ['id' => $version->id]);
    }

    public function test_admin_can_archive_a_version(): void
    {
        $version = $this->makeDraftVersion();

        $this->actingAs($this->admin)
            ->post(route('admin.cot-indicators.archive', $version))
            ->assertRedirect(route('admin.cot-indicators.index'));

        $this->assertSame(CotIndicatorVersion::STATUS_ARCHIVED, $version->fresh()->status);
        $this->assertFalse($version->fresh()->canEdit());
    }

    public function test_admin_can_delete_an_unused_draft_version(): void
    {
        $version = $this->makeDraftVersion();

        $this->actingAs($this->admin)
            ->delete(route('admin.cot-indicators.destroy', $version))
            ->assertRedirect(route('admin.cot-indicators.index'));

        $this->assertDatabaseMissing('cot_indicator_versions', ['id' => $version->id]);
    }

    public function test_admin_can_add_a_ppst_standard_as_an_indicator(): void
    {
        $version = $this->makeDraftVersion();
        $standard = PpstStandard::factory()->create([
            'indicator_code' => '1.1.2',
            'domain' => 'Content Knowledge and Pedagogy',
            'description' => 'Applies knowledge of content within and across curriculum areas.',
        ]);

        $this->actingAs($this->admin)->post(
            route('admin.cot-indicators.indicators.from-standard', $version),
            ['ppst_standard_id' => $standard->id]
        )->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertDatabaseHas('cot_indicators', [
            'version_id' => $version->id,
            'ppst_standard_id' => $standard->id,
            'code' => '1.1.2',
            'domain' => 'Content Knowledge and Pedagogy',
            'description' => 'Applies knowledge of content within and across curriculum areas.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    public function test_adding_an_existing_ppst_code_to_a_version_is_rejected(): void
    {
        $version = $this->makeDraftVersion();
        $standard = PpstStandard::factory()->create([
            'indicator_code' => '1.1.2',
        ]);

        CotIndicator::factory()->create([
            'version_id' => $version->id,
            'code' => '1.1.2',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.cot-indicators.edit', $version))
            ->post(
                route('admin.cot-indicators.indicators.from-standard', $version),
                ['ppst_standard_id' => $standard->id]
            )
            ->assertRedirect(route('admin.cot-indicators.edit', $version));

        $this->assertSame(1, CotIndicator::where('version_id', $version->id)->count());
    }

    public function test_adding_a_ppst_standard_to_an_immutable_version_is_rejected(): void
    {
        $version = CotIndicatorVersion::factory()->published()->create();
        $standard = PpstStandard::factory()->create([
            'indicator_code' => '1.1.2',
        ]);

        $this->actingAs($this->admin)
            ->post(
                route('admin.cot-indicators.indicators.from-standard', $version),
                ['ppst_standard_id' => $standard->id]
            )
            ->assertRedirect();

        $this->assertDatabaseMissing('cot_indicators', [
            'version_id' => $version->id,
            'code' => '1.1.2',
        ]);
    }

    public function test_edit_page_renders_the_ppst_picker_for_draft_versions(): void
    {
        $version = $this->makeDraftVersion();
        $standard = PpstStandard::factory()->create([
            'indicator_code' => '2.1.1',
            'domain' => 'Learning Environment',
            'description' => 'Manages classroom structure to engage learners.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.cot-indicators.edit', $version))
            ->assertOk()
            ->assertSee('Add from PPST Standards')
            ->assertSee('2.1.1')
            ->assertSee('Manages classroom structure to engage learners.')
            ->assertSee('/indicators/from-standard', false);
    }
}
