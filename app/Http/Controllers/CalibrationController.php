<?php

namespace App\Http\Controllers;

use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\Tool;
use App\Imports\EksternalImport;
use App\Exports\EksternalExport;
use App\Imports\MesinImport;
use App\Exports\MesinExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CalibrationController extends Controller
{
    use LogsActivity;

    // ==========================================
    // KALIBRASI EKSTERNAL - SMALL TOOL
    // ==========================================
    public function eksternalSmall(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $schedules = Eksternal::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('eksternal.small', compact('schedules', 'tahun'));
    }

    public function eksternalSmallCreate()
    {
        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('eksternal.create', compact('tools'));
    }

    public function eksternalSmallStore(Request $request)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['qty_set'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $eksternal = Eksternal::create($data);

        // ===== LOG ACTIVITY =====
        $this->logCreate(
            'eksternal',
            $eksternal->id,
            $eksternal->tool_name . ' - Eksternal Small Tool (' . $eksternal->tahun . ')',
            $eksternal->toArray()
        );

        return redirect()->route('eksternal.small', ['tahun' => $data['tahun']])
            ->with('success', 'Jadwal kalibrasi eksternal berhasil ditambahkan!');
    }

    public function eksternalSmallEdit(Eksternal $eksternal)
    {
        return view('eksternal.edit', compact('eksternal'));
    }

    public function eksternalSmallUpdate(Request $request, Eksternal $eksternal)
    {
        // Simpan data lama SEBELUM update
        $dataLama = $eksternal->toArray();

        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['qty_set'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $eksternal->update($data);

        // ===== LOG ACTIVITY =====
        $this->logUpdate(
            'eksternal',
            $eksternal->id,
            $eksternal->tool_name . ' - Eksternal Small Tool (' . $eksternal->tahun . ')',
            $dataLama,
            $eksternal->fresh()->toArray()
        );

        return redirect()->route('eksternal.small')->with('success', 'Jadwal kalibrasi eksternal berhasil diperbarui!');
    }

    public function eksternalSmallDestroy(Eksternal $eksternal)
    {
        $dataLama = $eksternal->toArray();
        $eksternalId = $eksternal->id;
        $keterangan = $eksternal->tool_name . ' - Eksternal Small Tool (' . $eksternal->tahun . ')';

        $eksternal->delete();

        // ===== LOG ACTIVITY =====
        $this->logDelete('eksternal', $eksternalId, $keterangan, $dataLama);

        return redirect()->route('eksternal.small')->with('success', 'Jadwal kalibrasi eksternal berhasil dihapus!');
    }

    // ===== IMPORT EKSTERNAL SMALL =====
    public function eksternalSmallImportForm()
    {
        return view('eksternal.import');
    }

    public function eksternalSmallImportStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
        ]);

        try {
            Excel::import(new EksternalImport($request->tahun), $request->file('file'));

            // ===== LOG ACTIVITY =====
            $this->logActivity(
                'CREATE',
                'eksternal',
                null,
                'Import Excel Eksternal Small Tool tahun ' . $request->tahun,
                null,
                ['tahun' => $request->tahun, 'file' => $request->file('file')->getClientOriginalName()]
            );

            return redirect()->route('eksternal.small', ['tahun' => $request->tahun])
                ->with('success', 'Data berhasil di-import untuk tahun ' . $request->tahun);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ===== EXPORT EKSTERNAL SMALL =====
    public function eksternalSmallExportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $filename = 'eksternal-small-' . $tahun . '.xlsx';

        // ===== LOG ACTIVITY =====
        $this->logExport('eksternal', 'Export Excel Eksternal Small Tool tahun ' . $tahun);

        return Excel::download(new EksternalExport($tahun), $filename);
    }

    public function eksternalSmallExportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = Eksternal::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        // ===== LOG ACTIVITY =====
        $this->logExport('eksternal', 'Export PDF Eksternal Small Tool tahun ' . $tahun);

        $pdf = Pdf::loadView('eksternal.pdf', compact('data', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('eksternal-small-' . $tahun . '.pdf');
    }

    public function eksternalSmallDownloadTemplate()
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

        $filename = 'template-import-eksternal-small.csv';

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

    // ==========================================
    // KALIBRASI EKSTERNAL - MESIN
    // ==========================================
    public function mesin(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $machines = EksternalMesin::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('mesin.index', compact('machines', 'tahun'));
    }

    public function mesinCreate()
    {
        return view('mesin.create');
    }

    public function mesinStore(Request $request)
    {
        $data = $request->validate([
            'tool_name'      => 'required|string|max:255',
            'machine_model'  => 'nullable|string|max:255',
            'code_number'    => 'nullable|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'maker'          => 'nullable|string|max:255',
            'no_machine'     => 'nullable|string|max:255',
            'retension'      => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'next_date'      => 'nullable|date',
            'tahun'          => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['total'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $mesin = EksternalMesin::create($data);

        // ===== LOG ACTIVITY =====
        $this->logCreate(
            'mesin',
            $mesin->id,
            $mesin->tool_name . ' - Mesin Alat Ukur (' . $mesin->tahun . ')',
            $mesin->toArray()
        );

        return redirect()->route('mesin.index')
            ->with('success', 'Data mesin berhasil ditambahkan!');
    }

    public function mesinEdit(EksternalMesin $mesin)
    {
        return view('mesin.edit', compact('mesin'));
    }

    public function mesinUpdate(Request $request, EksternalMesin $mesin)
    {
        // Simpan data lama SEBELUM update
        $dataLama = $mesin->toArray();

        $data = $request->validate([
            'tool_name'      => 'required|string|max:255',
            'machine_model'  => 'nullable|string|max:255',
            'code_number'    => 'nullable|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'maker'          => 'nullable|string|max:255',
            'no_machine'     => 'nullable|string|max:255',
            'retension'      => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'next_date'      => 'nullable|date',
            'tahun'          => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['total'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $mesin->update($data);

        // ===== LOG ACTIVITY =====
        $this->logUpdate(
            'mesin',
            $mesin->id,
            $mesin->tool_name . ' - Mesin Alat Ukur (' . $mesin->tahun . ')',
            $dataLama,
            $mesin->fresh()->toArray()
        );

        return redirect()->route('mesin.index')
            ->with('success', 'Data mesin berhasil diperbarui!');
    }

    public function mesinDestroy(EksternalMesin $mesin)
    {
        $dataLama = $mesin->toArray();
        $mesinId = $mesin->id;
        $keterangan = $mesin->tool_name . ' - Mesin Alat Ukur (' . $mesin->tahun . ')';

        $mesin->delete();

        // ===== LOG ACTIVITY =====
        $this->logDelete('mesin', $mesinId, $keterangan, $dataLama);

        return redirect()->route('mesin.index')
            ->with('success', 'Data mesin berhasil dihapus!');
    }

    // ===== IMPORT EKSTERNAL MESIN =====
    public function mesinImportForm()
    {
        return view('mesin.import');
    }

    public function mesinImportStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
        ]);

        try {
            Excel::import(new MesinImport($request->tahun), $request->file('file'));

            // ===== LOG ACTIVITY =====
            $this->logActivity(
                'CREATE',
                'mesin',
                null,
                'Import Excel Mesin Alat Ukur tahun ' . $request->tahun,
                null,
                ['tahun' => $request->tahun, 'file' => $request->file('file')->getClientOriginalName()]
            );

            return redirect()->route('mesin.index', ['tahun' => $request->tahun])
                ->with('success', 'Data berhasil di-import untuk tahun ' . $request->tahun);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ===== EXPORT EKSTERNAL MESIN =====
    public function mesinExportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $filename = 'eksternal-mesin-' . $tahun . '.xlsx';

        // ===== LOG ACTIVITY =====
        $this->logExport('mesin', 'Export Excel Mesin Alat Ukur tahun ' . $tahun);

        return Excel::download(new MesinExport($tahun), $filename);
    }

    public function mesinExportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = EksternalMesin::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        // ===== LOG ACTIVITY =====
        $this->logExport('mesin', 'Export PDF Mesin Alat Ukur tahun ' . $tahun);

        $pdf = Pdf::loadView('mesin.pdf', compact('data', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('eksternal-mesin-' . $tahun . '.pdf');
    }

    public function mesinDownloadTemplate()
    {
        $headers = [
            'tool_name', 'machine_model', 'code_number', 'serial_number',
            'maker', 'no_machine', 'retension', 'start_date', 'next_date',
            'jan', 'feb', 'mar', 'apr', 'may', 'jun',
            'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
        ];

        $contoh = [
            'CONTOH MESIN', 'SJ-210', '178-560-01', '301241402',
            'MITUTOYO', '01-42', '3/YEAR', '2026-01-01', '2027-01-01',
            1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        ];

        $filename = 'template-import-eksternal-mesin.csv';

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