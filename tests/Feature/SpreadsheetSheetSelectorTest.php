<?php

namespace Tests\Feature;

use App\Imports\InternalImport;
use App\Imports\ProductionDataImport;
use App\Imports\SpreadsheetImportHeaders;
use App\Imports\SpreadsheetSheetSelector;
use App\Models\Internal;
use App\Models\ProductionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;

class SpreadsheetSheetSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_every_matching_sheet_and_ignores_other_feature_sheets(): void
    {
        $workbook = new Spreadsheet;
        $external = $workbook->getActiveSheet();
        $external->setTitle('Kalibrasi External');
        $external->fromArray([
            ['NO', 'TOOL NAME', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'QTY SET'],
            [1, 'EXTERNAL ONLY', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1],
        ]);

        foreach ([
            ['INTERNAL ONE', 'Kalibrasi Internal'],
            ['INTERNAL TWO', 'Kalibrasi Internal 2'],
        ] as [$toolName, $title]) {
            $sheet = $workbook->createSheet();
            $sheet->setTitle($title);
            $sheet->fromArray([
                ['NO', 'TOOL NAME', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'QTY'],
                [1, $toolName, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1],
            ]);
        }

        $path = tempnam(sys_get_temp_dir(), 'feature-import-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            $selected = SpreadsheetSheetSelector::forHeaders(
                $path,
                SpreadsheetImportHeaders::INTERNAL_MONTHLY,
                fn (int $rowHeader) => new InternalImport('2026', 'HAMATETSU', $rowHeader),
            );
            Excel::import($selected, $path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(
            ['INTERNAL ONE', 'INTERNAL TWO'],
            Internal::query()->orderBy('tool_name')->pluck('tool_name')->all(),
        );
        $this->assertDatabaseMissing('internals', ['tool_name' => 'EXTERNAL ONLY']);
    }

    public function test_it_rejects_workbooks_without_a_matching_feature_table(): void
    {
        $workbook = new Spreadsheet;
        $workbook->getActiveSheet()->fromArray([
            ['TOOL NAME', 'MACHINE MODEL', 'NEXT DATE'],
            ['MACHINE ONLY', 'MODEL A', '2026-10-10'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'unmatched-import-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            $this->expectException(RuntimeException::class);
            SpreadsheetSheetSelector::forHeaders(
                $path,
                SpreadsheetImportHeaders::INTERNAL_MONTHLY,
                fn (int $rowHeader) => new InternalImport('2026', 'HAMATETSU', $rowHeader),
            );
        } finally {
            @unlink($path);
        }
    }

    public function test_production_import_ignores_sheets_without_a_registered_division(): void
    {
        $workbook = new Spreadsheet;
        $otherFeatureSheet = $workbook->getActiveSheet();
        $otherFeatureSheet->setTitle('Machine');
        $headers = ['TOOL NAME', 'TYPE', 'SIZE', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $otherFeatureSheet->fromArray([$headers, ['OTHER FEATURE', 'TYPE A', '10MM', 'OK']]);

        $divisionSheet = $workbook->createSheet();
        $divisionSheet->setTitle('QA 1');
        $divisionSheet->fromArray([$headers, ['QA TOOL', 'TYPE B', '20MM', 'OK']]);

        $path = tempnam(sys_get_temp_dir(), 'production-import-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            Excel::import(new ProductionDataImport($path), $path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(['QA TOOL'], ProductionData::query()->pluck('tool_name')->all());
        $this->assertSame('QA 1', ProductionData::query()->firstOrFail()->division);
    }

    public function test_production_import_allows_missing_type_and_keeps_categories_as_distinct_identity(): void
    {
        $workbook = new Spreadsheet;
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle('QA1');
        $sheet->fromArray([
            ['TOOL NAME', 'SIZE', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            ['CATEGORY TOOL', '20MM', 'OK'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'production-category-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            Excel::import(new ProductionDataImport($path, 'OPTIONAL'), $path);
            Excel::import(new ProductionDataImport($path, 'MASTER KALIBRATOR'), $path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(2, ProductionData::query()->count());
        $this->assertSame(
            ['MASTER KALIBRATOR', 'OPTIONAL'],
            ProductionData::query()->orderBy('category')->pluck('category')->all(),
        );
        $this->assertSame(['-'], ProductionData::query()->pluck('type')->unique()->all());
        $this->assertSame(['QA 1'], ProductionData::query()->pluck('division')->unique()->all());
    }

    public function test_production_import_keeps_same_tool_with_distinct_registration_numbers(): void
    {
        $workbook = new Spreadsheet;
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle('PPIC 1');
        $sheet->fromArray([
            ['TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
            ['JIG GAUGE', 'SPLINE RING', 'SIZE A', 'FINAL INSPECTION', 'J-491', null, null, null, null, null, null, 'NG', null, null, null, null, null],
            ['JIG GAUGE', 'SPLINE RING', 'SIZE A', 'FINAL INSPECTION', 'J-495', null, null, null, null, null, null, 'OK', null, null, null, null, null],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'production-registration-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            Excel::import(new ProductionDataImport($path, 'DATA ALAT UKUR ALL PRODUKSI TAHUN 2026'), $path);
            Excel::import(new ProductionDataImport($path, 'DATA ALAT UKUR ALL PRODUKSI TAHUN 2026'), $path);
            Excel::import(new ProductionDataImport($path, 'DATA ALAT UKUR ALL PRODUKSI', 2025), $path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(4, ProductionData::query()->count());
        $this->assertDatabaseHas('data_all_produksis', [
            'year' => 2025,
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'tool_name' => 'JIG GAUGE',
            'number_reg' => 'J-491',
        ]);
        $this->assertDatabaseHas('data_all_produksis', [
            'year' => 2026,
            'category' => 'DATA ALAT UKUR ALL PRODUKSI',
            'tool_name' => 'JIG GAUGE',
            'number_reg' => 'J-491',
        ]);
        $this->assertDatabaseHas('data_all_produksis', [
            'division' => 'PPIC 1',
            'tool_name' => 'JIG GAUGE',
            'number_reg' => 'J-491',
            'jul' => 'NG',
        ]);
        $this->assertDatabaseHas('data_all_produksis', [
            'division' => 'PPIC 1',
            'tool_name' => 'JIG GAUGE',
            'number_reg' => 'J-495',
            'jul' => 'OK',
        ]);
    }

    public function test_master_calibrator_import_maps_headerless_tool_rows(): void
    {
        $workbook = new Spreadsheet;
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle('QA 1');
        $sheet->fromArray([
            ['NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG'],
            [1, 'MASTER TOOL', 'CALIPER', '0-150MM', 'SER-1', 'REG-1'],
        ]);

        $path = tempnam(sys_get_temp_dir(), 'master-calibrator-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            Excel::import(new ProductionDataImport($path, 'MASTER KALIBRATOR'), $path);
        } finally {
            @unlink($path);
        }

        $row = ProductionData::firstOrFail();
        $this->assertSame('MASTER TOOL', $row->tool_name);
        $this->assertSame('CALIPER', $row->type);
        $this->assertSame('0-150MM', $row->size);
        $this->assertSame('SER-1', $row->number_seri);
        $this->assertSame('REG-1', $row->number_reg);
        $this->assertSame('MASTER KALIBRATOR', $row->category);
    }
}
