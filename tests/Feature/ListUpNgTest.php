<?php

namespace Tests\Feature;

use App\Imports\ListUpNgImport;
use App\Imports\ProductionDataFolderImport;
use App\Imports\SpreadsheetSheetSelector;
use App\Models\InternalCalibrationListUp;
use App\Models\InternalVerificationSchedule;
use App\Models\ListUpNg;
use App\Models\ProductionData;
use App\Models\ToolDefect;
use App\Models\User;
use App\Services\EquipmentIdentityMatcher;
use App\Services\ListUpNgStatusSynchronizer;
use App\Exports\ListUpNgExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ListUpNgTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_uses_tool_names_and_types_from_production_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ProductionData::create([
            'division' => 'QA 1',
            'tool_name' => 'BALL ANVIL',
            'type' => 'CARBIDE',
            'size' => '2MM',
        ]);
        ProductionData::create([
            'division' => 'QA 2',
            'tool_name' => 'BALL ANVIL',
            'type' => 'STEEL',
            'size' => '3MM',
        ]);

        $this->actingAs($admin)
            ->get(route('list-up-ng.create'))
            ->assertOk()
            ->assertSee('<option value="BALL ANVIL"', false)
            ->assertSee('CARBIDE')
            ->assertSee('STEEL')
            ->assertSee('+ Tambah Type Baru')
            ->assertDontSee('name="stock_qa"', false)
            ->assertDontSee('name="no_spp"', false)
            ->assertDontSee('name="note_order"', false)
            ->assertSee('<option value="PRODUKSI 1"', false)
            ->assertSee('listUpNgToolTypes');
    }

    public function test_new_type_is_added_to_production_data_and_reused_for_the_tool(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ProductionData::create([
            'division' => 'QA 2',
            'category' => 'OPTIONAL',
            'tool_name' => 'HANDRILL',
            'type' => 'ELECTRIC',
            'size' => '10 mm',
            'number_seri' => 'SER-1',
        ]);

        $this->actingAs($admin)
            ->post(route('list-up-ng.store'), [
                'tool_name' => 'HANDRILL',
                'type' => '__add_new_type__',
                'new_type' => 'HYDRAULIC',
                'size' => '18 mm',
                'number_reg' => 'HD-001',
                'ng_date' => '2026-11-16',
                'division' => 'QA 1',
                'category_ng' => 'VERIFIKASI',
                'note' => 'OVER STD',
            ])
            ->assertRedirect(route('list-up-ng.index', ['tahun' => 2026]))
            ->assertSessionHas('success');

        $newType = ProductionData::where('tool_name', 'HANDRILL')
            ->where('type', 'HYDRAULIC')
            ->firstOrFail();
        $this->assertSame('QA 1', $newType->division);
        $this->assertSame('OPTIONAL', $newType->category);
        $this->assertSame('NG', $newType->nov);

        $this->get(route('list-up-ng.create'))
            ->assertOk()
            ->assertSee('HYDRAULIC');
    }

    public function test_edit_form_keeps_a_legacy_tool_and_type_not_in_production_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $listUpNg = ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'LEGACY TOOL',
            'type' => 'LEGACY TYPE',
            'ng_date' => '2026-10-15',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
        ]);

        $this->actingAs($admin)
            ->get(route('list-up-ng.edit', $listUpNg))
            ->assertOk()
            ->assertSee('<option value="LEGACY TOOL"', false)
            ->assertSee('<option value="LEGACY TYPE"', false);
    }

    public function test_index_filters_list_up_ng_by_year_and_shows_an_empty_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'ONLY 2026 TOOL',
            'ng_date' => '2026-03-15',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
        ]);

        $this->actingAs($admin)
            ->get(route('list-up-ng.index', ['tahun' => 2024]))
            ->assertOk()
            ->assertSee('value="2014"', false)
            ->assertSee('YEAR 2024')
            ->assertSee('value="2026"', false)
            ->assertDontSee('value="2013"', false)
            ->assertSee('Belum ada data List Up NG untuk YEAR')
            ->assertDontSee('ONLY 2026 TOOL')
            ->assertViewHas('ngs', fn ($ngs) => $ngs->isEmpty());

        $this->get(route('list-up-ng.index', ['tahun' => 2026]))
            ->assertOk()
            ->assertSee('ONLY 2026 TOOL')
            ->assertDontSee('Stock QA')
            ->assertDontSee('No SPP')
            ->assertViewHas('ngs', fn ($ngs) => $ngs->count() === 1);
    }

    public function test_import_ignores_split_stock_and_order_columns_after_note(): void
    {
        $productionRow = ProductionData::create([
            'division' => 'QA 1',
            'category' => 'OPTIONAL',
            'tool_name' => 'GAUGE A',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'number_seri' => 'SER-100',
            'number_reg' => 'REG-100',
        ]);
        $workbook = new Spreadsheet;
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle('LIST UP NG');
        $sheet->fromArray([
            ['NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG.', 'PART NAME', 'PART NUMBER', 'NG DATE', 'division', 'CATEGORY NG', 'NOTE', 'STOCK', null, 'STATUS ORDER', null, null],
            [null, null, null, null, null, null, null, null, null, null, null, null, 'QA', 'PROD', 'NO SPP', 'OPEN/CLOSED', 'NOTE'],
            [1, 'GAUGE A', 'MICROMETER', '0-25MM', 'SER-100', 'REG-100', 'PART A', 'P-100', '2026-03-15', 'QA 1', 'KALIBRASI', 'Instrument damaged', 4, 5, 'SPP-100', 'OPEN', 'Order follow-up'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'list-up-ng-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            $sheetImports = [];
            $selected = SpreadsheetSheetSelector::forHeaders(
                $path,
                [
                    ['tool_name', 'nama_alat'],
                    ['ng_date', 'tanggal_ng'],
                    ['division', 'division'],
                    ['category_ng', 'kategori_ng'],
                ],
                function (int $rowHeader) use (&$sheetImports): ListUpNgImport {
                    $import = new ListUpNgImport($rowHeader, 2026);
                    $sheetImports[] = $import;

                    return $import;
                },
            );
            Excel::import($selected, $path);
        } finally {
            @unlink($path);
        }

        $ng = ListUpNg::firstOrFail();
        $this->assertSame(0, $ng->stock_qa);
        $this->assertSame(0, $ng->stock_prod);
        $this->assertNull($ng->no_spp);
        $this->assertSame('CLOSED', $ng->status_order);
        $this->assertNull($ng->note_order);
        $this->assertSame('Instrument damaged', $ng->note);
        $this->assertSame(1, array_sum(array_map(
            fn (ListUpNgImport $import): int => $import->getImportedCount(),
            $sheetImports,
        )));
        $this->assertSame('NG', $productionRow->fresh()->mar);
    }

    public function test_import_rejects_rows_outside_the_selected_year(): void
    {
        $workbook = new Spreadsheet;
        $workbook->getActiveSheet()->fromArray([
            ['TOOL NAME', 'NG DATE', 'division', 'CATEGORY NG'],
            ['GAUGE A', '2025-03-15', 'QA 1', 'KALIBRASI'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'list-up-ng-year-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            $selected = SpreadsheetSheetSelector::forHeaders(
                $path,
                [
                    ['tool_name', 'nama_alat'],
                    ['ng_date', 'tanggal_ng'],
                    ['division', 'division'],
                    ['category_ng', 'kategori_ng'],
                ],
                fn (int $rowHeader): ListUpNgImport => new ListUpNgImport($rowHeader, 2026),
            );

            $this->expectException(\RuntimeException::class);
            Excel::import($selected, $path);
        } finally {
            @unlink($path);
        }
    }

    public function test_list_up_ng_changes_update_the_monthly_master_recap(): void
    {
        $ng = ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'GAUGE A',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'ng_date' => '2026-03-15',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD, RUSAK, OVER STD',
        ]);

        $recap = ToolDefect::where('tahun', 2026)
            ->where('bulan', 'MAR')
            ->where('division', 'QA 1')
            ->firstOrFail();
        $this->assertSame(1, (int) $recap->over_std);
        $this->assertSame(1, (int) $recap->rusak);
        $this->assertSame(0, (int) $recap->lain_lain);

        $ng->update(['note' => 'CATATAN DI LUAR KATEGORI REKAP']);
        $recap = ToolDefect::where('tahun', 2026)
            ->where('bulan', 'MAR')
            ->where('division', 'QA 1')
            ->firstOrFail();
        $this->assertSame(0, (int) $recap->over_std);
        $this->assertSame(0, (int) $recap->rusak);
        $this->assertSame(1, (int) $recap->lain_lain);

        $ng->delete();
        $recap = ToolDefect::where('tahun', 2026)
            ->where('bulan', 'MAR')
            ->where('division', 'QA 1')
            ->firstOrFail();
        $this->assertSame(0, (int) $recap->lain_lain);
    }

    public function test_list_up_ng_updates_only_the_matching_production_year(): void
    {
        $previousYear = ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2025,
            'tool_name' => 'YEAR-SCOPED GAUGE',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'number_reg' => 'REG-YEAR',
        ]);
        $currentYear = ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'tool_name' => 'YEAR-SCOPED GAUGE',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'number_reg' => 'REG-YEAR',
        ]);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'YEAR-SCOPED GAUGE',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'number_reg' => 'REG-YEAR',
            'ng_date' => '2026-11-16',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD',
        ]);

        $this->assertNull($previousYear->fresh()->nov);
        $this->assertSame('NG', $currentYear->fresh()->nov);
    }

    public function test_canonical_production_department_resolves_duplicate_tool_identity(): void
    {
        $productionOne = ProductionData::create([
            'division' => 'P1',
            'year' => 2026,
            'tool_name' => 'SAME GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'SAME-REG',
        ]);
        $productionTwo = ProductionData::create([
            'division' => 'P2',
            'year' => 2026,
            'tool_name' => 'SAME GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'SAME-REG',
        ]);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'SAME GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'SAME-REG',
            'ng_date' => '2026-09-10',
            'division' => 'PRODUKSI 1',
            'category_ng' => 'VERIFIKASI',
        ]);

        $this->assertSame('NG', $productionOne->fresh()->sep);
        $this->assertNull($productionTwo->fresh()->sep);
    }

    public function test_update_and_delete_reconcile_production_statuses_and_preserve_other_active_ng_rows(): void
    {
        $productionRow = ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'tool_name' => 'RECONCILE GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'REC-1',
            'mar' => 'OK',
            'is_year_template' => true,
        ]);
        $attributes = [
            'tahun' => 2026,
            'tool_name' => 'RECONCILE GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'REC-1',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD',
        ];
        $firstNg = ListUpNg::create($attributes + ['ng_date' => '2026-03-10']);
        $secondNg = ListUpNg::create($attributes + ['ng_date' => '2026-03-20']);

        $this->assertSame('NG', $productionRow->fresh()->mar);
        $this->assertFalse($productionRow->fresh()->is_year_template);

        $firstNg->update(['ng_date' => '2026-04-15']);
        $this->assertSame('NG', $productionRow->fresh()->mar);
        $this->assertSame('NG', $productionRow->fresh()->apr);

        $secondNg->delete();
        $this->assertSame('OK', $productionRow->fresh()->mar);
        $this->assertSame('NG', $productionRow->fresh()->apr);
        $this->assertFalse($productionRow->fresh()->is_year_template);

        $firstNg->delete();
        $this->assertSame('OK', $productionRow->fresh()->mar);
        $this->assertNull($productionRow->fresh()->apr);
        $this->assertTrue($productionRow->fresh()->is_year_template);
        $this->assertDatabaseCount('list_up_ng_status_snapshots', 0);
    }

    public function test_target_and_yearly_sync_bound_queries_with_unrelated_ng_rows(): void
    {
        $productionRow = ProductionData::create([
            'division' => 'QA 1',
            'year' => 2026,
            'tool_name' => 'TARGET GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'TARGET-REG',
        ]);
        $now = now();
        $rows = [];
        for ($index = 0; $index < 40; $index++) {
            $rows[] = [
                'tahun' => 2026,
                'tool_name' => "UNRELATED GAUGE {$index}",
                'type' => 'DIGITAL',
                'size' => '0-25MM',
                'number_reg' => null,
                'ng_date' => '2026-03-10',
                'division' => 'QA 1',
                'category_ng' => 'VERIFIKASI',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        $rows[] = [
            'tahun' => 2026,
            'tool_name' => 'TARGET GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'TARGET-REG',
            'ng_date' => '2026-03-10',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        DB::table('list_up_ngs')->insert($rows);

        DB::flushQueryLog();
        DB::enableQueryLog();
        app(ListUpNgStatusSynchronizer::class)->syncTarget('production', $productionRow->id);
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame('NG', $productionRow->fresh()->mar);
        $this->assertLessThan(30, $queryCount);

        DB::flushQueryLog();
        DB::enableQueryLog();
        app(ListUpNgStatusSynchronizer::class)->syncYear(2026);
        $yearQueryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(40, $yearQueryCount);
    }

    public function test_deleting_list_up_ng_restores_the_original_calibration_status(): void
    {
        $calibration = InternalCalibrationListUp::create([
            'tahun' => 2026,
            'company' => 'HAMATETSU',
            'tool_name' => 'RESTORE CALIPER',
            'tool_type' => 'DIGITAL',
            'specification' => '0-150MM',
            'serial_number' => 'RESTORE-SER',
            'registration_number' => 'RESTORE-REG',
            'shop' => 'QA 1',
            'calibration_date' => '2026-05-10',
            'jgm' => null,
        ]);
        $ng = ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'RESTORE CALIPER',
            'type' => 'DIGITAL',
            'size' => '0-150MM',
            'number_seri' => 'RESTORE-SER',
            'number_reg' => 'RESTORE-REG',
            'ng_date' => '2026-05-20',
            'division' => 'QA 1',
            'category_ng' => 'KALIBRASI',
        ]);

        $this->assertSame('NG', $calibration->fresh()->jgm);

        $ng->delete();

        $this->assertNull($calibration->fresh()->jgm);
        $this->assertDatabaseCount('list_up_ng_status_snapshots', 0);
    }

    public function test_yearly_resynchronization_clears_a_status_when_schedule_period_moves_away_from_ng_month(): void
    {
        $schedule = InternalVerificationSchedule::create([
            'tahun' => 2026,
            'company' => 'HAMATETSU',
            'tool_name' => 'MOVE VERIFICATION',
            'tool_type' => 'DIGITAL',
            'specification' => '0-25MM',
            'serial_number' => 'MOVE-SER',
            'registration_number' => 'MOVE-REG',
            'shop' => 'QA 1',
            'period1_verification_date' => '2026-03-10',
        ]);
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'MOVE VERIFICATION',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_seri' => 'MOVE-SER',
            'number_reg' => 'MOVE-REG',
            'ng_date' => '2026-03-20',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
        ]);

        $this->assertSame('NG', $schedule->fresh()->period1_jgm);

        $schedule->update(['period1_verification_date' => '2026-04-10']);
        app(ListUpNgStatusSynchronizer::class)->syncYear(2026);

        $this->assertNull($schedule->fresh()->period1_jgm);
        $this->assertDatabaseCount('list_up_ng_status_snapshots', 0);
    }

    public function test_list_up_ng_export_stops_at_the_main_note_column(): void
    {
        $ng = ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'EXPORT GAUGE',
            'ng_date' => '2026-05-10',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
        ]);
        $export = new ListUpNgExport(2026);
        $mappedRow = $export->map($ng);

        $this->assertCount(12, $export->headings());
        $this->assertNotContains('NO SPP', $export->headings());
        $this->assertCount(12, $mappedRow);
    }

    public function test_replacing_yearly_import_restores_status_for_removed_ng_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $productionRow = ProductionData::create([
            'division' => 'QA 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'year' => 2026,
            'tool_name' => 'REMOVED FROM WORKBOOK',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'IMPORT-REG',
            'nov' => 'OK',
            'is_year_template' => true,
        ]);
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'REMOVED FROM WORKBOOK',
            'type' => 'DIGITAL',
            'size' => '0-25MM',
            'number_reg' => 'IMPORT-REG',
            'ng_date' => '2026-11-10',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD',
        ]);
        $workbook = new Spreadsheet;
        $workbook->getActiveSheet()->fromArray([
            ['TOOL NAME', 'NG DATE', 'division', 'CATEGORY NG'],
            ['OTHER GAUGE', '2026-11-15', 'QA 1', 'VERIFIKASI'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'list-up-ng-replace-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            $upload = UploadedFile::fake()->createWithContent('replacement.xlsx', file_get_contents($path));
            $this->actingAs($admin)
                ->post(route('list-up-ng.import.store'), ['tahun' => 2026, 'file' => $upload])
                ->assertRedirect(route('list-up-ng.index', ['tahun' => 2026]))
                ->assertSessionHas('success');
        } finally {
            @unlink($path);
        }

        $this->assertDatabaseMissing('list_up_ngs', ['tool_name' => 'REMOVED FROM WORKBOOK']);
        $this->assertDatabaseHas('list_up_ngs', ['tool_name' => 'OTHER GAUGE']);
        $this->assertSame('OK', $productionRow->fresh()->nov);
        $this->assertTrue($productionRow->fresh()->is_year_template);
        $this->assertDatabaseCount('list_up_ng_status_snapshots', 0);
    }

    public function test_unique_registration_match_resolves_division_and_marks_production_even_if_serial_differs(): void
    {
        $productionRow = ProductionData::create([
            'division' => 'PPIC 1',
            'category' => 'DATA ALAT UKUR ALL PRODUKSI TAHUN 2026',
            'tool_name' => 'JIG GAUGE',
            'type' => 'SPLINE RING',
            'size' => '+0.01 Ø20.623 0',
            'number_reg' => 'J-495',
        ]);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'JIG GAUGE',
            'type' => 'SPLINE RING',
            'size' => '+0.01 Ø20.623 0',
            'number_seri' => 'FINAL INSPECTION',
            'number_reg' => 'J-495',
            'ng_date' => '2026-07-14',
            'division' => 'PPIC',
            'category_ng' => 'VERIFIKASI',
            'note' => 'MIN STD',
        ]);

        $this->assertSame('NG', $productionRow->fresh()->jul);

        $recap = ToolDefect::where('tahun', 2026)
            ->where('bulan', 'JUL')
            ->where('division', 'PPIC 1')
            ->firstOrFail();
        $this->assertSame(1, (int) $recap->min_std);
        $this->assertDatabaseHas('tool_defect_details', [
            'tahun' => 2026,
            'tool_name' => 'JIG GAUGE',
            'division' => 'PPIC 1',
            'tanggal' => '2026-07-14',
        ]);
    }

    public function test_registration_mismatch_does_not_match_similar_tools_from_other_divisions(): void
    {
        foreach ([
            ['QA 1', 'J-493'],
            ['PPIC 1', 'J-495'],
            ['P1', 'J-490'],
        ] as [$division, $registration]) {
            ProductionData::create([
                'division' => $division,
                'tool_name' => 'JIG GAUGE',
                'type' => 'SPLINE RING',
                'size' => '+0.01 Ø20.623 0',
                'number_reg' => $registration,
            ]);
        }

        $ng = new ListUpNg([
            'tool_name' => 'JIG GAUGE',
            'type' => 'SPLINE RING',
            'size' => '+0.01 Ø20.623 0',
            'number_seri' => 'FINAL INSPECTION',
            'number_reg' => 'J-491',
            'division' => 'PPIC',
        ]);

        $this->assertTrue(app(EquipmentIdentityMatcher::class)->productionMatches($ng)->isEmpty());
    }

    public function test_folder_reimport_includes_punctuated_division_and_restores_existing_ng_mark(): void
    {
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'THREAD RING GAUGE',
            'type' => 'STEEL',
            'size' => 'M16X1-6g GR (GO)',
            'number_seri' => '2407663-1',
            'number_reg' => 'TR-61',
            'ng_date' => '2026-07-14',
            'division' => 'QA INSP',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD',
        ]);

        $folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'production-import-2026-' . uniqid();
        mkdir($folder);

        $workbook = new Spreadsheet;
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle('QA INSP.');
        $sheet->fromArray([
            ['TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            ['THREAD RING GAUGE', 'STEEL', 'M16X1-6g GR (GO)', '2407663-1', 'TR-61', null, null, null, null, null, null, null, null, null, null, null, null],
        ]);
        $file = $folder . DIRECTORY_SEPARATOR . 'THREAD RING GAUGE.xlsx';
        (new Xlsx($workbook))->save($file);
        $workbook->disconnectWorksheets();

        try {
            $summary = app(ProductionDataFolderImport::class)->import($folder);
        } finally {
            @unlink($file);
            @rmdir($folder);
        }

        $this->assertSame(1, $summary['records_processed']);
        $productionRow = ProductionData::where('number_reg', 'TR-61')->firstOrFail();
        $this->assertSame('QA INSP', $productionRow->division);
        $this->assertSame('NG', $productionRow->jul);
    }
}