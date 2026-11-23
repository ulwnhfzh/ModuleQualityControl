<?php

namespace Tests\Feature;

use App\Models\ProductionData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_year_and_orders_divisions_like_the_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            ['ENG', 'ENG TOOL', 2026],
            ['QA 1.2', 'QA12 TOOL', 2026],
            ['QA 1', 'QA1 TOOL', 2026],
            ['QA 1', 'PREVIOUS YEAR TOOL', 2025],
        ] as [$division, $toolName, $year]) {
            ProductionData::create([
                'division' => $division,
                'category' => 'DATA ALAT UKUR ALL PRODUKSI',
                'year' => $year,
                'tool_name' => $toolName,
                'type' => 'TYPE A',
                'size' => '10MM',
            ]);
        }

        $currentYear = $this->actingAs($admin)
            ->get(route('data-all-produksi.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('DATA ALAT UKUR ALL PRODUKSI')
            ->assertSee('value="2014"', false)
            ->assertSee('value="2025"', false)
            ->assertSee('value="2026"', false)
            ->assertDontSee('value="2013"', false)
            ->viewData('data');

        $this->assertSame(
            ['QA 1', 'QA 1.2', 'ENG'],
            $currentYear->getCollection()->pluck('division')->all(),
        );

        $previousYear = $this->get(route('data-all-produksi.index', ['year' => 2025]))
            ->assertOk()
            ->viewData('data');
        $this->assertSame(['PREVIOUS YEAR TOOL'], $previousYear->getCollection()->pluck('tool_name')->all());
    }

    public function test_index_marks_the_entire_row_red_when_a_month_has_ng(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'tool_name' => 'RED MARK TOOL',
            'type' => 'TYPE A',
            'size' => '10MM',
            'mar' => 'NG',
        ]);

        $this->actingAs($admin)
            ->get(route('data-all-produksi.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('RED MARK TOOL')
            ->assertSee('<tr class="bg-rose-100 text-rose-900 hover:bg-rose-200 text-center">', false);
    }

    public function test_index_does_not_show_template_inventory_as_data_for_an_unimported_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2024,
            'is_year_template' => true,
            'tool_name' => 'TEMPLATE ONLY TOOL',
            'type' => 'TYPE A',
            'size' => '10MM',
        ]);
        ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'tool_name' => 'CURRENT YEAR TOOL',
            'type' => 'TYPE A',
            'size' => '10MM',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('data-all-produksi.index', ['year' => 2024]))
            ->assertOk()
            ->assertSee('value="2024"', false)
            ->assertSee('Belum ada data untuk pencarian ini.')
            ->assertDontSee('TEMPLATE ONLY TOOL')
            ->assertDontSee('CURRENT YEAR TOOL');

        $this->assertSame(0, $response->viewData('data')->count());
    }

    public function test_import_form_keeps_real_categories_available_for_a_year_without_imported_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'tool_name' => 'CURRENT YEAR TOOL',
            'type' => 'TYPE A',
            'size' => '10MM',
        ]);
        ProductionData::create([
            'division' => 'QA 1',
            'category' => 'TEMPLATE CATEGORY',
            'year' => 2024,
            'is_year_template' => true,
            'tool_name' => 'TEMPLATE ONLY TOOL',
            'type' => 'TYPE A',
            'size' => '10MM',
        ]);

        $this->actingAs($admin)
            ->get(route('data-all-produksi.import.form', ['year' => 2024]))
            ->assertOk()
            ->assertSee('YEAR data')
            ->assertSee('<option value="DATA ALAT UKUR ALL PRODUKSI">', false)
            ->assertDontSee('TEMPLATE CATEGORY');
    }

    public function test_manual_entry_saves_the_selected_year_and_normalizes_the_production_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('data-all-produksi.store'), [
                'division' => 'QA 1',
                'category' => 'DATA ALAT UKUR ALL PRODUKSI TAHUN 2025',
                'year' => 2025,
                'tool_name' => 'MANUAL YEAR TOOL',
                'type' => 'TYPE A',
                'size' => '10MM',
            ])
            ->assertRedirect(route('data-all-produksi.index', ['division' => 'QA 1', 'year' => 2025]));

        $this->assertDatabaseHas('data_all_produksis', [
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2025,
            'tool_name' => 'MANUAL YEAR TOOL',
        ]);
    }
}