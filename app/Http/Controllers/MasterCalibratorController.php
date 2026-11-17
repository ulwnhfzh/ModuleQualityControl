<?php

namespace App\Http\Controllers;

use App\Models\MasterCalibrator;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MasterCalibratorImport;
use App\Imports\SpreadsheetSheetSelector;
use App\Exports\MasterCalibratorExport;
use Barryvdh\DomPDF\Facade\Pdf;

class MasterCalibratorController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $filters = $request->validate(['tahun' => 'nullable|integer|between:2000,2100']);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $schedules = MasterCalibrator::where('tahun', $tahun)->orderBy('tool_name')->orderBy('size')->get();

        return view('master-calibrator.index', compact('tahun', 'schedules'));
    }

    public function create(Request $request)
    {
        $tahun = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        return view('master-calibrator.form', ['tahun' => $tahun, 'item' => new MasterCalibrator()]);
    }

    public function store(Request $request)
    {
        $item = MasterCalibrator::create($this->validated($request));
        $this->logCreate('master_calibrator', $item->id, $item->tool_name . ' - Master Kalibrator (' . $item->tahun . ')', $item->toArray());
        return redirect()->route('master-calibrator.index', ['tahun' => $item->tahun])->with('success', 'Data Master Kalibrator berhasil ditambahkan.');
    }

    public function edit(MasterCalibrator $masterCalibrator)
    {
        return view('master-calibrator.form', ['tahun' => $masterCalibrator->tahun, 'item' => $masterCalibrator]);
    }

    public function update(Request $request, MasterCalibrator $masterCalibrator)
    {
        $before = $masterCalibrator->toArray();
        $masterCalibrator->update($this->validated($request));
        $this->logUpdate('master_calibrator', $masterCalibrator->id, $masterCalibrator->tool_name . ' - Master Kalibrator (' . $masterCalibrator->tahun . ')', $before, $masterCalibrator->fresh()->toArray());
        return redirect()->route('master-calibrator.index', ['tahun' => $masterCalibrator->tahun])->with('success', 'Data Master Kalibrator berhasil diperbarui.');
    }

    public function destroy(MasterCalibrator $masterCalibrator)
    {
        $before = $masterCalibrator->toArray();
        $id = $masterCalibrator->id;
        $year = $masterCalibrator->tahun;
        $this->logDelete('master_calibrator', $id, $masterCalibrator->tool_name . ' - Master Kalibrator (' . $year . ')', $before);
        $masterCalibrator->delete();
        return redirect()->route('master-calibrator.index', ['tahun' => $year])->with('success', 'Data Master Kalibrator berhasil dihapus.');
    }

    public function importForm(Request $request)
    {
        $tahun = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));

        return view('master-calibrator.import', compact('tahun'));
    }

    public function import(Request $request)
    {
        $data = $request->validate(['tahun' => 'required|integer|between:2000,2100', 'file' => 'required|file|mimes:xls,xlsx|max:10240']);
        $sidebarContext = $request->input('sidebar_context') === 'external-list-up'
            ? ['sidebar_context' => 'external-list-up']
            : [];

        try {
            $import = SpreadsheetSheetSelector::forHeaders(
                $request->file('file'),
                [
                    ['tool_name', 'nama_alat', 'nama_alat_ukur'],
                    ['type', 'tipe'],
                    ['size', 'ukuran'],
                    ['number_reg', 'number_registration', 'nomor_registrasi'],
                    ['jan', 'january', 'januari'],
                ],
                fn (int $headerRow) => new MasterCalibratorImport((int) $data['tahun'], $headerRow + 1),
            );
            Excel::import($import, $request->file('file'));
            $this->logActivity('CREATE', 'master_calibrator', null, 'Import Master Kalibrator tahun ' . $data['tahun'], null, ['file' => $request->file('file')->getClientOriginalName()]);
            return redirect()->route('master-calibrator.index', ['tahun' => $data['tahun']] + $sidebarContext)->with('success', 'Workbook Master Kalibrator berhasil diimpor.');
        } catch (\Throwable $exception) {
            return redirect()->route('master-calibrator.index', ['tahun' => $data['tahun']] + $sidebarContext)->with('error', 'Import gagal: ' . $exception->getMessage());
        }
    }

    public function export(Request $request)
    {
        $tahun = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        $this->logExport('master_calibrator', 'Export Master Kalibrator tahun ' . $tahun);
        return Excel::download(new MasterCalibratorExport($tahun), "master-kalibrator-{$tahun}.xlsx");
    }

    public function exportPdf(Request $request)
    {
        $tahun = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        $schedules = MasterCalibrator::where('tahun', $tahun)->orderBy('tool_name')->orderBy('size')->get();

        $this->logExport('master_calibrator', 'Export PDF Master Kalibrator tahun ' . $tahun);

        return Pdf::loadView('master-calibrator.pdf', compact('tahun', 'schedules'))
            ->setPaper('a3', 'landscape')
            ->download("master-kalibrator-{$tahun}.pdf");
    }

    private function validated(Request $request): array
    {
        $rules = [
            'tahun' => 'required|integer|between:2000,2100',
            'tool_name' => 'required|string|max:150',
            'type' => 'nullable|string|max:100', 'size' => 'nullable|string|max:150',
            'number_seri' => 'nullable|string|max:100', 'number_reg' => 'nullable|string|max:100',
            'part_name' => 'nullable|string|max:150', 'part_number' => 'nullable|string|max:150',
            'note' => 'nullable|string|max:5000',
        ];
        foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $month) {
            $rules[$month] = 'nullable|string|max:30';
        }
        $data = $request->validate($rules);
        $data['tool_name'] = strtoupper($data['tool_name']);
        return $data;
    }
}
