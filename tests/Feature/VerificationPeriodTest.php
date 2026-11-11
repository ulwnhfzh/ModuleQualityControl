<?php

namespace Tests\Feature;

use App\Models\InternalCalibrationListUp;
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

    public function test_calibration_period_two_data_is_saved_on_create_and_update(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $calibrationData = [
            'tahun' => '2026',
            'tool_name' => 'CALIPER',
            'tool_type' => 'MEASURING',
            'period2_calibration_date' => '2026-02-01',
            'period2_next_calibration_date' => '2026-08-01',
            'period2_jgm' => 'PASS',
        ];

        $this->actingAs($admin)
            ->post(route('verifikasi.periode.kalibrasi.store'), $calibrationData)
            ->assertRedirect(route('verifikasi.periode.index', ['tahun' => '2026', 'jenis' => 'kalibrasi']));

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
            ->assertRedirect(route('verifikasi.periode.index', ['tahun' => '2026', 'jenis' => 'kalibrasi']));

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
