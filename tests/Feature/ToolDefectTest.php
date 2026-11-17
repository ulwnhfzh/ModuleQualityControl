<?php

namespace Tests\Feature;

use App\Exports\ToolDefectsExport;
use App\Imports\ToolDefectsImport;
use App\Models\ListUpNg;
use App\Models\ProductionData;
use App\Models\ToolDefect;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_master_recap_import_is_preserved_and_later_list_up_ng_changes_apply_as_deltas(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'GAUGE A',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'ng_date' => '2026-01-15',
            'dept' => 'PRODUKSI 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('MASTER REKAP GENERAL');
        $sheet->fromArray([
            ['DATA RECORD TOOL ALL DIVISION 2026'],
            [null, 'DIVISION', 'JANUARY'],
            [null, null, 'AUS', 'ERORR', 'GOMPAL', 'HILANG', 'KARAT', 'MACET', 'MIN STD', 'OVER STD', 'PATAH', 'RUSAK', 'SERET', 'LAIN-LAIN', 'TOTAL'],
        ]);

        foreach (ToolDefect::DIVISIONS as $index => $division) {
            $row = $index + 4;
            $sheet->fromArray([$index + 1, $division, 0, 0, 0, 0, 0, 0, 0, $division === 'PRODUKSI 1' ? 75 : 0, 0, 0, 0, 0, $division === 'PRODUKSI 1' ? 75 : 0], null, "A{$row}");
        }
        $sheet->fromArray(['TOTAL'], null, 'A21');
        $sheet->setCellValue('J21', 75);
        $sheet->setCellValue('O21', 75);
        $sheet->setCellValue('A23', 'JENIS NG ALL DEPARTEMENT');
        $sheet->setCellValue('B24', 'PRODUKSI 1');
        $sheet->setCellValue('J24', 1000);

        $filePath = tempnam(sys_get_temp_dir(), 'master-recap-');
        (new Xlsx($spreadsheet))->save($filePath);
        $spreadsheet->disconnectWorksheets();

        try {
            $this->actingAs($admin)
                ->post(route('defects.import.store'), [
                    'file' => new UploadedFile(
                        $filePath,
                        'master-recap.xlsx',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        null,
                        true,
                    ),
                    'tahun' => 2026,
                ])
                ->assertRedirect(route('defects.index', ['tahun' => 2026]))
                ->assertSessionHas('success');
        } finally {
            @unlink($filePath);
        }

        $recap = ToolDefect::where('tahun', 2026)->where('bulan', 'JAN')->where('division', 'PRODUKSI 1')->firstOrFail();
        $this->assertSame(75, (int) $recap->over_std);
        $this->assertSame(75, (int) ToolDefect::where('tahun', 2026)->sum('over_std'));

        $productionRow = ProductionData::create([
            'division' => 'PRODUKSI 1',
            'tool_name' => 'GAUGE B',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'number_reg' => 'REG-B',
        ]);
        $newNg = ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'GAUGE B',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'number_reg' => 'REG-B',
            'ng_date' => '2026-01-20',
            'dept' => 'PRODUKSI 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'RUSAK',
        ]);
        $recap->refresh();
        $this->assertSame('NG', $productionRow->fresh()->jan);
        $this->assertSame(1, (int) $recap->rusak);
        $this->assertSame(76, (int) ToolDefect::where('tahun', 2026)->sum('over_std') + ToolDefect::where('tahun', 2026)->sum('rusak'));
        $this->assertDatabaseHas('tool_defect_details', [
            'tahun' => 2026,
            'tool_name' => 'GAUGE B',
            'division' => 'PRODUKSI 1',
            'tanggal' => '2026-01-20',
        ]);

        $newNg->update(['note' => 'MIN STD']);
        $recap->refresh();
        $this->assertSame(0, (int) $recap->rusak);
        $this->assertSame(1, (int) $recap->min_std);

        $newNg->delete();
        $recap->refresh();
        $this->assertSame(75, (int) $recap->over_std);
        $this->assertSame(0, (int) $recap->min_std);
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
            ->assertSee('YEAR 2014')
            ->assertSee('YEAR 2026')
            ->assertDontSee('YEAR 2013')
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

    public function test_recap_for_a_year_without_data_shows_an_empty_table_instead_of_zero_divisions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ToolDefect::create([
            'tahun' => 2026,
            'bulan' => 'JAN',
            'division' => 'ENGINEERING',
            'aus' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('defects.index', ['tahun' => 2024, 'bulan' => 'JAN']))
            ->assertOk()
            ->assertSee('YEAR 2024')
            ->assertSee('Belum ada data rekap NG untuk YEAR')
            ->assertSee('Analisis NG untuk YEAR 2024 akan tampil setelah data tahun tersebut tersedia.');

        $this->assertSame([], $response->viewData('sortedData'));
        $this->assertFalse($response->viewData('hasYearData'));
    }

    public function test_master_recap_is_read_only_when_list_up_ng_is_the_source(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ListUpNg::create([
            'tahun' => 2026,
            'tool_name' => 'GAUGE A',
            'type' => 'MICROMETER',
            'size' => '0-25MM',
            'ng_date' => '2026-03-15',
            'dept' => 'QA 1',
            'category_ng' => 'VERIFIKASI',
            'note' => 'OVER STD',
        ]);

        $this->actingAs($admin)
            ->get(route('defects.index', ['tahun' => 2026, 'bulan' => 'MAR']))
            ->assertOk()
            ->assertSee('Nilai impor Master Rekap dipertahankan')
            ->assertSee('Rekap otomatis')
            ->assertDontSee('Tambah');

        $this->post(route('defects.store'), [
            'tahun' => 2026,
            'bulan' => 'APR',
            'division' => 'QA 1',
            'aus' => 0,
            'erorr' => 0,
            'gompal' => 0,
            'hilang' => 0,
            'karat' => 0,
            'macet' => 0,
            'min_std' => 0,
            'over_std' => 1,
            'patah' => 0,
            'rusak' => 0,
            'seret' => 0,
            'lain_lain' => 0,
        ])
            ->assertRedirect(route('defects.index', ['tahun' => 2026, 'bulan' => 'APR']))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tool_defects', [
            'tahun' => 2026,
            'bulan' => 'APR',
            'division' => 'QA 1',
            'over_std' => 0,
        ]);
    }

    private function writeSpreadsheet(Spreadsheet $spreadsheet): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'tool-defects-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($filePath);

        return $filePath;
    }
}
