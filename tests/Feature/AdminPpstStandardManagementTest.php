<?php

namespace Tests\Feature;

use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\PpstStandard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPpstStandardManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)
            ->get(route('admin.ppst-standards.index'))
            ->assertForbidden();

        $this->actingAs($supervisor)
            ->post(route('admin.ppst-standards.store'), [
                'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                'indicator_code' => '1.1.1',
                'description' => 'Nope.',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_list_standards_grouped_by_domain(): void
    {
        $first = PpstStandard::factory()->create([
            'domain' => 'Domain 1: Content Knowledge and Pedagogy',
            'strand' => '1.1',
            'indicator_code' => '1.1.2',
            'description' => 'Apply knowledge of content within and across curriculum teaching areas',
        ]);
        $second = PpstStandard::factory()->create([
            'domain' => 'Domain 2: Learning Environment',
            'strand' => '2.1',
            'indicator_code' => '2.1.1',
            'description' => 'Knowledge of safe and secure learning environments',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.ppst-standards.index'))
            ->assertOk();

        $response->assertSee($first->domain)
            ->assertSee($first->indicator_code)
            ->assertSee($first->description)
            ->assertSee($second->domain)
            ->assertSee($second->indicator_code)
            ->assertSee($second->description);
    }

    public function test_admin_can_view_the_create_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.ppst-standards.create'))
            ->assertOk()
            ->assertSee('name="indicator_code"', false)
            ->assertSee('name="domain"', false);
    }

    public function test_admin_can_create_a_standard_with_derived_strand(): void
    {
        $this->actingAs($this->admin)->post(route('admin.ppst-standards.store'), [
            'domain' => 'Domain 1: Content Knowledge and Pedagogy',
            'indicator_code' => '1.1.2',
            'description' => 'Apply knowledge of content within and across curriculum teaching areas',
            'sort_order' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('admin.ppst-standards.index'));

        $this->assertDatabaseHas('ppst_standards', [
            'domain' => 'Domain 1: Content Knowledge and Pedagogy',
            'strand' => '1.1',
            'indicator_code' => '1.1.2',
            'description' => 'Apply knowledge of content within and across curriculum teaching areas',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }

    public function test_duplicate_indicator_code_is_rejected(): void
    {
        PpstStandard::factory()->create(['indicator_code' => '1.1.1']);

        $this->actingAs($this->admin)
            ->from(route('admin.ppst-standards.create'))
            ->post(route('admin.ppst-standards.store'), [
                'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                'indicator_code' => '1.1.1',
                'description' => 'Duplicate code.',
            ])
            ->assertSessionHasErrors('indicator_code');
    }

    public function test_malformed_indicator_code_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.ppst-standards.create'))
            ->post(route('admin.ppst-standards.store'), [
                'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                'indicator_code' => 'abc',
                'description' => 'Malformed code.',
            ])
            ->assertSessionHasErrors('indicator_code');

        $this->assertDatabaseMissing('ppst_standards', ['indicator_code' => 'abc']);
    }

    public function test_admin_can_view_the_edit_page(): void
    {
        $standard = PpstStandard::factory()->create([
            'indicator_code' => '1.1.2',
            'description' => 'Apply knowledge of content across curriculum areas.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.ppst-standards.edit', $standard))
            ->assertOk()
            ->assertSee('1.1.2')
            ->assertSee('Apply knowledge of content across curriculum areas.');
    }

    public function test_admin_can_update_a_standard(): void
    {
        $standard = PpstStandard::factory()->create([
            'domain' => 'Domain 1: Content Knowledge and Pedagogy',
            'indicator_code' => '1.1.2',
        ]);

        $this->actingAs($this->admin)->put(
            route('admin.ppst-standards.update', $standard),
            [
                'domain' => 'Domain 2: Learning Environment',
                'indicator_code' => '2.1.2',
                'description' => 'Establish safe and secure learning environments.',
                'sort_order' => 5,
                'is_active' => '1',
            ]
        )->assertRedirect(route('admin.ppst-standards.index'));

        $this->assertDatabaseHas('ppst_standards', [
            'id' => $standard->id,
            'domain' => 'Domain 2: Learning Environment',
            'strand' => '2.1',
            'indicator_code' => '2.1.2',
            'description' => 'Establish safe and secure learning environments.',
            'sort_order' => 5,
        ]);
    }

    public function test_admin_can_toggle_active_state(): void
    {
        $standard = PpstStandard::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)->post(
            route('admin.ppst-standards.toggle-active', $standard),
            ['is_active' => '0']
        )->assertRedirect(route('admin.ppst-standards.index'));

        $this->assertFalse($standard->fresh()->is_active);

        $this->actingAs($this->admin)->post(
            route('admin.ppst-standards.toggle-active', $standard),
            ['is_active' => '1']
        )->assertRedirect(route('admin.ppst-standards.index'));

        $this->assertTrue($standard->fresh()->is_active);
    }

    public function test_referenced_standard_cannot_be_deleted(): void
    {
        $standard = PpstStandard::factory()->create(['indicator_code' => '1.1.2']);
        $version = CotIndicatorVersion::factory()->create();

        CotIndicator::factory()->create([
            'version_id' => $version->id,
            'ppst_standard_id' => $standard->id,
            'code' => '1.1.2',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.ppst-standards.index'))
            ->delete(route('admin.ppst-standards.destroy', $standard))
            ->assertRedirect(route('admin.ppst-standards.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('ppst_standards', ['id' => $standard->id]);
    }

    public function test_admin_can_delete_an_unreferenced_standard(): void
    {
        $standard = PpstStandard::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.ppst-standards.destroy', $standard))
            ->assertRedirect(route('admin.ppst-standards.index'));

        $this->assertDatabaseMissing('ppst_standards', ['id' => $standard->id]);
    }
}
