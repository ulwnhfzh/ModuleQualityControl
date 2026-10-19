<?php

namespace App\Http\Controllers;

use App\Models\Internal;
use App\Models\Tool;
use App\Models\DokumenCounter;
use App\Support\DocumentPdfLayout;
use App\Imports\InternalImport;
use App\Imports\SpreadsheetHeaderRowDetector;
use App\Exports\InternalExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $schedules = Internal::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('internal.index', compact('schedules', 'tahun'));
    }

    public function create(Request $request)
    {
        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        $tahun = (int) $request->input('tahun', date('Y'));
        return view('internal.create', compact('tools', 'tahun'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);

        $data['qty'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $internal = Internal::create($data);

        $this->logCreate(
            'internal',
            $internal->id,
            $internal->tool_name . ' - Kalibrasi Internal (' . $internal->tahun . ')',
            $internal->toArray()
        );

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('internal.create', ['tahun' => $data['tahun']])
                ->with('success', 'Jadwal berhasil disimpan. Lanjutkan input berikutnya.');
        }

        return redirect()->route('internal.index', ['tahun' => $data['tahun']])
            ->with('success', 'Jadwal kalibrasi internal berhasil ditambahkan!');
    }

    public function edit(Internal $internal)
    {
        return view('internal.edit', compact('internal'));
    }

    public function update(Request $request, Internal $internal)
    {
        $dataLama = $internal->toArray();

        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);

        $data['qty'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $internal->update($data);

        $this->logUpdate(
            'internal',
            $internal->id,
            $internal->tool_name . ' - Kalibrasi Internal (' . $internal->tahun . ')',
            $dataLama,
            $internal->fresh()->toArray()
        );

        return redirect()->route('internal.index')->with('success', 'Jadwal kalibrasi internal berhasil diperbarui!');
    }

    public function destroy(Internal $internal)
    {
        $dataLama = $internal->toArray();
        $internalId = $internal->id;
        $keterangan = $internal->tool_name . ' - Kalibrasi Internal (' . $internal->tahun . ')';

        $internal->delete();

        $this->logDelete('internal', $internalId, $keterangan, $dataLama);

        return redirect()->route('internal.index')->with('success', 'Jadwal kalibrasi internal berhasil dihapus!');
    }

    // ===== IMPORT =====
    public function importForm()
    {
        return view('internal.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
        ]);

        try {
            $rowHeader = SpreadsheetHeaderRowDetector::detect($request->file('file'));
            Excel::import(new InternalImport($request->tahun, $rowHeader), $request->file('file'));

            $this->logActivity(
                'CREATE',
                'internal',
                null,
                'Import Excel Kalibrasi Internal tahun ' . $request->tahun,
                null,
                ['tahun' => $request->tahun, 'file' => $request->file('file')->getClientOriginalName()]
            );

            return redirect()->route('internal.index', ['tahun' => $request->tahun])
                ->with('success', 'Data berhasil di-import untuk tahun ' . $request->tahun);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ===== EXPORT =====
    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $filename = 'internal-' . $tahun . '.xlsx';

        $this->logExport('internal', 'Export Excel Kalibrasi Internal tahun ' . $tahun);

        return Excel::download(new InternalExport($tahun), $filename);
    }

    /**
     * Export PDF dengan nomor dokumen otomatis
     */
    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = Internal::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        // ===== GENERATE NOMOR DOKUMEN =====
        $nomorDokumen = DokumenCounter::generateNomor('INT', $tahun, 'QC');

        // ===== LOG ACTIVITY =====
        $this->logExport('internal', 'Export PDF Kalibrasi Internal - Nomor: ' . $nomorDokumen);

        // ===== GENERATE PDF =====
        $pdf = Pdf::loadView('internal.pdf', compact('data', 'tahun', 'nomorDokumen'))
            ->setPaper('a4', 'landscape');

        DocumentPdfLayout::addPageNumbers($pdf);

        return $pdf->download('internal-' . $tahun . '.pdf');
    }

    public function downloadTemplate()
    {
        $headers = [
            'tool_name',
            'jan', 'feb', 'mar', 'apr', 'may', 'jun',
            'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
        ];

        $contoh = [
            'CONTOH ALAT',
            1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        ];

        $filename = 'template-import-internal.csv';

        $callback = function () use ($headers, $contoh) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            fputcsv($file, $contoh);
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
