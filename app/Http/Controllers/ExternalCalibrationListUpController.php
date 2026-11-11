<?php

namespace App\Http\Controllers;

use App\Exports\ExternalCalibrationListUpExport;
use App\Imports\ExternalCalibrationListUpImport;
use App\Imports\SpreadsheetHeaderRowDetector;
use App\Models\ExternalCalibrationListUp;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExternalCalibrationListUpController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $year = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        $schedules = ExternalCalibrationListUp::where('tahun', $year)->orderBy('tool_name')->orderBy('registration_number')->get();
        return view('external-calibration-list-up.index', compact('year', 'schedules'));
    }

    public function create(Request $request)
    {
        $year = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        return view('external-calibration-list-up.form', ['year' => $year, 'item' => new ExternalCalibrationListUp()]);
    }

    public function store(Request $request)
    {
        $item = ExternalCalibrationListUp::create($this->validated($request));
        $this->logCreate('external_calibration_list_up', $item->id, $item->tool_name . ' - External Calibration (' . $item->tahun . ')', $item->toArray());
        return redirect()->route('external-list-up.index', ['tahun' => $item->tahun])->with('success', 'List Up External Calibration berhasil ditambahkan.');
    }

    public function edit(ExternalCalibrationListUp $item)
    {
        return view('external-calibration-list-up.form', ['year' => $item->tahun, 'item' => $item]);
    }

    public function update(Request $request, ExternalCalibrationListUp $item)
    {
        $before = $item->toArray();
        $item->update($this->validated($request));
        $this->logUpdate('external_calibration_list_up', $item->id, $item->tool_name . ' - External Calibration (' . $item->tahun . ')', $before, $item->fresh()->toArray());
        return redirect()->route('external-list-up.index', ['tahun' => $item->tahun])->with('success', 'List Up External Calibration berhasil diperbarui.');
    }

    public function destroy(ExternalCalibrationListUp $item)
    {
        $year = $item->tahun;
        $this->logDelete('external_calibration_list_up', $item->id, $item->tool_name . ' - External Calibration (' . $year . ')', $item->toArray());
        $item->delete();
        return redirect()->route('external-list-up.index', ['tahun' => $year])->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Menampilkan halaman form upload file Excel.
     */
    public function importForm(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        return view('external-calibration-list-up.import', compact('tahun'));
    }

    public function import(Request $request)
    {
        $data = $request->validate(['tahun' => 'required|integer|between:2000,2100', 'file' => 'required|file|mimes:xls,xlsx|max:10240']);
        try {
            $headerRow = SpreadsheetHeaderRowDetector::detect($request->file('file'));
            Excel::import(new ExternalCalibrationListUpImport((int) $data['tahun'], $headerRow), $request->file('file'));
            $this->logActivity('CREATE', 'external_calibration_list_up', null, 'Import External Calibration tahun ' . $data['tahun'], null, ['file' => $request->file('file')->getClientOriginalName()]);
            return redirect()->route('external-list-up.index', ['tahun' => $data['tahun']])->with('success', 'Workbook List Up External Calibration berhasil diimpor.');
        } catch (\Throwable $exception) {
            return redirect()->route('external-list-up.index', ['tahun' => $data['tahun']])->with('error', 'Import gagal: ' . $exception->getMessage());
        }
    }

    public function export(Request $request)
    {
        $year = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        $this->logExport('external_calibration_list_up', 'Export List Up External Calibration tahun ' . $year);
        return Excel::download(new ExternalCalibrationListUpExport($year), "list-up-external-calibration-{$year}.xlsx");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'tahun' => 'required|integer|between:2000,2100',
            'tool_name' => 'required|string|max:150',
            'specification' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'shop' => 'nullable|string|max:100',
            'calibration_date' => 'nullable|date',
            'next_calibration_date' => 'nullable|date|after_or_equal:calibration_date',
            'jgm' => 'nullable|string|max:30',
            'calibration_count' => 'nullable|integer|min:0|max:2147483647',
            'note' => 'nullable|string|max:5000',
        ]);
        $data['tool_name'] = mb_strtoupper($data['tool_name']);
        return $data;
    }
}