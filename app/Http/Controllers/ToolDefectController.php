<?php

namespace App\Http\Controllers;

use App\Models\ToolDefect;
use App\Imports\ToolDefectsImport;
use App\Exports\ToolDefectsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ToolDefectController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'bulan' => 'nullable|in:' . implode(',', ToolDefect::MONTHS),
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $bulanFilter = $filters['bulan'] ?? 'DEC';

        $jenisKerusakan = ['aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 'min_std', 'over_std', 'patah', 'rusak', 'seret', 'lain_lain'];

        $rawData = ToolDefect::where('tahun', $tahun)->where('bulan', $bulanFilter)->get();

        $dataPerDivisi = [];
        foreach ($rawData as $row) {
            if (!isset($dataPerDivisi[$row->division])) {
                $dataPerDivisi[$row->division] = array_fill_keys($jenisKerusakan, 0);
                $dataPerDivisi[$row->division]['total'] = 0;
                $dataPerDivisi[$row->division]['grand_total'] = 0;
            }
            foreach ($jenisKerusakan as $jenis) {
                $dataPerDivisi[$row->division][$jenis] += $row->$jenis;
                $dataPerDivisi[$row->division]['total'] += $row->$jenis;
            }
        }

        foreach ($dataPerDivisi as $divisi => &$data) {
            $grandTotal = ToolDefect::where('tahun', $tahun)
                ->where('division', $divisi)
                ->sum(DB::raw('aus + erorr + gompal + hilang + karat + macet + min_std + over_std + patah + rusak + seret + lain_lain'));
            $data['grand_total'] = $grandTotal;
        }

        $urutanDivisi = [
            'ENGINEERING', 'L.SCC', 'PRODUKSI 1', 'PRODUKSI 2', 'PRODUKSI 3', 
            'PRODUKSI 5', 'PRODUKSI 6', 'PRODUKSI 7', 'PRODUKSI 8', 
            'PPIC 1', 'PPIC 2', 'QA INCOMING', 'QA INSPECTION', 'QA 1', 
            'QA 1.2', 'QA 2', 'NISSHO'
        ];
        
        $sortedData = [];
        foreach ($urutanDivisi as $div) {
            if (isset($dataPerDivisi[$div])) {
                $sortedData[$div] = $dataPerDivisi[$div];
            } else {
                $sortedData[$div] = array_fill_keys($jenisKerusakan, 0);
                $sortedData[$div]['total'] = 0;
                $sortedData[$div]['grand_total'] = 0;
            }
        }

        $footerTotals = array_fill_keys($jenisKerusakan, 0);
        $footerTotals['total'] = 0;
        $footerTotals['grand_total'] = 0;

        foreach ($sortedData as $data) {
            foreach ($jenisKerusakan as $jenis) {
                $footerTotals[$jenis] += $data[$jenis];
            }
            $footerTotals['total'] += $data['total'];
            $footerTotals['grand_total'] += $data['grand_total'];
        }

        return view('defects.index', compact(
            'tahun', 'bulanFilter', 'jenisKerusakan', 'sortedData', 'footerTotals'
        ));
    }

    public function create()
    {
        return view('defects.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'tahun' => 'required|integer|between:2000,2100',
            'bulan' => 'required|in:' . implode(',', ToolDefect::MONTHS),
            'division' => 'required|in:' . implode(',', ToolDefect::DIVISIONS),
        ];
        foreach (ToolDefect::COUNT_FIELDS as $field) {
            $rules[$field] = 'nullable|integer|min:0|max:2147483647';
        }
        $data = $request->validate($rules);

        foreach (['aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 'min_std', 'over_std', 'patah', 'rusak', 'seret', 'lain_lain'] as $col) {
            $data[$col] = $data[$col] ?? 0;
        }

        ToolDefect::create($data);
        return redirect()->route('defects.index')->with('success', 'Data NG berhasil ditambahkan!');
    }

    // ===== FUNGSI BARU: EDIT =====
    public function edit(Request $request, ToolDefect $defect)
    {
        $tahun = $request->get('tahun', $defect->tahun);
        $bulan = $request->get('bulan', $defect->bulan);
        return view('defects.edit', compact('defect', 'tahun', 'bulan'));
    }

    // ===== FUNGSI BARU: UPDATE =====
    public function update(Request $request, ToolDefect $defect)
    {
        $rules = [
            'tahun' => 'required|integer|between:2000,2100',
            'bulan' => 'required|in:' . implode(',', ToolDefect::MONTHS),
            'division' => 'required|in:' . implode(',', ToolDefect::DIVISIONS),
        ];
        foreach (ToolDefect::COUNT_FIELDS as $field) {
            $rules[$field] = 'nullable|integer|min:0|max:2147483647';
        }
        $data = $request->validate($rules);

        foreach (['aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 'min_std', 'over_std', 'patah', 'rusak', 'seret', 'lain_lain'] as $col) {
            $data[$col] = $data[$col] ?? 0;
        }

        $defect->update($data);
        return redirect()->route('defects.index', ['tahun' => $data['tahun'], 'bulan' => $data['bulan']])
            ->with('success', 'Data NG berhasil diperbarui!');
    }

    // ===== FUNGSI BARU: DESTROY (HAPUS) =====
    public function destroy(Request $request, ToolDefect $defect)
    {
        $tahun = $defect->tahun;
        $bulan = $defect->bulan;
        $defect->delete();

        return redirect()->route('defects.index', ['tahun' => $tahun, 'bulan' => $bulan])
            ->with('success', 'Data NG berhasil dihapus!');
    }

    public function importForm()
    {
        return view('defects.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
            'tahun' => 'required|integer|between:2000,2100',
        ]);

        try {
            $filePath = $request->file('file')->getRealPath();
            $import = new ToolDefectsImport((int) $request->tahun);
            $import->import($filePath);

            return redirect()->route('defects.index', ['tahun' => $request->tahun])
                ->with('success', 'Data NG berhasil di-import otomatis dari semua bulan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'bulan' => 'nullable|in:' . implode(',', ToolDefect::MONTHS),
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        return Excel::download(new ToolDefectsExport((int) $tahun), 'Data-NG-' . $tahun . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'bulan' => 'nullable|in:' . implode(',', ToolDefect::MONTHS),
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $bulan = $filters['bulan'] ?? 'DEC';
        
        $jenisKerusakan = ['aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 'min_std', 'over_std', 'patah', 'rusak', 'seret', 'lain_lain'];
        $rawData = ToolDefect::where('tahun', $tahun)->where('bulan', $bulan)->get();
        
        $dataPerDivisi = [];
        foreach ($rawData as $row) {
            if (!isset($dataPerDivisi[$row->division])) {
                $dataPerDivisi[$row->division] = array_fill_keys($jenisKerusakan, 0);
                $dataPerDivisi[$row->division]['total'] = 0;
            }
            foreach ($jenisKerusakan as $jenis) {
                $dataPerDivisi[$row->division][$jenis] += $row->$jenis;
                $dataPerDivisi[$row->division]['total'] += $row->$jenis;
            }
        }

        $pdf = Pdf::loadView('defects.pdf', compact('tahun', 'bulan', 'dataPerDivisi', 'jenisKerusakan'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Data-NG-' . $bulan . '-' . $tahun . '.pdf');
    }
}