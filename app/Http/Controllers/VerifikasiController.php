<?php

namespace App\Http\Controllers;

use App\Models\Verifikasi;
use App\Models\Tool;
use App\Imports\VerifikasiImport;
use App\Exports\VerifikasiExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    use LogsActivity;

    // ==========================================
    // TAMPILKAN TABEL
    // ==========================================
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $mode = $request->input('mode', 'bulanan');

        if ($mode == 'harian') {
            $schedules = Verifikasi::where('tahun', $tahun)
                ->where('tipe', 'harian')
                ->orderBy('tool_name', 'asc')
                ->get();
        } else {
            $schedules = Verifikasi::where('tahun', $tahun)
                ->where('tipe', 'bulanan')
                ->orderBy('tool_name', 'asc')
                ->get();
        }

        return view('verifikasi.index', compact('schedules', 'tahun', 'mode'));
    }

    // ==========================================
    // FORM TAMBAH
    // ==========================================
    public function create(Request $request)
    {
        $mode = $request->input('mode', 'bulanan');

        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('verifikasi.create', compact('tools', 'mode'));
    }

    // ==========================================
    // SIMPAN DATA
    // ==========================================
    public function store(Request $request)
    {
        $mode = $request->input('tipe', 'bulanan');
        $data['tool_name'] = strtoupper($request->tool_name);
        $data['tahun'] = $request->tahun;
        $data['tipe'] = $mode;

        if ($mode == 'harian') {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'tanggal' => 'required|date',
                'qa_1' => 'integer|min:0', 'qa_1_2' => 'integer|min:0', 'qa_inc' => 'integer|min:0', 'qa_insp' => 'integer|min:0',
                'ppic_1' => 'integer|min:0', 'ppic_2' => 'integer|min:0', 'line_scc' => 'integer|min:0',
                'p1' => 'integer|min:0', 'p2' => 'integer|min:0', 'p3' => 'integer|min:0', 'p5' => 'integer|min:0',
                'p6' => 'integer|min:0', 'p7' => 'integer|min:0', 'p8' => 'integer|min:0', 'eng' => 'integer|min:0',
            ]);

            $data['tanggal'] = $request->tanggal;
            $data['qa_1'] = $request->qa_1 ?? 0;
            $data['qa_1_2'] = $request->qa_1_2 ?? 0;
            $data['qa_inc'] = $request->qa_inc ?? 0;
            $data['qa_insp'] = $request->qa_insp ?? 0;
            $data['ppic_1'] = $request->ppic_1 ?? 0;
            $data['ppic_2'] = $request->ppic_2 ?? 0;
            $data['line_scc'] = $request->line_scc ?? 0;
            $data['p1'] = $request->p1 ?? 0;
            $data['p2'] = $request->p2 ?? 0;
            $data['p3'] = $request->p3 ?? 0;
            $data['p5'] = $request->p5 ?? 0;
            $data['p6'] = $request->p6 ?? 0;
            $data['p7'] = $request->p7 ?? 0;
            $data['p8'] = $request->p8 ?? 0;
            $data['eng'] = $request->eng ?? 0;
            $data['total'] = array_sum([
                $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
                $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
                $data['p1'], $data['p2'], $data['p3'], $data['p5'],
                $data['p6'], $data['p7'], $data['p8'], $data['eng']
            ]);
        } else {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
                'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
                'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
            ]);

            $data['jan'] = $request->jan ?? 0;
            $data['feb'] = $request->feb ?? 0;
            $data['mar'] = $request->mar ?? 0;
            $data['apr'] = $request->apr ?? 0;
            $data['may'] = $request->may ?? 0;
            $data['jun'] = $request->jun ?? 0;
            $data['jul'] = $request->jul ?? 0;
            $data['aug'] = $request->aug ?? 0;
            $data['sep'] = $request->sep ?? 0;
            $data['oct'] = $request->oct ?? 0;
            $data['nov'] = $request->nov ?? 0;
            $data['dec'] = $request->dec ?? 0;
            $data['total'] = array_sum([
                $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
                $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
            ]);
        }

        $verifikasi = Verifikasi::create($data);

        // ===== LOG ACTIVITY =====
        $this->logCreate(
            'verifikasi',
            $verifikasi->id,
            $verifikasi->tool_name . ' - Mode ' . strtoupper($mode) . ' (' . $verifikasi->tahun . ')',
            $verifikasi->toArray()
        );

        return redirect()->route('verifikasi.index', ['tahun' => $data['tahun'], 'mode' => $mode])
            ->with('success', 'Jadwal verifikasi berhasil ditambahkan!');
    }

    // ==========================================
    // FORM EDIT
    // ==========================================
    public function edit(Verifikasi $verifikasi)
    {
        return view('verifikasi.edit', compact('verifikasi'));
    }

    // ==========================================
    // UPDATE
    // ==========================================
    public function update(Request $request, Verifikasi $verifikasi)
    {
        // Simpan data lama SEBELUM update
        $dataLama = $verifikasi->toArray();

        $data['tool_name'] = strtoupper($request->tool_name);
        $data['tahun'] = $request->tahun;

        if ($verifikasi->tipe == 'harian') {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'tanggal' => 'required|date',
                'qa_1' => 'integer|min:0', 'qa_1_2' => 'integer|min:0', 'qa_inc' => 'integer|min:0', 'qa_insp' => 'integer|min:0',
                'ppic_1' => 'integer|min:0', 'ppic_2' => 'integer|min:0', 'line_scc' => 'integer|min:0',
                'p1' => 'integer|min:0', 'p2' => 'integer|min:0', 'p3' => 'integer|min:0', 'p5' => 'integer|min:0',
                'p6' => 'integer|min:0', 'p7' => 'integer|min:0', 'p8' => 'integer|min:0', 'eng' => 'integer|min:0',
            ]);

            $data['tanggal'] = $request->tanggal;
            $data['qa_1'] = $request->qa_1 ?? 0;
            $data['qa_1_2'] = $request->qa_1_2 ?? 0;
            $data['qa_inc'] = $request->qa_inc ?? 0;
            $data['qa_insp'] = $request->qa_insp ?? 0;
            $data['ppic_1'] = $request->ppic_1 ?? 0;
            $data['ppic_2'] = $request->ppic_2 ?? 0;
            $data['line_scc'] = $request->line_scc ?? 0;
            $data['p1'] = $request->p1 ?? 0;
            $data['p2'] = $request->p2 ?? 0;
            $data['p3'] = $request->p3 ?? 0;
            $data['p5'] = $request->p5 ?? 0;
            $data['p6'] = $request->p6 ?? 0;
            $data['p7'] = $request->p7 ?? 0;
            $data['p8'] = $request->p8 ?? 0;
            $data['eng'] = $request->eng ?? 0;
            $data['total'] = array_sum([
                $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
                $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
                $data['p1'], $data['p2'], $data['p3'], $data['p5'],
                $data['p6'], $data['p7'], $data['p8'], $data['eng']
            ]);
        } else {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
                'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
                'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
            ]);

            $data['jan'] = $request->jan ?? 0;
            $data['feb'] = $request->feb ?? 0;
            $data['mar'] = $request->mar ?? 0;
            $data['apr'] = $request->apr ?? 0;
            $data['may'] = $request->may ?? 0;
            $data['jun'] = $request->jun ?? 0;
            $data['jul'] = $request->jul ?? 0;
            $data['aug'] = $request->aug ?? 0;
            $data['sep'] = $request->sep ?? 0;
            $data['oct'] = $request->oct ?? 0;
            $data['nov'] = $request->nov ?? 0;
            $data['dec'] = $request->dec ?? 0;
            $data['total'] = array_sum([
                $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
                $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
            ]);
        }

        $verifikasi->update($data);

        // ===== LOG ACTIVITY =====
        $this->logUpdate(
            'verifikasi',
            $verifikasi->id,
            $verifikasi->tool_name . ' - Mode ' . strtoupper($verifikasi->tipe) . ' (' . $verifikasi->tahun . ')',
            $dataLama,
            $verifikasi->fresh()->toArray()
        );

        return redirect()->route('verifikasi.index', ['mode' => $verifikasi->tipe])
            ->with('success', 'Jadwal verifikasi berhasil diperbarui!');
    }

    // ==========================================
    // HAPUS
    // ==========================================
    public function destroy(Verifikasi $verifikasi)
    {
        $dataLama = $verifikasi->toArray();
        $verifikasiId = $verifikasi->id;
        $keterangan = $verifikasi->tool_name . ' - Mode ' . strtoupper($verifikasi->tipe) . ' (' . $verifikasi->tahun . ')';
        $mode = $verifikasi->tipe;

        $verifikasi->delete();

        // ===== LOG ACTIVITY =====
        $this->logDelete('verifikasi', $verifikasiId, $keterangan, $dataLama);

        return redirect()->route('verifikasi.index', ['mode' => $mode])
            ->with('success', 'Jadwal verifikasi berhasil dihapus!');
    }

    // ==========================================
    // IMPORT
    // ==========================================
    public function importForm(Request $request)
    {
        $mode = $request->input('mode', 'bulanan');
        return view('verifikasi.import', compact('mode'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
            'mode' => 'required|in:bulanan,harian',
        ]);

        try {
            Excel::import(
                new VerifikasiImport($request->tahun, $request->mode),
                $request->file('file')
            );

            // ===== LOG ACTIVITY =====
            $this->logActivity(
                'CREATE',
                'verifikasi',
                null,
                'Import Excel mode ' . strtoupper($request->mode) . ' tahun ' . $request->tahun,
                null,
                ['tahun' => $request->tahun, 'mode' => $request->mode, 'file' => $request->file('file')->getClientOriginalName()]
            );

            return redirect()->route('verifikasi.index', [
                'tahun' => $request->tahun,
                'mode' => $request->mode
            ])->with('success', 'Data berhasil di-import.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ==========================================
    // EXPORT
    // ==========================================
    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $mode = $request->input('mode', 'bulanan');

        $labelMode = $mode === 'bulanan' ? 'tahunan' : 'bulanan';
        $filename = 'verifikasi-' . $labelMode . '-' . $tahun . '.xlsx';

        // ===== LOG ACTIVITY =====
        $this->logExport('verifikasi', 'Export Excel mode ' . strtoupper($mode) . ' tahun ' . $tahun);

        return Excel::download(new VerifikasiExport($tahun, $mode), $filename);
    }

    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $mode = $request->input('mode', 'bulanan');

        $data = Verifikasi::where('tahun', $tahun)
            ->where('tipe', $mode)
            ->orderBy('tool_name', 'asc')
            ->get();

        // ===== LOG ACTIVITY =====
        $this->logExport('verifikasi', 'Export PDF mode ' . strtoupper($mode) . ' tahun ' . $tahun);

        $pdf = Pdf::loadView('verifikasi.pdf', compact('data', 'tahun', 'mode'))
            ->setPaper('a4', 'landscape');

        $labelMode = $mode === 'bulanan' ? 'tahunan' : 'bulanan';
        return $pdf->download('verifikasi-' . $labelMode . '-' . $tahun . '.pdf');
    }

    // ==========================================
    // DOWNLOAD TEMPLATE
    // ==========================================
    public function downloadTemplate(Request $request)
    {
        $mode = $request->input('mode', 'bulanan');

        $labelMode = $mode === 'bulanan' ? 'tahunan' : 'bulanan';
        $filename = 'template-import-verifikasi-' . $labelMode . '.csv';

        if ($mode === 'harian') {
            $headers = [
                'tool_name', 'tanggal',
                'qa_1', 'qa_1_2', 'qa_inc', 'qa_insp',
                'ppic_1', 'ppic_2', 'line_scc',
                'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng'
            ];
            $contoh = [
                'CONTOH ALAT', '2026-10-12',
                1, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1, 1
            ];
        } else {
            $headers = [
                'tool_name',
                'jan', 'feb', 'mar', 'apr', 'may', 'jun',
                'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
            ];
            $contoh = [
                'CONTOH ALAT',
                9, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
            ];
        }

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