<?php

namespace App\Http\Controllers;

use App\Models\DataAllProduksi;
use Illuminate\Http\Request;
use App\Exports\DataAllProduksiExport;
use App\Imports\DataAllProduksiImport;
use Maatwebsite\Excel\Facades\Excel;

class DataAllProduksiController extends Controller
{
    public function index(Request $request)
    {
        $divisionList = [
            'QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP', 
            'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 
            'P3', 'P5', 'P6', 'P7', 'P8'
        ];

        $selectedDivision = $request->get('division', 'ALL'); 
        $searchTool = $request->get('tool_name');

        if ($selectedDivision !== 'ALL' && !in_array($selectedDivision, $divisionList)) {
            $selectedDivision = 'ALL';
        }

        $query = DataAllProduksi::query();

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

        if ($selectedDivision === 'ALL') {
            $data = $query->orderBy('division', 'asc')->orderBy('id', 'asc')->get();
        } else {
            $data = $query->orderBy('id', 'asc')->get();
        }

        return view('data-all-produksi.index', compact('data', 'divisionList', 'selectedDivision', 'toolNameList', 'searchTool'));
    }

    public function getToolsByDivision(Request $request)
    {
        $division = $request->get('division');
        $query = DataAllProduksi::query();
        
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
        $divisionList = ['QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP', 'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8'];
        return view('data-all-produksi.create', compact('divisionList'));
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

        DataAllProduksi::create($validated);

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('data-all-produksi.create')->with('success', 'Data berhasil disimpan.');
        }
        return redirect()->route('data-all-produksi.index', ['division' => $request->division])->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(DataAllProduksi $dataAllProduksi)
    {
        $divisionList = ['QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP', 'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8'];
        return view('data-all-produksi.edit', ['item' => $dataAllProduksi, 'divisionList' => $divisionList]);
    }

    public function update(Request $request, DataAllProduksi $dataAllProduksi)
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
        return redirect()->route('data-all-produksi.index', ['division' => $request->division])->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(DataAllProduksi $dataAllProduksi)
    {
        $division = $dataAllProduksi->division;
        $dataAllProduksi->delete();
        return redirect()->route('data-all-produksi.index', ['division' => $division])->with('success', 'Data berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $division = $request->get('division', 'ALL');
        $filename = $division === 'ALL' ? 'data-all-produksi-semua-divisi-' . date('Ymd') . '.xlsx' : 'data-produksi-' . str_replace(' ', '-', strtolower($division)) . '-' . date('Ymd') . '.xlsx';
        return Excel::download(new DataAllProduksiExport($division), $filename);
    }

    public function importForm() { return view('data-all-produksi.import'); }

    public function importStore(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv|max:5120']);
        try {
            $filePath = $request->file('file')->getRealPath();
            Excel::import(new DataAllProduksiImport($filePath), $request->file('file'));
            return redirect()->route('data-all-produksi.index')->with('success', 'Data berhasil di-import!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}