<?php

namespace App\Http\Controllers;

use App\Models\ProductionData;
use App\Models\InternalVerificationSchedule;
use Illuminate\Http\Request;
use App\Exports\ProductionDataExport;
use App\Imports\ProductionDataImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ProductionDataController extends Controller
{
    public function index(Request $request)
    {
        $divisionList = [
            'QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP', 
            'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 
            'P3', 'P5', 'P6', 'P7', 'P8', 'ENG', 'NISSHO'
        ];

        $selectedDivision = $request->get('division', 'ALL'); 
        $searchTool = $request->get('tool_name');

        if ($selectedDivision !== 'ALL' && !in_array($selectedDivision, $divisionList)) {
            $selectedDivision = 'ALL';
        }

        $query = ProductionData::query();

        if ($selectedDivision !== 'ALL') {
            $query->where('division', $selectedDivision);
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

        $data = $query->orderBy($selectedDivision === 'ALL' ? 'division' : 'id', 'asc')
            ->orderBy('id', 'asc')
            ->simplePaginate(100)
            ->withQueryString();

        return view('production-data.index', compact('data', 'divisionList', 'selectedDivision', 'toolNameList', 'searchTool'));
    }

    public function getToolsByDivision(Request $request)
    {
        $division = $request->get('division');
        $query = ProductionData::query();
        
        if ($division && $division !== 'ALL') {
            $query->where('division', $division);
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
        $divisionList = ['QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP', 'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'ENG', 'NISSHO'];
        return view('production-data.create', compact('divisionList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division' => 'required|string', 'tool_name' => 'required|string', 'type' => 'required|string', 'size' => 'required|string',
            'number_seri' => 'nullable|string', 'number_reg' => 'nullable|string', 'part_name' => 'nullable|string', 'part_number' => 'nullable|string', 'note' => 'nullable|string',
        ]);

        // Ambil input bulan apa adanya (bisa angka atau teks)
        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        foreach ($months as $month) {
            $validated[$month] = $request->input($month); // Tidak dipaksa 0
        }

        $productionRow = ProductionData::create($validated);
        InternalVerificationSchedule::syncForProductionRow($productionRow);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('data-all-produksi.create')->with('success', 'Data berhasil disimpan.');
        }
        return redirect()->route('data-all-produksi.index', ['division' => $request->division])->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(ProductionData $dataAllProduksi)
    {
        $divisionList = ['QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP', 'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'ENG', 'NISSHO'];
        return view('production-data.edit', ['item' => $dataAllProduksi, 'divisionList' => $divisionList]);
    }

    public function update(Request $request, ProductionData $dataAllProduksi)
    {
        $validated = $request->validate([
            'division' => 'required|string', 'tool_name' => 'required|string', 'type' => 'required|string', 'size' => 'required|string',
            'number_seri' => 'nullable|string', 'number_reg' => 'nullable|string', 'part_name' => 'nullable|string', 'part_number' => 'nullable|string', 'note' => 'nullable|string',
        ]);

        $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
        foreach ($months as $month) {
            $validated[$month] = $request->input($month);
        }

        $dataAllProduksi->update($validated);
        InternalVerificationSchedule::syncForProductionRow($dataAllProduksi->fresh());
        return redirect()->route('data-all-produksi.index', ['division' => $request->division])->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(ProductionData $dataAllProduksi)
    {
        $division = $dataAllProduksi->division;
        $dataAllProduksi->delete();
        return redirect()->route('data-all-produksi.index', ['division' => $division])->with('success', 'Data berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $division = $request->get('division', 'ALL');
        $filename = $division === 'ALL' ? 'data-all-produksi-semua-divisi-' . date('Ymd') . '.xlsx' : 'data-produksi-' . str_replace(' ', '-', strtolower($division)) . '-' . date('Ymd') . '.xlsx';
        return Excel::download(new ProductionDataExport($division), $filename);
    }

    public function importForm() { return view('production-data.import'); }

    public function importStore(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);
        try {
            $filePath = $request->file('file')->getRealPath();
            DB::transaction(function () use ($filePath, $request): void {
                Excel::import(new ProductionDataImport($filePath), $request->file('file'));
                InternalVerificationSchedule::syncAllProductionRows();
            });
            return redirect()->route('data-all-produksi.index')->with('success', 'Data berhasil di-import!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
