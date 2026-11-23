<?php

namespace Tests\Feature;

use App\Imports\AnnualStatusWorkbookImport;
use App\Models\ExternalCalibrationListUp;
use App\Models\InternalCalibrationListUp;
use App\Models\InternalVerificationSchedule;
use App\Models\ListUpNg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AnnualStatusWorkbookImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_calibration_workbook_maps_two_header_rows_and_status(): void
    {
        $path = $this->makeWorkbook('CALIPER', [
            ['LIST UP INTERNAL CALIBRATION'],
            ['PT HAMATETSU INDONESIA'],
            ['NO', 'TOOL NAME', 'TYPE', 'SPECIFICATION', 'NUMBER SERI', 'NUMBER REGISTRATION', 'SHOP', 'YEAR 2026'],
            [null, null, null, null, null, null, null, 'CALIBRATION DATE', 'NEXT CALIBRATION DATE', 'JGM'],
            [1, 'CALIPER', 'DIGITAL', '0-150 mm', 'SER-1', 'REG-1', 'QA 1', '2026-04-10', '2027-04-10', 'NG'],
        ]);

        try {
            $import = new AnnualStatusWorkbookImport($path, 'internal_calibration', 2026);
            $import->import($path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(1, $import->getImportedCount());
        $schedule = InternalCalibrationListUp::firstOrFail();
        $this->assertSame(2026, $schedule->tahun);
        $this->assertSame('CALIPER', $schedule->tool_name);
        $this->assertSame('DIGITAL', $schedule->tool_type);
        $this->assertSame('REG-1', $schedule->registration_number);
        $this->assertSame('2026-04-10', $schedule->calibration_date->format('Y-m-d'));
        $this->assertSame('NG', $schedule->jgm);
    }

    public function test_external_calibration_workbook_maps_status_without_treating_certificate_as_count(): void
    {
        $path = $this->makeWorkbook('CALIPER', [
            ['LIST UP EXTERNAL CALIBRATION'],
            ['PT HAMATETSU INDONESIA'],
            ['NO', 'TOOL NAME', 'SPECIFICATION', 'NUMBER SERI', 'NUMBER REGISTRATION', 'SHOP', 'YEAR 2026'],
            [null, null, null, null, null, null, 'CALIBRATION DATE', 'NEXT CALIBRATION DATE', 'JGM', 'NUMBER KALIBRASI'],
            [1, 'CALIPER', '0-150 mm', 'SER-1', 'REG-1', 'QA 2', '2026-04-10', '2027-04-10', 'OK', 'CERT-1'],
        ]);

        try {
            $import = new AnnualStatusWorkbookImport($path, 'external_calibration', 2026);
            $import->import($path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(1, $import->getImportedCount());
        $item = ExternalCalibrationListUp::firstOrFail();
        $this->assertSame('OK', $item->jgm);
        $this->assertNull($item->calibration_count);
    }

    public function test_internal_calibration_records_in_different_shops_remain_distinct(): void
    {
        $path = $this->makeWorkbook('CALIPER', [
            ['LIST UP INTERNAL CALIBRATION'],
            ['PT HAMATETSU INDONESIA'],
            ['NO', 'TOOL NAME', 'TYPE', 'SPECIFICATION', 'NUMBER SERI', 'NUMBER REGISTRATION', 'SHOP', 'YEAR 2026'],
            [null, null, null, null, null, null, null, 'CALIBRATION DATE', 'NEXT CALIBRATION DATE', 'JGM'],
            [1, 'CALIPER', 'DIGITAL', '0-150 mm', 'SER-1', 'REG-1', 'QA 1', '2026-04-10', '2027-04-10', 'OK'],
            [2, 'CALIPER', 'DIGITAL', '0-150 mm', 'SER-1', 'REG-1', 'QA 2', '2026-04-10', '2027-04-10', 'OK'],
        ]);

        try {
            $import = new AnnualStatusWorkbookImport($path, 'internal_calibration', 2026);
            $import->import($path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(2, $import->getImportedCount());
        $this->assertSame(2, InternalCalibrationListUp::count());
    }

    public function test_verification_import_skips_same_period_ok_conflict_and_imports_other_rows(): void
    {
        InternalVerificationSchedule::create([
            'tahun' => 2026,
            'company' => 'HAMATETSU',
            'tool_name' => 'KEEP OK GAUGE',
            'tool_type' => 'STEEL',
            'specification' => 'M8',
            'serial_number' => 'SER-8',
            'registration_number' => 'REG-8',
            'shop' => 'P2',
            'period1_verification_date' => '2026-05-01',
            'period1_jgm' => 'OK',
        ]);
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'BLADE MICROMETER',
            'type' => 'DIGITAL',
            'size' => '0-25 mm',
            'number_seri' => 'SER-2',
            'number_reg' => 'REG-2',
            'ng_date' => '2026-07-03',
            'division' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
        ]);

        $path = $this->makeWorkbook('BLADE MM', [
            ['LIST UP INTERNAL VERIFICATION'],
            ['PT HAMATETSU INDONESIA'],
            ['NO', 'TOOL NAME', 'TYPE', 'SPECIFICATION', 'NUMBER SERI', 'NUMBER REGISTRATION', 'SHOP', 'YEAR 2026'],
            [null, null, null, null, null, null, null, 'PERIODE 1', null, null, 'PERIODE 2'],
            [null, null, null, null, null, null, null, 'VERIFICATION DATE', 'NEXT VERIFICATION DATE', 'JGM', 'VERIFICATION DATE', 'NEXT VERIFICATION DATE', 'JGM'],
            [1, 'BLADE MICROMETER', 'DIGITAL', '0-25 mm', 'SER-2', 'REG-2', 'QA 1', '2026-01-03', '2026-06-03', 'NG', '2026-07-03', '2027-01-03', 'OK'],
            [2, 'SAFE GAUGE', 'STEEL', 'M10', 'SER-3', 'REG-3', 'P2', '2026-04-03', '2026-10-03', 'NG', null, null, null],
            [3, 'INVALID GAUGE', 'STEEL', 'M12', 'SER-4', 'REG-4', 'P2', '2026-04-03', 'z', 'NG', null, null, null],
            [4, 'KEEP OK GAUGE', 'STEEL', 'M8', 'SER-8', 'REG-8', 'P2', '2026-05-20', '2026-11-20', 'NG', null, null, null],
        ]);

        try {
            $import = new AnnualStatusWorkbookImport($path, 'verification', 2026);
            $import->import($path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(2, $import->getImportedCount());
        $this->assertSame(1, $import->getSkippedConflictCount());
        $this->assertCount(1, $import->getSkippedInvalidRows());
        $this->assertSame('INVALID GAUGE', $import->getSkippedInvalidRows()[0]['tool_name']);
        $schedule = InternalVerificationSchedule::where('tool_name', 'SAFE GAUGE')->firstOrFail();
        $this->assertSame('2026-04-03', $schedule->period1_verification_date->format('Y-m-d'));
        $this->assertSame('NG', $schedule->period1_jgm);
        $this->assertDatabaseMissing('verifikasi_internal_schedules', [
            'tool_name' => 'BLADE MICROMETER',
        ]);
        $this->assertSame(
            'OK',
            InternalVerificationSchedule::where('tool_name', 'KEEP OK GAUGE')->firstOrFail()->period1_jgm,
        );
    }

    public function test_list_up_ng_only_marks_calibration_status_for_the_matching_month(): void
    {
        $schedule = InternalCalibrationListUp::create([
            'tahun' => 2026,
            'company' => 'HAMATETSU',
            'tool_name' => 'CALIPER',
            'tool_type' => 'DIGITAL',
            'specification' => '0-150 mm',
            'serial_number' => 'SER-4',
            'registration_number' => 'REG-4',
            'shop' => 'QA 1',
            'calibration_date' => '2026-04-10',
            'jgm' => null,
        ]);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'CALIPER',
            'type' => 'DIGITAL',
            'size' => '0-150 mm',
            'number_seri' => 'SER-4',
            'number_reg' => 'REG-4',
            'ng_date' => '2026-03-10',
            'division' => 'QA 1',
            'category_ng' => 'KALIBRASI',
        ]);
        $this->assertNull($schedule->fresh()->jgm);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'CALIPER',
            'type' => 'DIGITAL',
            'size' => '0-150 mm',
            'number_seri' => 'SER-4',
            'number_reg' => 'REG-4',
            'ng_date' => '2026-04-20',
            'division' => 'QA 1',
            'category_ng' => 'KALIBRASI',
        ]);
        $this->assertSame('NG', $schedule->fresh()->jgm);
    }

    public function test_external_calibration_ng_is_synchronized_and_ok_is_not_overwritten(): void
    {
        $schedule = ExternalCalibrationListUp::create([
            'tahun' => 2026,
            'tool_name' => 'EXTERNAL GAUGE',
            'specification' => '0-25 mm',
            'serial_number' => 'SER-5',
            'registration_number' => 'REG-5',
            'shop' => 'QA 2',
            'calibration_date' => '2026-05-10',
            'jgm' => null,
        ]);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'EXTERNAL GAUGE',
            'size' => '0-25 mm',
            'number_seri' => 'SER-5',
            'number_reg' => 'REG-5',
            'ng_date' => '2026-05-20',
            'division' => 'QA 2',
            'category_ng' => 'KALIBRASI EKSTENAL',
        ]);
        $this->assertSame('NG', $schedule->fresh()->jgm);
    }

    public function test_verification_ng_does_not_overwrite_same_period_ok(): void
    {
        $schedule = InternalVerificationSchedule::create([
            'tahun' => 2026,
            'company' => 'HAMATETSU',
            'tool_name' => 'VERIFICATION GAUGE',
            'tool_type' => 'DIGITAL',
            'specification' => '0-25 mm',
            'serial_number' => 'SER-6',
            'registration_number' => 'REG-6',
            'shop' => 'P3',
            'period1_verification_date' => '2026-03-10',
            'period1_jgm' => 'OK',
        ]);

        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'VERIFICATION GAUGE',
            'type' => 'DIGITAL',
            'size' => '0-25 mm',
            'number_seri' => 'SER-6',
            'number_reg' => 'REG-6',
            'ng_date' => '2026-03-20',
            'division' => 'P3',
            'category_ng' => 'VERIFIKASI',
        ]);

        $this->assertSame('OK', $schedule->fresh()->period1_jgm);
    }

    private function makeWorkbook(string $sheetName, array $rows): string
    {
        $workbook = new Spreadsheet;
        $sheet = $workbook->getActiveSheet();
        $sheet->setTitle($sheetName);
        $sheet->fromArray($rows, null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'annual-status-import-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        return $path;
    }
}
