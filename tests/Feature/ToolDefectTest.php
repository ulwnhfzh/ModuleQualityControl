<?php

namespace Tests\Feature;

use App\Exports\ToolDefectsExport;
use App\Imports\ToolDefectsImport;
use App\Models\ToolDefect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ToolDefectTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_reads_monthly_data_from_multiple_sheets_and_saves_erorr_column(): void
    {
        $spreadsheet = new Spreadsheet();
        $january = $spreadsheet->getActiveSheet();
        $january->setTitle('JANUARI');
        $january->fromArray([
            ['DIVISION', 'AUS', 'ERROR', 'GOMPAL'],
            ['PRODUKSI 1', 2, 3, 4],
        ]);

        $february = $spreadsheet->createSheet();
        $february->setTitle('FEBRUARI');
        $february->fromArray([
            ['DIVISION', 'AUS', 'ERORR', 'GOMPAL'],
            ['PRODUKSI 2', 5, 6, 7],
        ]);

        $filePath = $this->writeSpreadsheet($spreadsheet);
        try {
            $importedRows = (new ToolDefectsImport(2026))->import($filePath);
        } finally {
            @unlink($filePath);
        }

        $this->assertSame(2, $importedRows);
        $this->assertDatabaseHas('tool_defects', [
            'tahun' => 2026,
            'bulan' => 'JAN',
            'division' => 'PRODUKSI 1',
            'aus' => 2,
            'erorr' => 3,
            'gompal' => 4,
        ]);
        $this->assertDatabaseHas('tool_defects', [
            'tahun' => 2026,
            'bulan' => 'FEB',
            'division' => 'PRODUKSI 2',
            'aus' => 5,
            'erorr' => 6,
            'gompal' => 7,
        ]);
    }

    public function test_exported_annual_workbook_can_be_imported_without_shifting_month_columns(): void
    {
        ToolDefect::create([
            'tahun' => 2026,
            'bulan' => 'JAN',
            'division' => 'ENGINEERING',
            'aus' => 8,
            'erorr' => 2,
            'gompal' => 1,
        ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray((new ToolDefectsExport(2026))->array());
        $filePath = $this->writeSpreadsheet($spreadsheet);
        try {
            $importedRows = (new ToolDefectsImport(2026))->import($filePath);
        } finally {
            @unlink($filePath);
        }

        $this->assertSame(count(ToolDefect::DIVISIONS) * count(ToolDefect::MONTHS), $importedRows);
        $this->assertDatabaseHas('tool_defects', [
            'tahun' => 2026,
            'bulan' => 'JAN',
            'division' => 'ENGINEERING',
            'aus' => 8,
            'erorr' => 2,
            'gompal' => 1,
        ]);
        $this->assertDatabaseHas('tool_defects', [
            'tahun' => 2026,
            'bulan' => 'FEB',
            'division' => 'ENGINEERING',
            'aus' => 0,
            'erorr' => 0,
            'gompal' => 0,
        ]);
    }

    public function test_admin_can_view_data_ng_and_download_excel_and_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ToolDefect::create([
            'tahun' => 2026,
            'bulan' => 'JAN',
            'division' => 'ENGINEERING',
            'erorr' => 4,
        ]);

        $this->actingAs($admin)
            ->get(route('defects.index', ['tahun' => 2026, 'bulan' => 'JAN']))
            ->assertOk()
            ->assertSee('Data NG')
            ->assertSee('EDIT')
            ->assertSee('feature-toolbar relative z-10 mb-3 overflow-visible', false)
            ->assertSee('onclick="toggleExportNg()"', false)
            ->assertSee('id="exportMenuNg"', false)
            ->assertSee('defects/export/excel?tahun=2026&amp;bulan=JAN', false)
            ->assertSee('defects/export/pdf?tahun=2026&amp;bulan=JAN', false);

        $this->get(route('defects.export.excel', ['tahun' => 2026]))
            ->assertOk()
            ->assertDownload('Data-NG-2026.xlsx');

        $this->get(route('defects.export.pdf', ['tahun' => 2026, 'bulan' => 'JAN']))
            ->assertOk()
            ->assertDownload('Data-NG-JAN-2026.pdf');
    }

    private function writeSpreadsheet(Spreadsheet $spreadsheet): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'tool-defects-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($filePath);

        return $filePath;
    }
}
