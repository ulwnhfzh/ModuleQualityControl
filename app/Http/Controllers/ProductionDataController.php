<?php

namespace App\Http\Controllers;

use App\Models\ProductionData;
use App\Models\InternalVerificationSchedule;
use App\Services\ListUpNgStatusSynchronizer;
use Illuminate\Http\Request;
use App\Exports\ProductionDataExport;
use App\Imports\ProductionDataImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ProductionDataController extends Controller
{
    private const DIVISIONS = [
        'QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP',
        'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2',
        'P3', 'P5', 'P6', 'P7', 'P8', 'ENG', 'NISSHO',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'year' => 'nullable|integer|between:2000,2100',
        ]);

        $divisionList = self::DIVISIONS;
        $selectedYear = (int) ($filters['year'] ?? date('Y'));
        $yearOptions = $this->yearOptions($selectedYear);
        $selectedDivision = $request->get('division', 'ALL');
        $selectedCategory = $request->get('category', 'ALL');
        $searchTool = $request->get('tool_name');
        $categories = ProductionData::query()
            ->where('year', $selectedYear)
            ->where('is_year_template', false)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        if ($selectedDivision !== 'ALL' && !in_array($selectedDivision, $divisionList)) {
            $selectedDivision = 'ALL';
        }
        if ($selectedCategory !== 'ALL' && ! $categories->contains($selectedCategory)) {
            $selectedCategory = 'ALL';
        }

        $query = ProductionData::query()
            ->where('year', $selectedYear)
            ->where('is_year_template', false);

        if ($selectedDivision !== 'ALL') {
            $query->where('division', $selectedDivision);
        }
        if ($selectedCategory !== 'ALL') {
            $query->where('category', $selectedCategory);
        }

        $toolNameQuery = clone $query; 
        $toolNameList = $toolNameQuery->whereNotNull('tool_name')
            ->where('tool_name', '<>', '')
            ->distinct()
            ->orderBy('tool_name')
            ->pluck('tool_name');

        if ($searchTool && in_array($searchTool, $toolNameList->toArray())) {
            $query->where('tool_name', $searchTool);
        }

        if ($selectedDivision === 'ALL') {
            $divisionOrder = implode(' ', array_map(
                fn (int $position): string => 'WHEN ? THEN ' . $position,
                array_keys($divisionList),
            ));
            $query->orderByRaw(
                'CASE division ' . $divisionOrder . ' ELSE ' . count($divisionList) . ' END',
                $divisionList,
            );
        }

        $data = $query->orderBy('id')->simplePaginate(100)->withQueryString();

        return view('production-data.index', compact('data', 'divisionList', 'selectedDivision', 'selectedCategory', 'categories', 'toolNameList', 'searchTool', 'selectedYear', 'yearOptions'));
    }

    public function getToolsByDivision(Request $request)
    {
        $division = $request->get('division');
        $year = (int) $request->get('year', date('Y'));
        $query = ProductionData::query()
            ->where('year', $year)
            ->where('is_year_template', false);
        
        if ($division && $division !== 'ALL') {
            $query->where('division', $division);
        }
        $category = $request->get('category');
        if ($category && $category !== 'ALL') {
            $query->where('category', $category);
        }

        $tools = $query->whereNotNull('tool_name')
            ->where('tool_name', '<>', '')
            ->distinct()
            ->orderBy('tool_name')
            ->pluck('tool_name');

        return response()->json($tools);
    }

    public function create()
    {
        $divisionList = self::DIVISIONS;
        $selectedYear = (int) old('year', request()->get('year', date('Y')));
        $yearOptions = $this->yearOptions($selectedYear);

        return view('production-data.create', compact('divisionList', 'selectedYear', 'yearOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division' => 'required|string', 'category' => 'required|string|max:255', 'year' => 'required|integer|between:2000,2100',
            'tool_name' => 'required|string', 'type' => 'required|string', 'size' => 'required|string',
            'number_seri' => 'nullable|string', 'number_reg' => 'nullable|string', 'part_name' => 'nullable|string', 'part_number' => 'nullable|string', 'note' => 'nullable|string',
        ]);
        $validated['category'] = $this->normalizeProductionCategory($validated['category']);
        $validated['is_year_template'] = false;

        // Ambil input bulan apa adanya (bisa angka atau teks)
        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        foreach ($months as $month) {
            $validated[$month] = $request->input($month); // Tidak dipaksa 0
        }

        $productionRow = ProductionData::create($validated);
        InternalVerificationSchedule::syncForProductionRow($productionRow);
        app(ListUpNgStatusSynchronizer::class)->syncTarget('production', $productionRow->id);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('data-all-produksi.create', ['year' => $validated['year']])->with('success', 'Data berhasil disimpan.');
        }
        return redirect()->route('data-all-produksi.index', [
            'division' => $request->division,
            'year' => $validated['year'],
        ])->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(ProductionData $dataAllProduksi)
    {
        $divisionList = self::DIVISIONS;
        $selectedYear = (int) old('year', $dataAllProduksi->year);
        $yearOptions = $this->yearOptions($selectedYear);

        return view('production-data.edit', compact('dataAllProduksi', 'divisionList', 'selectedYear', 'yearOptions'))
            ->with('item', $dataAllProduksi);
    }

    public function update(Request $request, ProductionData $dataAllProduksi)
    {
        $validated = $request->validate([
            'division' => 'required|string', 'category' => 'required|string|max:255', 'year' => 'required|integer|between:2000,2100',
            'tool_name' => 'required|string', 'type' => 'required|string', 'size' => 'required|string',
            'number_seri' => 'nullable|string', 'number_reg' => 'nullable|string', 'part_name' => 'nullable|string', 'part_number' => 'nullable|string', 'note' => 'nullable|string',
        ]);
        $validated['category'] = $this->normalizeProductionCategory($validated['category']);
        $validated['is_year_template'] = false;

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        foreach ($months as $month) {
            $validated[$month] = $request->input($month);
        }

        $dataAllProduksi->update($validated);
        InternalVerificationSchedule::syncForProductionRow($dataAllProduksi->fresh());
        app(ListUpNgStatusSynchronizer::class)->syncTarget('production', $dataAllProduksi->id);
        return redirect()->route('data-all-produksi.index', [
            'division' => $request->division,
            'year' => $validated['year'],
        ])->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(ProductionData $dataAllProduksi)
    {
        $division = $dataAllProduksi->division;
        $year = $dataAllProduksi->year;
        $dataAllProduksi->delete();
        app(ListUpNgStatusSynchronizer::class)->syncTarget('production', $dataAllProduksi->id);
        return redirect()->route('data-all-produksi.index', ['division' => $division, 'year' => $year])->with('success', 'Data berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'year' => 'nullable|integer|between:2000,2100',
        ]);
        $division = $request->get('division', 'ALL');
        $category = $request->get('category');
        if ($category === 'ALL') {
            $category = null;
        }
        $year = (int) ($filters['year'] ?? date('Y'));
        $filename = $division === 'ALL'
            ? "data-all-produksi-{$year}-semua-divisi-" . date('Ymd') . '.xlsx'
            : 'data-produksi-' . str_replace(' ', '-', strtolower($division)) . "-{$year}-" . date('Ymd') . '.xlsx';

        return Excel::download(new ProductionDataExport($division, $category, $year), $filename);
    }

    public function importForm(Request $request)
    {
        $selectedYear = (int) old('year', $request->get('year', date('Y')));
        $categories = ProductionData::query()
            ->where('is_year_template', false)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        $yearOptions = $this->yearOptions($selectedYear);

        return view('production-data.import', compact('categories', 'selectedYear', 'yearOptions'));
    }

    public function importStore(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            'category' => 'required|string|max:255',
            'year' => 'required|integer|between:2000,2100',
        ]);
        try {
            $filePath = $request->file('file')->getRealPath();
            $import = new ProductionDataImport($filePath, $validated['category'], (int) $validated['year']);
            if ($import->getSelectedSheetCount() === 0) {
                throw new \RuntimeException('Workbook tidak memiliki sheet division yang dikenali untuk Data Produksi. Tidak ada data yang diimpor.');
            }

            DB::transaction(function () use ($import, $request, $validated): void {
                Excel::import($import, $request->file('file'));
                if ($import->getImportedCount() === 0) {
                    throw new \RuntimeException('Tidak ada baris alat yang dapat dipetakan dari workbook.');
                }
                InternalVerificationSchedule::syncAllProductionRows();
                app(ListUpNgStatusSynchronizer::class)->syncYear((int) $validated['year']);
            });
            return redirect()->route('data-all-produksi.index', ['year' => $validated['year']])->with('success', 'Data berhasil di-import!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * @return array<int, int>
     */
    private function yearOptions(?int $selectedYear = null): array
    {
        $currentYear = (int) date('Y');
        $years = range($currentYear, 2014);
        if ($selectedYear !== null && ! in_array($selectedYear, $years, true)) {
            $years[] = $selectedYear;
            rsort($years);
        }

        return $years;
    }

    private function normalizeProductionCategory(string $category): string
    {
        if (preg_match('/^DATA ALAT UKUR ALL PRODUKSI(?:\s+TAHUN\s+20\d{2})?$/i', trim($category)) === 1) {
            return 'DATA ALAT UKUR ALL PRODUKSI';
        }

        return trim($category);
    }
}
