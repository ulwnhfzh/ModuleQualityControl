<?php

namespace App\Http\Controllers;

use App\Exports\ExternalCalibrationListUpExport;
use App\Imports\ExternalCalibrationListUpImport;
use App\Imports\AnnualStatusWorkbookImport;
use App\Imports\SpreadsheetImportHeaders;
use App\Imports\SpreadsheetSheetSelector;
use App\Models\ExternalCalibrationListUp;
use App\Models\ProductionData;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExternalCalibrationListUpController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'tool_name' => 'nullable|string|max:255',
            'shop' => 'nullable|string|max:100',
        ]);
        $year = (int) ($filters['tahun'] ?? date('Y'));
        $toolName = trim($filters['tool_name'] ?? '');
        $shop = $filters['shop'] ?? '';
        $toolNameList = $this->productionToolNameOptions($year, $shop);
        if ($toolName !== '' && !$toolNameList->contains($toolName)) {
            $toolName = '';
        }
        $query = ExternalCalibrationListUp::where('tahun', $year);
        $shops = (clone $query)->whereNotNull('shop')->where('shop', '<>', '')
            ->distinct()->orderBy('shop')->pluck('shop');
        $shops = $this->mergeProductionShopOptions($year, $shops);
        if ($toolName !== '') $query->where('tool_name', $toolName);
        if ($shop !== '') $query->where('shop', $shop);
        $schedules = $query->orderBy('tool_name')->orderBy('registration_number')->orderBy('id')
            ->simplePaginate(100)->withQueryString();

        return view('external-calibration-list-up.index', compact('year', 'schedules', 'shops', 'toolNameList', 'toolName', 'shop'));
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
            ->unique()
            ->sort()
            ->values();
    }

    public function create(Request $request)
    {
        $year = (int) ($request->validate(['tahun' => 'nullable|integer|between:2000,2100'])['tahun'] ?? date('Y'));
        $toolNameList = $this->productionToolNameOptions($year, '');
        return view('external-calibration-list-up.form', compact('year', 'toolNameList') + ['item' => new ExternalCalibrationListUp()]);
    }

    public function store(Request $request)
    {
        $item = ExternalCalibrationListUp::create($this->validated($request));
        $this->logCreate('external_calibration_list_up', $item->id, $item->tool_name . ' - External Calibration (' . $item->tahun . ')', $item->toArray());
        return redirect()->route('external-list-up.index', ['tahun' => $item->tahun])->with('success', 'List Up External Calibration berhasil ditambahkan.');
    }

    public function edit(ExternalCalibrationListUp $item)
    {
        $year = $item->tahun;
        $toolNameList = $this->productionToolNameOptions($year, '');
        if (!$toolNameList->contains($item->tool_name)) {
            $toolNameList->prepend($item->tool_name);
        }

        return view('external-calibration-list-up.form', compact('year', 'item', 'toolNameList'));
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
        $annualImport = null;
        try {
            $path = $request->file('file')->getRealPath();
            if (AnnualStatusWorkbookImport::supports($path, 'external_calibration', (int) $data['tahun'])) {
                $annualImport = new AnnualStatusWorkbookImport($path, 'external_calibration', (int) $data['tahun']);
                $annualImport->import($path);
            } else {
                $import = SpreadsheetSheetSelector::forHeaders(
                    $request->file('file'),
                    [
                        SpreadsheetImportHeaders::TOOL_NAME,
                        ['specification', 'spesifikasi'],
                        ['number_seri', 'serial_number', 'nomor_seri'],
                        ['number_registration', 'registration_number', 'number_reg', 'nomor_registrasi'],
                        ['calibration_date', 'tanggal_kalibrasi'],
                        ['next_calibration_date', 'tanggal_kalibrasi_berikutnya'],
                    ],
                    fn (int $headerRow) => new ExternalCalibrationListUpImport((int) $data['tahun'], $headerRow),
                );
                Excel::import($import, $request->file('file'));
            }
            $this->logActivity('CREATE', 'external_calibration_list_up', null, 'Import External Calibration tahun ' . $data['tahun'], null, ['file' => $request->file('file')->getClientOriginalName()]);
            $message = 'Workbook List Up External Calibration berhasil diimpor.';
            if ($annualImport !== null) {
                $message .= ' Baris diproses: ' . $annualImport->getImportedCount()
                    . '; dilewati karena konflik status: ' . $annualImport->getSkippedConflictCount()
                    . '; status OK dipertahankan: ' . $annualImport->getProtectedOkCount()
                    . '; dilewati karena tanggal tidak valid: ' . count($annualImport->getSkippedInvalidRows()) . '.';
            }
            return redirect()->route('external-list-up.index', ['tahun' => $data['tahun']])->with('success', $message);
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