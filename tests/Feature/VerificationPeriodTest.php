<?php

namespace Tests\Feature;

use App\Models\InternalCalibrationListUp;
use App\Models\ExternalCalibrationListUp;
use App\Models\ProductionData;
use App\Models\User;
use App\Models\InternalVerificationSchedule;
use App\Models\Qc2Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_pdf_exports_download_pdf_content_for_each_schedule_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        InternalVerificationSchedule::create([
            'tahun' => 2026,
            'tool_name' => 'VERIFICATION TOOL',
            'tool_type' => 'MEASURING',
            'period1_verification_date' => '2026-01-15',
        ]);
        InternalCalibrationListUp::create([
            'tahun' => 2026,
            'tool_name' => 'CALIBRATION TOOL',
            'tool_type' => 'MEASURING',
            'calibration_date' => '2026-02-15',
        ]);
        Qc2Verification::create([
            'tahun' => 2026,
            'tool_name' => 'QC2 TOOL',
            'tool_type' => 'MEASURING',
            'verification_date' => '2026-03-15',
        ]);

        $exports = [
            ['verifikasi.periode.export.pdf', 'list-up-verifikasi-2026.pdf'],
            ['verifikasi.periode.kalibrasi.export.pdf', 'list-up-kalibrasi-internal-2026.pdf'],
            ['verifikasi.qc2.export.pdf', 'list-up-qc2-2026.pdf'],
        ];

        foreach ($exports as [$route, $filename]) {
            $response = $this->actingAs($admin)->get(route($route, ['tahun' => 2026]));

            $response
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf')
                ->assertDownload($filename);
            $this->assertStringStartsWith('%PDF-', $response->getContent());
        }
    }

    public function test_quality_control_list_up_pages_search_by_tool_and_shop_and_paginate_100_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $now = now();
        $productionRows = [];
        for ($index = 1; $index <= 101; $index++) {
            $productionRows[] = [
                'division' => 'QA 1',
                'category' => 'DATA ALAT UKUR ALL PRODUKSI',
                'year' => 2026,
                'is_year_template' => false,
                'tool_name' => sprintf('PAGINATED TOOL %03d', $index),
                'type' => 'TEST',
                'size' => 'TEST',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $productionRows[] = [
            'division' => 'P5',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'is_year_template' => false,
            'tool_name' => 'PAGINATED TOOL 102',
            'type' => 'TEST',
            'size' => 'TEST',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $productionRows[] = [
            'division' => 'P8',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'is_year_template' => false,
            'tool_name' => 'P8 MASTER TOOL',
            'type' => 'TEST',
            'size' => 'TEST',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        ProductionData::insert($productionRows);

        $pages = [
            [InternalVerificationSchedule::class, 'verifikasi.periode.index', ['jenis' => 'verifikasi', 'company' => 'HAMATETSU']],
            [InternalCalibrationListUp::class, 'verifikasi.periode.index', ['jenis' => 'kalibrasi', 'company' => 'HAMATETSU']],
            [Qc2Verification::class, 'verifikasi.periode.index', ['jenis' => 'verifikasi_qc2', 'company' => 'HAMATETSU']],
            [ExternalCalibrationListUp::class, 'external-list-up.index', []],
        ];

        foreach ($pages as [$model, $route, $routeFilters]) {
            $rows = [];
            for ($index = 1; $index <= 101; $index++) {
                $row = [
                    'tahun' => 2026,
                    'tool_name' => sprintf('PAGINATED TOOL %03d', $index),
                    'shop' => 'QA 1',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($model === InternalCalibrationListUp::class) {
                    $row['tool_type'] = 'TEST';
                }
                $rows[] = $row;
            }
            $rows[] = [
                'tahun' => 2026,
                'tool_name' => 'UNRELATED TOOL',
                'shop' => 'QA 1',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $rows[] = [
                'tahun' => 2026,
                'tool_name' => 'PAGINATED TOOL 102',
                'shop' => 'P5',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($model === InternalCalibrationListUp::class) {
                $rows[101]['tool_type'] = 'TEST';
                $rows[102]['tool_type'] = 'TEST';
            }
            $model::insert($rows);

            $filters = array_merge($routeFilters, [
                'tahun' => 2026,
                'shop' => 'QA 1',
            ]);
            $this->actingAs($admin)
                ->get(route($route, $filters))
                ->assertOk()
                ->assertViewHas('schedules', fn ($schedules) => $schedules->count() === 100 && $schedules->hasMorePages())
                ->assertSee('<option value="PAGINATED TOOL 101"', false)
                ->assertDontSee('<option value="PAGINATED TOOL 102"', false)
                ->assertSee('<option value="P8"', false)
                ->assertDontSee('<option value="P8 MASTER TOOL"', false)
                ->assertSee('page=2', false);

            $secondPage = $this->get(route($route, array_merge($filters, ['page' => 2])))
                ->assertOk();
            $this->assertSame(2, $secondPage->viewData('schedules')->count(), $model);
            $this->assertFalse($secondPage->viewData('schedules')->hasMorePages(), $model);
            $secondPage
                ->assertSee('PAGINATED TOOL 101')
                ->assertViewHas('toolName', '')
                ->assertViewHas('shop', 'QA 1');

            $this->get(route($route, array_merge($filters, ['tool_name' => 'PAGINATED TOOL 101'])))
                ->assertOk()
                ->assertViewHas('schedules', fn ($schedules) => $schedules->count() === 1)
                ->assertSee('PAGINATED TOOL 101')
                ->assertViewHas('toolName', 'PAGINATED TOOL 101');
        }
    }

    public function test_quality_control_create_forms_use_tool_names_and_types_from_data_all_produksi(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach ([
            [2026, 'MASTER TOOL', 'MASTER TYPE'],
            [2025, 'PREVIOUS YEAR TOOL', 'OLD TYPE'],
            [2026, 'P8 MASTER TOOL', 'P8 TYPE'],
        ] as [$year, $toolName, $type]) {
            ProductionData::create([
                'division' => $toolName === 'P8 MASTER TOOL' ? 'P8' : 'QA 1',
                'category' => 'DATA ALAT UKUR ALL PRODUKSI',
                'year' => $year,
                'tool_name' => $toolName,
                'type' => $type,
                'size' => 'TEST',
            ]);
        }

        $forms = [
            ['verifikasi.periode.create', ['tahun' => 2026, 'company' => 'HAMATETSU'], true],
            ['verifikasi.periode.kalibrasi.create', ['tahun' => 2026, 'company' => 'HAMATETSU'], true],
            ['verifikasi.qc2.create', ['tahun' => 2026, 'company' => 'HAMATETSU'], true],
            ['external-list-up.create', ['tahun' => 2026], false],
        ];

        foreach ($forms as [$route, $parameters, $hasTypeDropdown]) {
            $response = $this->actingAs($admin)
                ->get(route($route, $parameters))
                ->assertOk()
                ->assertSee('<option value="MASTER TOOL"', false)
                ->assertDontSee('<option value="PREVIOUS YEAR TOOL"', false);

            if ($hasTypeDropdown) {
                $response->assertSee('MASTER TYPE')
                    ->assertSee('<option value="P8"', false);
            }
        }
    }

    public function test_calibration_period_two_data_is_saved_on_create_and_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $calibrationData = [
            'tahun' => '2026',
            'company' => 'HAMATETSU',
            'tool_name' => 'CALIPER',
            'tool_type' => 'MEASURING',
            'period2_calibration_date' => '2026-02-01',
            'period2_next_calibration_date' => '2026-08-01',
            'period2_jgm' => 'PASS',
        ];

        $this->actingAs($admin)
            ->post(route('verifikasi.periode.kalibrasi.store'), $calibrationData)
            ->assertRedirect(route('verifikasi.periode.index', ['tahun' => '2026', 'jenis' => 'kalibrasi', 'company' => 'HAMATETSU']));

        $schedule = InternalCalibrationListUp::query()->firstOrFail();
        $this->assertSame('2026-02-01', $schedule->period2_calibration_date->format('Y-m-d'));
        $this->assertSame('2026-08-01', $schedule->period2_next_calibration_date->format('Y-m-d'));
        $this->assertSame('PASS', $schedule->period2_jgm);

        $updatedData = array_merge($calibrationData, [
            'period2_calibration_date' => '2026-03-01',
            'period2_next_calibration_date' => '2026-09-01',
            'period2_jgm' => 'RECHECK',
        ]);

        $this->put(route('verifikasi.periode.kalibrasi.update', $schedule), $updatedData)
            ->assertRedirect(route('verifikasi.periode.index', ['tahun' => '2026', 'jenis' => 'kalibrasi', 'company' => 'HAMATETSU']));

        $schedule->refresh();
        $this->assertSame('2026-03-01', $schedule->period2_calibration_date->format('Y-m-d'));
        $this->assertSame('2026-09-01', $schedule->period2_next_calibration_date->format('Y-m-d'));
        $this->assertSame('RECHECK', $schedule->period2_jgm);
    }

    public function test_calibration_period_two_next_date_cannot_precede_calibration_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('verifikasi.periode.kalibrasi.create'))
            ->post(route('verifikasi.periode.kalibrasi.store'), [
                'tahun' => '2026',
                'tool_name' => 'CALIPER',
                'tool_type' => 'MEASURING',
                'period2_calibration_date' => '2026-08-01',
                'period2_next_calibration_date' => '2026-02-01',
            ]);

        $response->assertSessionHasErrors('period2_next_calibration_date');
        $this->assertDatabaseCount('kalibrasi_internal_list_ups', 0);
    }
}
