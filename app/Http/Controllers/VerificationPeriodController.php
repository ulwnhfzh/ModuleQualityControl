<?php

namespace App\Http\Controllers;

use App\Models\InternalVerificationSchedule;
use App\Models\InternalCalibrationListUp;
use App\Models\Qc2Verification;
use App\Models\ProductionData;
use App\Exports\VerificationPeriodExport;
use App\Exports\InternalCalibrationListUpExport;
use App\Imports\VerificationPeriodImport;
use App\Imports\InternalCalibrationListUpImport;
use App\Imports\AnnualStatusWorkbookImport;
use App\Imports\Qc2VerificationImport;
use App\Exports\Qc2VerificationExport;
use App\Imports\SpreadsheetImportHeaders;
use App\Imports\SpreadsheetSheetSelector;
use App\Services\ListUpNgStatusSynchronizer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class VerificationPeriodController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'jenis' => 'nullable|in:kalibrasi,verifikasi,verifikasi_qc2',
            'company' => 'nullable|in:HAMATETSU,NISSO GIKEN',
            'tool_name' => 'nullable|string|max:255',
            'shop' => 'nullable|string|max:100',
        ]);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $jenis = $filters['jenis'] ?? 'verifikasi';
        $company = $filters['company'] ?? 'HAMATETSU';
        $toolName = trim($filters['tool_name'] ?? '');
        $shop = $filters['shop'] ?? '';
        if ($jenis === 'verifikasi_qc2') {
            $toolNameList = $this->productionToolNameOptions($tahun, $shop);
            if ($toolName !== '' && !$toolNameList->contains($toolName)) {
                $toolName = '';
            }
        }

        if ($jenis === 'kalibrasi') {
            $query = InternalCalibrationListUp::where('tahun', $tahun)->where('company', $company);
            $shops = (clone $query)->whereNotNull('shop')->where('shop', '<>', '')
                ->distinct()->orderBy('shop')->pluck('shop');
            $shops = $this->mergeProductionShopOptions($tahun, $shops);
            $toolNameList = $this->scheduleToolNameOptions(clone $query, $shop);
            if ($toolName !== '' && !$toolNameList->contains($toolName)) {
                $toolName = '';
            }
            if ($toolName !== '') $query->where('tool_name', $toolName);
            if ($shop !== '') $query->where('shop', $shop);
            $schedules = $query->orderBy('tool_name')->orderBy('id')->simplePaginate(100)->withQueryString();
            return view('verification.period.calibration', compact('schedules', 'tahun', 'jenis', 'company', 'shops', 'toolNameList', 'toolName', 'shop'));
        }

        if ($jenis === 'verifikasi_qc2') {
            $query = Qc2Verification::where('tahun', $tahun)->where('company', $company);
            $shops = (clone $query)->whereNotNull('shop')->where('shop', '<>', '')
                ->distinct()->orderBy('shop')->pluck('shop');
            $shops = $this->mergeProductionShopOptions($tahun, $shops);
            if ($toolName !== '') $query->where('tool_name', $toolName);
            if ($shop !== '') $query->where('shop', $shop);
            $schedules = $query->orderBy('tool_name')->orderBy('id')->simplePaginate(100)->withQueryString();
            return view('verification.period.qc2-verification', compact('schedules', 'tahun', 'jenis', 'company', 'shops', 'toolNameList', 'toolName', 'shop'));
        }

        $query = InternalVerificationSchedule::query()
            ->where('tahun', $tahun)
            ->where('company', $company);
        $shops = (clone $query)->whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
        $toolNameList = $this->scheduleToolNameOptions(clone $query, $shop);
        if ($toolName !== '' && !$toolNameList->contains($toolName)) {
            $toolName = '';
        }
        if ($toolName !== '') $query->where('tool_name', $toolName);
        if ($shop !== '') $query->where('shop', $shop);
        $schedules = $query->orderBy('tool_name')->orderBy('id')->simplePaginate(100)->withQueryString();
        $types = InternalVerificationSchedule::whereNotNull('tool_type')
            ->where('tool_type', '<>', '')
            ->distinct()
            ->orderBy('tool_type')
            ->pluck('tool_type');

        return view('verification.period.index', compact('schedules', 'tahun', 'types', 'jenis', 'company', 'shops', 'toolNameList', 'toolName', 'shop'));
    }

    private function scheduleToolNameOptions(Builder $query, string $shop): Collection
    {
        if ($shop !== '') {
            $query->where('shop', $shop);
        }

        return $query->whereNotNull('tool_name')
            ->where('tool_name', '<>', '')
            ->distinct()
            ->orderBy('tool_name')
            ->pluck('tool_name');
    }

    private function productionToolNameOptions(int $year, string $shop): Collection
    {
        $query = ProductionData::query()
            ->where('year', $year)
            ->where('is_year_template', false)
            ->whereNotNull('tool_name')
            ->where('tool_name', '<>', '');

        if ($shop !== '') {
            $divisions = in_array(mb_strtoupper(trim($shop)), ['LSCC', 'L.SCC', 'LINE SCC'], true)
                ? ['LSCC', 'L.SCC', 'LINE SCC']
                : [$shop];
            $query->whereIn('division', $divisions);
        }

        return $query->distinct()->orderBy('tool_name')->pluck('tool_name');
    }

    // ==========================================
    // METHOD KALIBRASI INTERNAL
    // ==========================================

    public function internalCalibrationCreate(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        $tools = $this->productionToolsForYear($tahun);
        $types = InternalCalibrationListUp::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = InternalCalibrationListUp::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
            
        $toolsWithTypes = $this->getToolsWithTypes('kalibrasi_internal_list_ups', $tahun);

        return view('verification.period.calibration-form', compact('tahun', 'company', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function internalCalibrationStore(Request $request)
    {
        $data = $this->validatedCalibrationData($request);
        $schedule = InternalCalibrationListUp::create($data);
        app(ListUpNgStatusSynchronizer::class)->syncTarget('internal_calibration', $schedule->id);
        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.periode.kalibrasi.create', ['tahun' => $data['tahun'], 'company' => $data['company']])
                ->with('success', 'Jadwal berhasil disimpan. Lanjutkan input berikutnya.');
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'kalibrasi', 'company' => $data['company']])
            ->with('success', 'Jadwal kalibrasi internal berhasil ditambahkan.');
    }

    public function internalCalibrationEdit(InternalCalibrationListUp $schedule)
    {
        $tahun = $schedule->tahun; $company = $schedule->company;
        $tools = $this->productionToolsForYear($tahun, $schedule->tool_name);
        $types = InternalCalibrationListUp::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = InternalCalibrationListUp::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
        $toolsWithTypes = $this->getToolsWithTypes('kalibrasi_internal_list_ups', $tahun);

        return view('verification.period.calibration-form', compact('schedule', 'tahun', 'company', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function internalCalibrationUpdate(Request $request, InternalCalibrationListUp $schedule)
    {
        $data = $this->validatedCalibrationData($request);
        $schedule->update($data);
        app(ListUpNgStatusSynchronizer::class)->syncTarget('internal_calibration', $schedule->id);
        return redirect()->route('verifikasi.periode.index', ['tahun' => $schedule->tahun, 'jenis' => 'kalibrasi', 'company' => $schedule->company])
            ->with('success', 'Jadwal kalibrasi internal berhasil diperbarui.');
    }

    public function internalCalibrationDestroy(InternalCalibrationListUp $schedule)
    {
        $tahun = $schedule->tahun;
        $schedule->delete();
        app(ListUpNgStatusSynchronizer::class)->syncTarget('internal_calibration', $schedule->id);
        return redirect()->route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi', 'company' => $schedule->company])
            ->with('success', 'Jadwal kalibrasi internal berhasil dihapus.');
    }

    public function internalCalibrationImportForm(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        return view('verification.period.calibration-import', compact('tahun', 'company'));
    }

    public function internalCalibrationImport(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);
        $annualImport = null;
        try {
            $path = $request->file('file')->getRealPath();
            if (AnnualStatusWorkbookImport::supports($path, 'internal_calibration', (int) $data['tahun'])) {
                $annualImport = new AnnualStatusWorkbookImport($path, 'internal_calibration', (int) $data['tahun'], $data['company']);
                $annualImport->import($path);
            } else {
                $import = SpreadsheetSheetSelector::forHeaders(
                    $request->file('file'),
                    [
                        SpreadsheetImportHeaders::TOOL_NAME,
                        SpreadsheetImportHeaders::TYPE,
                        ['calibration_date', 'tanggal_kalibrasi'],
                        ['next_calibration_date', 'next_calibration', 'tanggal_kalibrasi_berikutnya'],
                    ],
                    fn (int $rowHeader) => new InternalCalibrationListUpImport((int) $data['tahun'], $data['company'], $rowHeader),
                );
                Excel::import($import, $request->file('file'));
            }
        } catch (\Throwable $exception) {
            return back()->withInput()->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }
        app(ListUpNgStatusSynchronizer::class)->syncYear((int) $data['tahun']);
        $message = 'List Up Kalibrasi Internal berhasil diimpor.';
        if ($annualImport !== null) {
            $message .= ' Baris diproses: ' . $annualImport->getImportedCount()
                . '; konflik status dilewati: ' . $annualImport->getSkippedConflictCount()
                . '; status OK dipertahankan: ' . $annualImport->getProtectedOkCount()
                . '; tanggal tidak valid dilewati: ' . count($annualImport->getSkippedInvalidRows()) . '.';
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'kalibrasi', 'company' => $data['company']])
            ->with('success', $message);
    }

    public function internalCalibrationExport(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        return Excel::download(new InternalCalibrationListUpExport($tahun, $company), "list-up-kalibrasi-internal-{$tahun}.xlsx");
    }

    public function internalCalibrationExportPdf(Request $request)
    {
        return $this->downloadPeriodPdf($request, InternalCalibrationListUp::class, 'Jadwal Kalibrasi Internal', [
            ['label' => 'Tool Name', 'key' => 'tool_name'],
            ['label' => 'Type', 'key' => 'tool_type'],
            ['label' => 'Specification', 'key' => 'specification'],
            ['label' => 'Serial No.', 'key' => 'serial_number'],
            ['label' => 'Registration No.', 'key' => 'registration_number'],
            ['label' => 'Shop', 'key' => 'shop'],
            ['label' => 'Calibration Date', 'key' => 'calibration_date'],
            ['label' => 'Next Calibration Date', 'key' => 'next_calibration_date'],
            ['label' => 'JGM', 'key' => 'jgm'],
            ['label' => 'Period 2 Calibration Date', 'key' => 'period2_calibration_date'],
            ['label' => 'Period 2 Next Calibration Date', 'key' => 'period2_next_calibration_date'],
            ['label' => 'Period 2 JGM', 'key' => 'period2_jgm'],
        ], 'list-up-kalibrasi-internal');
    }

    // ==========================================
    // METHOD VERIFIKASI INTERNAL
    // ==========================================

    public function create(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        $types = InternalVerificationSchedule::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = InternalVerificationSchedule::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
        $tools = $this->productionToolsForYear($tahun);
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_internal_schedules', $tahun);

        return view('verification.period.form', compact('tahun', 'company', 'types', 'shops', 'tools', 'toolsWithTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $schedule = InternalVerificationSchedule::create($data);
        app(ListUpNgStatusSynchronizer::class)->syncTarget('internal_verification', $schedule->id);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.periode.create', ['tahun' => $data['tahun'], 'company' => $data['company']])
                ->with('success', 'Jadwal berhasil disimpan. Lanjutkan input berikutnya.');
        }

        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'company' => $data['company']])
            ->with('success', 'Jadwal periode berhasil ditambahkan.');
    }

    public function edit(InternalVerificationSchedule $schedule)
    {
        $tahun = $schedule->tahun; $company = $schedule->company;
        $types = InternalVerificationSchedule::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = InternalVerificationSchedule::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
        $tools = $this->productionToolsForYear($tahun, $schedule->tool_name);
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_internal_schedules', $tahun);

        return view('verification.period.form', compact('schedule', 'tahun', 'company', 'types', 'shops', 'tools', 'toolsWithTypes'));
    }

    public function update(Request $request, InternalVerificationSchedule $schedule)
    {
        $schedule->update($this->validatedData($request, $schedule));
        app(ListUpNgStatusSynchronizer::class)->syncTarget('internal_verification', $schedule->id);

        return redirect()->route('verifikasi.periode.index', ['tahun' => $schedule->tahun, 'company' => $schedule->company])
            ->with('success', 'Jadwal periode berhasil diperbarui.');
    }

    public function destroy(InternalVerificationSchedule $schedule)
    {
        $tahun = $schedule->tahun;
        $schedule->delete();
        app(ListUpNgStatusSynchronizer::class)->syncTarget('internal_verification', $schedule->id);

        return redirect()->route('verifikasi.periode.index', ['tahun' => $tahun, 'company' => $schedule->company])
            ->with('success', 'Jadwal periode berhasil dihapus.');
    }

    public function importForm(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        return view('verification.period.import', compact('tahun', 'company'));
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);
        $annualImport = null;
        try {
            $path = $request->file('file')->getRealPath();
            if (AnnualStatusWorkbookImport::supports($path, 'verification', (int) $data['tahun'])) {
                $annualImport = new AnnualStatusWorkbookImport($path, 'verification', (int) $data['tahun'], $data['company']);
                $annualImport->import($path);
            } else {
                $import = SpreadsheetSheetSelector::forHeaders(
                    $request->file('file'),
                    [
                        SpreadsheetImportHeaders::TOOL_NAME,
                        SpreadsheetImportHeaders::TYPE,
                        ['period1_verification_date', 'period_1_verification_date', 'verification_date', 'tanggal_verifikasi'],
                    ],
                    fn (int $rowHeader) => new VerificationPeriodImport((int) $data['tahun'], $data['company'], $rowHeader),
                );
                Excel::import($import, $request->file('file'));
            }
        } catch (\Throwable $exception) {
            return redirect()->route('verifikasi.periode.import.form', ['tahun' => $data['tahun'], 'company' => $data['company']])
                ->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }

        app(ListUpNgStatusSynchronizer::class)->syncYear((int) $data['tahun']);
        $message = 'Data List Up berhasil diimpor.';
        if ($annualImport !== null) {
            $message .= ' Baris diproses: ' . $annualImport->getImportedCount()
                . '; konflik status dilewati: ' . $annualImport->getSkippedConflictCount()
                . '; status OK dipertahankan: ' . $annualImport->getProtectedOkCount()
                . '; tanggal tidak valid dilewati: ' . count($annualImport->getSkippedInvalidRows()) . '.';
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'company' => $data['company']])
            ->with('success', $message);
    }

    public function export(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        return Excel::download(new VerificationPeriodExport($tahun, $company), "list-up-verifikasi-{$company}-{$tahun}.xlsx");
    }

    public function exportPdf(Request $request)
    {
        return $this->downloadPeriodPdf($request, InternalVerificationSchedule::class, 'Jadwal Verification Internal', [
            ['label' => 'Tool Name', 'key' => 'tool_name'],
            ['label' => 'Type', 'key' => 'tool_type'],
            ['label' => 'Specification', 'key' => 'specification'],
            ['label' => 'Serial No.', 'key' => 'serial_number'],
            ['label' => 'Registration No.', 'key' => 'registration_number'],
            ['label' => 'Shop', 'key' => 'shop'],
            ['label' => 'Period 1 Verification Date', 'key' => 'period1_verification_date'],
            ['label' => 'Period 1 Next Verification Date', 'key' => 'period1_next_verification_date'],
            ['label' => 'Period 1 JGM', 'key' => 'period1_jgm'],
            ['label' => 'Period 2 Verification Date', 'key' => 'period2_verification_date'],
            ['label' => 'Period 2 Next Verification Date', 'key' => 'period2_next_verification_date'],
            ['label' => 'Period 2 JGM', 'key' => 'period2_jgm'],
        ], 'list-up-verifikasi');
    }

    // ==========================================
    // METHOD VERIFIKASI QC2
    // ==========================================

    public function qc2Create(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        $tools = $this->productionToolsForYear($tahun);
        $types = Qc2Verification::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = Qc2Verification::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_qc2', $tahun);

        return view('verification.period.qc2-form', compact('tahun', 'company', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function qc2Store(Request $request)
    {
        $data = $this->validatedQc2Data($request);
        Qc2Verification::create($data);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.qc2.create', ['tahun' => $data['tahun'], 'company' => $data['company']])
                ->with('success', 'Jadwal QC2 berhasil disimpan. Lanjutkan input berikutnya.');
        }

        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'verifikasi_qc2', 'company' => $data['company']])
            ->with('success', 'Jadwal QC2 berhasil ditambahkan.');
    }

    public function qc2Edit(Qc2Verification $schedule)
    {
        $tahun = $schedule->tahun; $company = $schedule->company;
        $tools = $this->productionToolsForYear($tahun, $schedule->tool_name);
        $types = Qc2Verification::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = Qc2Verification::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($tahun, $shops);
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_qc2', $tahun);

        return view('verification.period.qc2-form', compact('schedule', 'tahun', 'company', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function qc2Update(Request $request, Qc2Verification $schedule)
    {
        $schedule->update($this->validatedQc2Data($request));

        return redirect()->route('verifikasi.periode.index', ['tahun' => $schedule->tahun, 'jenis' => 'verifikasi_qc2', 'company' => $schedule->company])
            ->with('success', 'Jadwal QC2 berhasil diperbarui.');
    }

    public function qc2Destroy(Qc2Verification $schedule)
    {
        $tahun = $schedule->tahun;
        $schedule->delete();

        return redirect()->route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'verifikasi_qc2', 'company' => $schedule->company])
            ->with('success', 'Jadwal QC2 berhasil dihapus.');
    }

    public function qc2ImportForm(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        return view('verification.period.qc2-import', compact('tahun', 'company'));
    }

    public function qc2Import(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);
        try {
            $import = SpreadsheetSheetSelector::forHeaders(
                $request->file('file'),
                [
                    SpreadsheetImportHeaders::TOOL_NAME,
                    SpreadsheetImportHeaders::TYPE,
                    ['verification_date', 'tanggal_verifikasi'],
                    ['next_verification_date', 'tanggal_verifikasi_berikutnya'],
                ],
                fn (int $rowHeader) => new Qc2VerificationImport((int) $data['tahun'], $data['company'], $rowHeader),
            );
            Excel::import($import, $request->file('file'));
        } catch (\Throwable $exception) {
            return back()->withInput()->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'verifikasi_qc2', 'company' => $data['company']])
            ->with('success', 'Data List Up QC2 berhasil diimpor.');
    }

    public function qc2ExportExcel(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $company = $this->validatedCompany($request);
        return Excel::download(new Qc2VerificationExport($tahun, $company), "list-up-qc2-{$company}-{$tahun}.xlsx");
    }

    public function qc2ExportPdf(Request $request)
    {
        return $this->downloadPeriodPdf($request, Qc2Verification::class, 'Jadwal Verification Internal QC2', [
            ['label' => 'Tool Name', 'key' => 'tool_name'],
            ['label' => 'Type', 'key' => 'tool_type'],
            ['label' => 'Specification', 'key' => 'specification'],
            ['label' => 'Serial No.', 'key' => 'serial_number'],
            ['label' => 'Registration No.', 'key' => 'registration_number'],
            ['label' => 'Shop', 'key' => 'shop'],
            ['label' => 'Verification Date', 'key' => 'verification_date'],
            ['label' => 'Next Verification Date', 'key' => 'next_verification_date'],
            ['label' => 'JGM', 'key' => 'jgm'],
        ], 'list-up-qc2');
    }

    private function downloadPeriodPdf(Request $request, string $model, string $title, array $columns, string $filename)
    {
        $tahun = $this->validatedYear($request);
        $isCalibration = $model === InternalCalibrationListUp::class;
        $company = $isCalibration ? '' : $this->validatedCompany($request);
        $query = $model::query()->where('tahun', $tahun);
        if (!$isCalibration) {
            $query->where('company', $company);
        }
        $schedules = $query->orderBy('tool_name')->get();

        return Pdf::loadView('verification.period.pdf', compact('schedules', 'tahun', 'title', 'columns', 'company'))
            ->setPaper('a4', 'landscape')
            ->download("{$filename}-{$tahun}.pdf");
    }

    // ==========================================
    // METHOD PRIVATE UNTUK VALIDASI & HELPER
    // ==========================================

    private function productionToolsForYear(int $year, ?string $currentToolName = null)
    {
        $tools = ProductionData::query()
            ->where('year', $year)
            ->where('is_year_template', false)
            ->whereNotNull('tool_name')
            ->where('tool_name', '<>', '')
            ->select('tool_name')
            ->distinct()
            ->orderBy('tool_name')
            ->get();

        if ($currentToolName !== null && $currentToolName !== '' && !$tools->contains('tool_name', $currentToolName)) {
            $tools->push((object) ['tool_name' => $currentToolName]);
        }

        return $tools;
    }

    private function mergeProductionShopOptions(int $year, Collection $scheduleShops): Collection
    {
        return $scheduleShops
            ->merge(ProductionData::query()
                ->where('year', $year)
                ->where('is_year_template', false)
                ->whereNotNull('division')
                ->where('division', '<>', '')
                ->distinct()
                ->orderBy('division')
                ->pluck('division'))
            ->filter(fn ($shop) => trim((string) $shop) !== '')
            ->reject(fn ($shop) => mb_strtoupper(trim((string) $shop)) === 'LINE SCC')
            ->unique()
            ->sort()
            ->values();
    }

    private function getToolsWithTypes(string $tableName, int $year): array
    {
        return \Illuminate\Support\Facades\DB::table($tableName)
            ->select('tool_name', 'tool_type')
            ->whereNotNull('tool_type')
            ->where('tool_type', '<>', '')
            ->get()
            ->concat(ProductionData::query()
                ->where('year', $year)
                ->where('is_year_template', false)
                ->whereNotNull('tool_name')
                ->where('tool_name', '<>', '')
                ->whereNotNull('type')
                ->where('type', '<>', '')
                ->get(['tool_name', 'type'])
                ->map(fn (ProductionData $row) => (object) [
                    'tool_name' => $row->tool_name,
                    'tool_type' => $row->type,
                ]))
            ->unique(fn ($row) => mb_strtoupper(trim((string) $row->tool_name) . '|' . trim((string) $row->tool_type)))
            ->groupBy('tool_name')
            ->map(function ($items) {
                return $items->pluck('tool_type')->unique()->values()->toArray();
            })
            ->toArray();
    }

    private function validatedData(Request $request, ?InternalVerificationSchedule $schedule = null): array
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
            'tool_name' => 'required|string|max:255',
            'tool_type' => 'required|string|max:100',
            'tool_type_new' => 'required_if:tool_type,__new|nullable|string|max:100',
            'shop' => 'nullable|string|max:100',
            'shop_new' => 'required_if:shop,__new|nullable|string|max:100',
            'specification' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'period1_verification_date' => 'nullable|date',
            'period1_next_verification_date' => 'nullable|date|after_or_equal:period1_verification_date',
            'period1_jgm' => 'nullable|string|max:30',
            'period2_verification_date' => 'nullable|date',
            'period2_next_verification_date' => 'nullable|date|after_or_equal:period2_verification_date',
            'period2_jgm' => 'nullable|string|max:30',
        ]);

        if ($validated['tool_type'] === '__new') {
            $validated['tool_type'] = trim($validated['tool_type_new']);
        }
        unset($validated['tool_type_new']);

        if (isset($validated['shop']) && $validated['shop'] === '__new') {
            $validated['shop'] = trim($validated['shop_new'] ?? '');
        }
        unset($validated['shop_new']);

        $validated['tool_type'] = mb_strtoupper(trim($validated['tool_type']), 'UTF-8');
        if (!empty($validated['shop'])) $validated['shop'] = mb_strtoupper(trim($validated['shop']), 'UTF-8');

        return $validated;
    }

    private function validatedQc2Data(Request $request): array
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
            'tool_name' => 'required|string|max:255',
            'tool_type' => 'required|string|max:100',
            'tool_type_new' => 'required_if:tool_type,__new|nullable|string|max:100',
            'shop' => 'nullable|string|max:100',
            'shop_new' => 'required_if:shop,__new|nullable|string|max:100',
            'specification' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'period1_verification_date' => 'nullable|date',
            'period1_next_verification_date' => 'nullable|date|after_or_equal:period1_verification_date',
            'period1_jgm' => 'nullable|string|max:30',
        ]);

        if ($validated['tool_type'] === '__new') {
            $validated['tool_type'] = trim($validated['tool_type_new']);
        }
        unset($validated['tool_type_new']);

        if (isset($validated['shop']) && $validated['shop'] === '__new') {
            $validated['shop'] = trim($validated['shop_new'] ?? '');
        }
        unset($validated['shop_new']);

        $validated['tool_type'] = mb_strtoupper(trim($validated['tool_type']), 'UTF-8');
        if (!empty($validated['shop'])) $validated['shop'] = mb_strtoupper(trim($validated['shop']), 'UTF-8');

        return $validated;
    }

    private function validatedYear(Request $request): int
    {
        $data = $request->validate(['tahun' => 'nullable|integer|between:2000,2100']);
        return (int) ($data['tahun'] ?? date('Y'));
    }

    private function validatedCompany(Request $request): string
    {
        return $request->validate(['company' => 'nullable|in:HAMATETSU,NISSO GIKEN'])['company'] ?? 'HAMATETSU';
    }

    private function validatedCalibrationData(Request $request): array
    {
        $data = $request->validate([
            'tahun' => 'required|digits:4',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
            'tool_name' => 'required|string|max:255',
            'tool_type' => 'required|string|max:100',
            'tool_type_new' => 'required_if:tool_type,__new|nullable|string|max:100',
            'shop' => 'nullable|string|max:100',
            'shop_new' => 'required_if:shop,__new|nullable|string|max:100',
            'specification' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'calibration_date' => 'nullable|date',
            'next_calibration_date' => 'nullable|date|after_or_equal:calibration_date',
            'jgm' => 'nullable|string|max:30',
            'period2_calibration_date' => 'nullable|date',
            'period2_next_calibration_date' => 'nullable|date|after_or_equal:period2_calibration_date',
            'period2_jgm' => 'nullable|string|max:30',
        ]);

        if ($data['tool_type'] === '__new') {
            $data['tool_type'] = trim($data['tool_type_new']);
        }
        unset($data['tool_type_new']);

        if (isset($data['shop']) && $data['shop'] === '__new') {
            $data['shop'] = trim($data['shop_new'] ?? '');
        }
        unset($data['shop_new']);

        $data['tool_name'] = mb_strtoupper(trim($data['tool_name']), 'UTF-8');
        $data['tool_type'] = mb_strtoupper(trim($data['tool_type']), 'UTF-8');
        if (!empty($data['shop'])) $data['shop'] = mb_strtoupper(trim($data['shop']), 'UTF-8');

        return $data;
    }
}
