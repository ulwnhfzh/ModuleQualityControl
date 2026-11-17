<?php

namespace Tests\Feature;

use App\Models\MasterCalibrator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MasterCalibratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_master_calibrator_pages_and_export_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        MasterCalibrator::create([
            'tahun' => 2026,
            'tool_name' => 'CALIBRATOR',
            'jan' => 'OK',
        ]);

        $this->actingAs($admin)
            ->get(route('master-calibrator.index', ['tahun' => 2026]))
            ->assertOk();

        $this->get(route('master-calibrator.create', ['tahun' => 2026]))
            ->assertOk();

        $this->get(route('master-calibrator.import.form', ['tahun' => 2026]))
            ->assertOk();

        $this->get(route('master-calibrator.export', ['tahun' => 2026]))
            ->assertOk()
            ->assertDownload('master-kalibrator-2026.xlsx');

        $pdf = $this->get(route('master-calibrator.export.pdf', ['tahun' => 2026]));
        $pdf->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('master-kalibrator-2026.pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
    }

    public function test_admin_can_create_update_and_delete_master_calibrator(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('master-calibrator.store'), [
            'tahun' => 2026,
            'tool_name' => 'calibrator one',
            'type' => 'Digital',
            'jan' => 'OK',
        ])->assertRedirect(route('master-calibrator.index', ['tahun' => 2026]));

        $item = MasterCalibrator::query()->firstOrFail();
        $this->assertSame('CALIBRATOR ONE', $item->tool_name);

        $this->get(route('master-calibrator.edit', $item))->assertOk();

        $this->put(route('master-calibrator.update', $item), [
            'tahun' => 2026,
            'tool_name' => 'calibrator updated',
            'type' => 'Analog',
            'jan' => 'NG',
        ])->assertRedirect(route('master-calibrator.index', ['tahun' => 2026]));

        $this->assertDatabaseHas('master_calibrators', [
            'id' => $item->id,
            'tool_name' => 'CALIBRATOR UPDATED',
            'jan' => 'NG',
        ]);

        $this->delete(route('master-calibrator.destroy', $item))
            ->assertRedirect(route('master-calibrator.index', ['tahun' => 2026]));

        $this->assertDatabaseMissing('master_calibrators', ['id' => $item->id]);
    }

    public function test_admin_can_import_master_calibrator_workbook(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $workbook = new Spreadsheet;
        $otherSheet = $workbook->getActiveSheet();
        $otherSheet->setTitle('Machine');
        $otherSheet->fromArray([['TOOL NAME', 'MACHINE MODEL', 'NEXT DATE'], ['PRESS', 'MODEL A', '2026-10-10']]);

        $masterSheet = $workbook->createSheet();
        $masterSheet->setTitle('Master Kalibrator');
        $masterSheet->fromArray([
            ['MASTER KALIBRATOR'],
            [],
            ['NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG', 'PART NAME', 'PART NUMBER', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'NOTE'],
            [1, 'CALIBRATOR A', 'DIGITAL', '10MM', 'SER-1', 'REG-1', 'PART A', 'PART-1', 'OK', null, null, null, null, null, null, null, null, null, null, null, 'CHECKED'],
        ]);
        $secondMasterSheet = $workbook->createSheet();
        $secondMasterSheet->setTitle('Master Kalibrator 2');
        $secondMasterSheet->fromArray([
            [],
            [],
            ['NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG', 'PART NAME', 'PART NUMBER', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'NOTE'],
            [1, 'CALIBRATOR B', 'ANALOG', '20MM', 'SER-2', 'REG-2', 'PART B', 'PART-2', 'NG', null, null, null, null, null, null, null, null, null, null, null, 'REPAIR'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'master-calibrator-');
        (new Xlsx($workbook))->save($path);
        $workbook->disconnectWorksheets();

        try {
            $upload = UploadedFile::fake()->createWithContent('master.xlsx', file_get_contents($path));
            $this->actingAs($admin)
                ->post(route('master-calibrator.import'), [
                    'tahun' => 2026,
                    'file' => $upload,
                ])
                ->assertRedirect(route('master-calibrator.index', ['tahun' => 2026]))
                ->assertSessionHas('success');
        } finally {
            @unlink($path);
        }

        $this->assertDatabaseHas('master_calibrators', [
            'tahun' => 2026,
            'tool_name' => 'CALIBRATOR A',
            'number_reg' => 'REG-1',
            'jan' => 'OK',
            'note' => 'CHECKED',
        ]);
        $this->assertDatabaseHas('master_calibrators', [
            'tahun' => 2026,
            'tool_name' => 'CALIBRATOR B',
            'number_reg' => 'REG-2',
            'jan' => 'NG',
            'note' => 'REPAIR',
        ]);
        $this->assertDatabaseMissing('master_calibrators', ['tool_name' => 'PRESS']);
    }
}
