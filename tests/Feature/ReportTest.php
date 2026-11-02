<?php

namespace Tests\Feature;

use App\Models\DataAllProduksi;
use App\Models\ToolDefect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_open_on_filtered_chart_view_without_summary_tab_or_overlapping_charts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('PILIH GRAFIK')
            ->assertSee('id="reportChart"', false)
            ->assertSee('Distribusi Data per Departemen')
            ->assertSee("indexAxis: 'x'", false)
            ->assertSee('max: 120', false)
            ->assertSee('value <= 100', false)
            ->assertDontSee('data-horizontal="true"', false)
            ->assertDontSee('horizontal ?')
            ->assertDontSee('Log Aktivitas per Aksi')
            ->assertDontSee('Ringkasan')
            ->assertDontSee('chartPareto');
    }

    public function test_reports_keep_activity_log_tab_available(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('reports.index', ['tab' => 'log']))
            ->assertOk()
            ->assertSee('Log Aktivitas')
            ->assertSee('Belum ada log aktivitas.');
    }

    public function test_each_graph_filter_endpoint_returns_chart_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $endpoints = [
            'reports.api.pareto.fitur',
            'reports.api.pareto.kategori',
            'reports.api.pareto.bulanan',
            'reports.api.pareto.departemen',
        ];

        foreach ($endpoints as $endpoint) {
            $this->actingAs($admin)
                ->getJson(route($endpoint, ['tahun' => 2026]))
                ->assertOk()
                ->assertJsonStructure(['labels', 'values']);
        }
    }

    public function test_each_supported_feature_chart_returns_chart_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $charts = [
            'verifikasi-bulanan',
            'internal-bulanan',
            'eksternal-bulanan',
            'mesin-bulanan',
            'produksi-bulanan',
            'data-ng',
            'stock-bulanan',
            'verifikasi-periode',
            'kalibrasi-periode',
            'verifikasi-qc2',
            'activity-actions',
            'daily-report',
        ];

        foreach ($charts as $chart) {
            $this->actingAs($admin)
                ->getJson(route('reports.api.chart', ['chart' => $chart, 'tahun' => 2026]))
                ->assertOk()
                ->assertJsonStructure(['labels', 'values']);
        }
    }

    public function test_data_ng_and_production_charts_use_their_own_table_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ToolDefect::create([
            'tahun' => 2026,
            'bulan' => 'JAN',
            'division' => 'QA 1',
            'aus' => 3,
            'erorr' => 2,
        ]);
        DataAllProduksi::create([
            'division' => 'QA 1',
            'tool_name' => 'CALIPER',
            'type' => 'MEASURING',
            'size' => '150MM',
            'jan' => 4,
            'feb' => 7,
        ]);

        $this->actingAs($admin)
            ->getJson(route('reports.api.chart', ['chart' => 'data-ng', 'tahun' => 2026]))
            ->assertOk()
            ->assertJsonPath('labels.0', 'AUS')
            ->assertJsonPath('values.0', 3)
            ->assertJsonPath('values.1', 2);

        $this->getJson(route('reports.api.chart', ['chart' => 'produksi-bulanan', 'tahun' => 2026]))
            ->assertOk()
            ->assertJsonPath('labels.0', 'Jan')
            ->assertJsonPath('values.0', 4)
            ->assertJsonPath('values.1', 7);
    }
}
