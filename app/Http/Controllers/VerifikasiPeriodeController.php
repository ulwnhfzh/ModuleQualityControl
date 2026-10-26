<?php

namespace App\Http\Controllers;

use App\Models\VerifikasiInternalSchedule;
use App\Models\KalibrasiInternalListUp;
use App\Models\VerifikasiQc2;
use App\Models\Tool;
use App\Exports\VerifikasiPeriodeExport;
use App\Exports\KalibrasiInternalListUpExport;
use App\Imports\VerifikasiPeriodeImport;
use App\Imports\KalibrasiInternalListUpImport;
use App\Imports\VerifikasiQc2Import;
use App\Exports\VerifikasiQc2Export;
use App\Imports\SpreadsheetHeaderRowDetector;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VerifikasiPeriodeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'jenis' => 'nullable|in:kalibrasi,verifikasi,verifikasi_qc2',
        ]);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $jenis = $filters['jenis'] ?? 'verifikasi';

        if ($jenis === 'kalibrasi') {
            $schedules = KalibrasiInternalListUp::where('tahun', $tahun)->orderBy('tool_name')->get();
            return view('verifikasi.periode.kalibrasi', compact('schedules', 'tahun', 'jenis'));
        }

        if ($jenis === 'verifikasi_qc2') {
            $schedules = VerifikasiQc2::where('tahun', $tahun)->orderBy('tool_name')->get();
            return view('verifikasi.periode.verifikasi-qc2', compact('schedules', 'tahun', 'jenis'));
        }

        $schedules = VerifikasiInternalSchedule::query()
            ->where('tahun', $tahun)
            ->orderBy('tool_name')
            ->get();
        $types = VerifikasiInternalSchedule::whereNotNull('tool_type')
            ->where('tool_type', '<>', '')
            ->distinct()
            ->orderBy('tool_type')
            ->pluck('tool_type');

        return view('verifikasi.periode.index', compact('schedules', 'tahun', 'types', 'jenis'));
    }

    // ==========================================
    // METHOD KALIBRASI INTERNAL
    // ==========================================

    public function internalCalibrationCreate(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $tools = Tool::orderBy('tool_name')->get();
        $types = KalibrasiInternalListUp::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = KalibrasiInternalListUp::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
            
        // TAMBAHAN: Mapping Tool -> Types
        $toolsWithTypes = $this->getToolsWithTypes('kalibrasi_internal_list_ups');

        return view('verifikasi.periode.kalibrasi-form', compact('tahun', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function internalCalibrationStore(Request $request)
    {
        $data = $this->validatedCalibrationData($request);
        KalibrasiInternalListUp::create($data);
        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.periode.kalibrasi.create', ['tahun' => $data['tahun']])
                ->with('success', 'Jadwal berhasil disimpan. Lanjutkan input berikutnya.');
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'kalibrasi'])
            ->with('success', 'Jadwal kalibrasi internal berhasil ditambahkan.');
    }

    public function internalCalibrationEdit(KalibrasiInternalListUp $schedule)
    {
        $tahun = $schedule->tahun;
        $tools = Tool::orderBy('tool_name')->get();
        $types = KalibrasiInternalListUp::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = KalibrasiInternalListUp::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $toolsWithTypes = $this->getToolsWithTypes('kalibrasi_internal_list_ups');

        return view('verifikasi.periode.kalibrasi-form', compact('schedule', 'tahun', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function internalCalibrationUpdate(Request $request, KalibrasiInternalListUp $schedule)
    {
        $data = $this->validatedCalibrationData($request);
        $schedule->update($data);
        return redirect()->route('verifikasi.periode.index', ['tahun' => $schedule->tahun, 'jenis' => 'kalibrasi'])
            ->with('success', 'Jadwal kalibrasi internal berhasil diperbarui.');
    }

    public function internalCalibrationDestroy(KalibrasiInternalListUp $schedule)
    {
        $tahun = $schedule->tahun;
        $schedule->delete();
        return redirect()->route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi'])
            ->with('success', 'Jadwal kalibrasi internal berhasil dihapus.');
    }

    public function internalCalibrationImportForm(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return view('verifikasi.periode.kalibrasi-import', compact('tahun'));
    }

    public function internalCalibrationImport(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);
        try {
            $rowHeader = SpreadsheetHeaderRowDetector::detect($request->file('file'));
            Excel::import(new KalibrasiInternalListUpImport((int) $data['tahun'], $rowHeader), $request->file('file'));
        } catch (\Throwable $exception) {
            return back()->withInput()->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'kalibrasi'])
            ->with('success', 'List Up Kalibrasi Internal berhasil diimpor.');
    }

    public function internalCalibrationExport(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return Excel::download(new KalibrasiInternalListUpExport($tahun), "list-up-kalibrasi-internal-{$tahun}.xlsx");
    }

    // ==========================================
    // METHOD VERIFIKASI INTERNAL
    // ==========================================

    public function create(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $types = VerifikasiInternalSchedule::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = VerifikasiInternalSchedule::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $tools = Tool::orderBy('tool_name')->get();
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_internal_schedules');

        return view('verifikasi.periode.form', compact('tahun', 'types', 'shops', 'tools', 'toolsWithTypes'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        VerifikasiInternalSchedule::create($data);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.periode.create', ['tahun' => $data['tahun']])
                ->with('success', 'Jadwal berhasil disimpan. Lanjutkan input berikutnya.');
        }

        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun']])
            ->with('success', 'Jadwal periode berhasil ditambahkan.');
    }

    public function edit(VerifikasiInternalSchedule $schedule)
    {
        $tahun = $schedule->tahun;
        $types = VerifikasiInternalSchedule::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = VerifikasiInternalSchedule::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $tools = Tool::orderBy('tool_name')->get();
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_internal_schedules');

        return view('verifikasi.periode.form', compact('schedule', 'tahun', 'types', 'shops', 'tools', 'toolsWithTypes'));
    }

    public function update(Request $request, VerifikasiInternalSchedule $schedule)
    {
        $schedule->update($this->validatedData($request, $schedule));

        return redirect()->route('verifikasi.periode.index', ['tahun' => $schedule->tahun])
            ->with('success', 'Jadwal periode berhasil diperbarui.');
    }

    public function destroy(VerifikasiInternalSchedule $schedule)
    {
        $tahun = $schedule->tahun;
        $schedule->delete();

        return redirect()->route('verifikasi.periode.index', ['tahun' => $tahun])
            ->with('success', 'Jadwal periode berhasil dihapus.');
    }

    public function importForm(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return view('verifikasi.periode.import', compact('tahun'));
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);
        try {
            $rowHeader = SpreadsheetHeaderRowDetector::detect($request->file('file'));
            Excel::import(new VerifikasiPeriodeImport((int) $data['tahun'], $rowHeader), $request->file('file'));
        } catch (\Throwable $exception) {
            return redirect()->route('verifikasi.periode.import.form', ['tahun' => $data['tahun']])
                ->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }

        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun']])
            ->with('success', 'Data List Up berhasil diimpor.');
    }

    public function export(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return Excel::download(new VerifikasiPeriodeExport($tahun), "list-up-verifikasi-{$tahun}.xlsx");
    }

    // ==========================================
    // METHOD VERIFIKASI QC2
    // ==========================================

    public function qc2Create(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $tools = Tool::orderBy('tool_name')->get();
        $types = VerifikasiQc2::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = VerifikasiQc2::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_qc2');

        return view('verifikasi.periode.form-qc2', compact('tahun', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function qc2Store(Request $request)
    {
        $data = $this->validatedQc2Data($request);
        VerifikasiQc2::create($data);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.qc2.create', ['tahun' => $data['tahun']])
                ->with('success', 'Jadwal QC2 berhasil disimpan. Lanjutkan input berikutnya.');
        }

        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'verifikasi_qc2'])
            ->with('success', 'Jadwal QC2 berhasil ditambahkan.');
    }

    public function qc2Edit(VerifikasiQc2 $schedule)
    {
        $tahun = $schedule->tahun;
        $tools = Tool::orderBy('tool_name')->get();
        $types = VerifikasiQc2::whereNotNull('tool_type')->where('tool_type', '<>', '')
            ->distinct()->orderBy('tool_type')->pluck('tool_type');
        $shops = VerifikasiQc2::whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $toolsWithTypes = $this->getToolsWithTypes('verifikasi_qc2');

        return view('verifikasi.periode.form-qc2', compact('schedule', 'tahun', 'tools', 'types', 'shops', 'toolsWithTypes'));
    }

    public function qc2Update(Request $request, VerifikasiQc2 $schedule)
    {
        $schedule->update($this->validatedQc2Data($request));

        return redirect()->route('verifikasi.periode.index', ['tahun' => $schedule->tahun, 'jenis' => 'verifikasi_qc2'])
            ->with('success', 'Jadwal QC2 berhasil diperbarui.');
    }

    public function qc2Destroy(VerifikasiQc2 $schedule)
    {
        $tahun = $schedule->tahun;
        $schedule->delete();

        return redirect()->route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'verifikasi_qc2'])
            ->with('success', 'Jadwal QC2 berhasil dihapus.');
    }

    public function qc2ImportForm(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return view('verifikasi.periode.qc2-import', compact('tahun'));
    }

    public function qc2Import(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);
        try {
            $rowHeader = SpreadsheetHeaderRowDetector::detect($request->file('file'));
            Excel::import(new VerifikasiQc2Import((int) $data['tahun'], $rowHeader), $request->file('file'));
        } catch (\Throwable $exception) {
            return back()->withInput()->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }
        return redirect()->route('verifikasi.periode.index', ['tahun' => $data['tahun'], 'jenis' => 'verifikasi_qc2'])
            ->with('success', 'Data List Up QC2 berhasil diimpor.');
    }

    public function qc2ExportExcel(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return Excel::download(new VerifikasiQc2Export($tahun), "list-up-qc2-{$tahun}.xlsx");
    }

    // ==========================================
    // METHOD PRIVATE UNTUK VALIDASI & HELPER
    // ==========================================

    /**
     * Mengambil mapping Tool Name => array of Types
     */
    private function getToolsWithTypes($tableName)
    {
        return \Illuminate\Support\Facades\DB::table($tableName)
            ->select('tool_name', 'tool_type')
            ->whereNotNull('tool_type')
            ->where('tool_type', '<>', '')
            ->distinct()
            ->get()
            ->groupBy('tool_name')
            ->map(function ($items) {
                return $items->pluck('tool_type')->unique()->values()->toArray();
            })
            ->toArray();
    }

    private function validatedData(Request $request, ?VerifikasiInternalSchedule $schedule = null): array
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
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

    private function validatedCalibrationData(Request $request): array
    {
        $data = $request->validate([
            'tahun' => 'required|digits:4',
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