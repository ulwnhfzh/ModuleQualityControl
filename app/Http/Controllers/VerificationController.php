<?php

namespace App\Http\Controllers;

use App\Models\Verification;
use App\Models\Tool;
use App\Models\DocumentCounter;
use App\Support\DocumentPdfLayout;
use App\Imports\VerificationImport;
use App\Imports\SpreadsheetHeaderRowDetector;
use App\Exports\VerificationExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class VerificationController extends Controller
{
    use LogsActivity;

    // ==========================================
    // TAMPILKAN TABEL
    // ==========================================
    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'mode' => 'nullable|in:bulanan,harian',
            'company' => 'nullable|in:HAMATETSU,NISSO GIKEN',
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $mode = $filters['mode'] ?? 'bulanan';
        $company = $filters['company'] ?? 'HAMATETSU';

        if ($company === 'NISSO GIKEN' && $mode !== 'harian') {
            return redirect()->route('verifikasi.index', ['tahun' => $tahun, 'mode' => 'harian', 'company' => $company]);
        }

        $schedules = Verification::where('tahun', $tahun)
            ->where('company', $company)
            ->where('tipe', $mode)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('verification.index', compact('schedules', 'tahun', 'mode', 'company'));
    }

    // ==========================================
    // FORM TAMBAH
    // ==========================================
    public function create(Request $request)
    {
        $filters = $request->validate([
            'mode' => 'nullable|in:bulanan,harian',
            'tahun' => 'nullable|integer|between:2000,2100',
            'company' => 'nullable|in:HAMATETSU,NISSO GIKEN',
        ]);
        $mode = $filters['mode'] ?? 'bulanan';
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $company = $filters['company'] ?? 'HAMATETSU';

        if ($company === 'NISSO GIKEN') {
            $mode = 'harian';
        }

        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('verification.create', compact('tools', 'mode', 'tahun', 'company'));
    }

    // ==========================================
    // SIMPAN DATA
    // ==========================================
    public function store(Request $request)
    {
        $modeData = $request->validate([
            'tipe' => 'required|in:bulanan,harian',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
        ]);
        $mode = $modeData['tipe'];
        if ($modeData['company'] === 'NISSO GIKEN' && $mode !== 'harian') {
            return back()->withInput()->withErrors(['tipe' => 'NISSO GIKEN menggunakan jadwal verifikasi enam bulanan. Pilih mode Bulanan.']);
        }
        $data['tool_name'] = strtoupper($request->tool_name);
        $data['tahun'] = $request->tahun;
        $data['tipe'] = $mode;
        $data['company'] = $modeData['company'];

        if ($mode == 'harian') {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|integer|between:2000,2100',
                'tanggal' => 'required|date',
                'qa_1' => 'integer|min:0|max:2147483647', 'qa_1_2' => 'integer|min:0|max:2147483647', 'qa_inc' => 'integer|min:0|max:2147483647', 'qa_insp' => 'integer|min:0|max:2147483647',
                'ppic_1' => 'integer|min:0|max:2147483647', 'ppic_2' => 'integer|min:0|max:2147483647', 'line_scc' => 'integer|min:0|max:2147483647',
                'p1' => 'integer|min:0|max:2147483647', 'p2' => 'integer|min:0|max:2147483647', 'p3' => 'integer|min:0|max:2147483647', 'p5' => 'integer|min:0|max:2147483647',
                'p6' => 'integer|min:0|max:2147483647', 'p7' => 'integer|min:0|max:2147483647', 'p8' => 'integer|min:0|max:2147483647', 'eng' => 'integer|min:0|max:2147483647',
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
                'tahun' => 'required|integer|between:2000,2100',
                'jan' => 'integer|min:0|max:2147483647', 'feb' => 'integer|min:0|max:2147483647', 'mar' => 'integer|min:0|max:2147483647', 'apr' => 'integer|min:0|max:2147483647',
                'may' => 'integer|min:0|max:2147483647', 'jun' => 'integer|min:0|max:2147483647', 'jul' => 'integer|min:0|max:2147483647', 'aug' => 'integer|min:0|max:2147483647',
                'sep' => 'integer|min:0|max:2147483647', 'oct' => 'integer|min:0|max:2147483647', 'nov' => 'integer|min:0|max:2147483647', 'dec' => 'integer|min:0|max:2147483647',
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

        $data['verified_by'] = $request->boolean('sudah_diverifikasi') ? Auth::user()->name : null;
        $data['verified_at'] = $request->boolean('sudah_diverifikasi') ? now() : null;

        if ($message = $this->validateNissoCycle($data)) {
            return back()->withInput()->withErrors(['tanggal' => $message]);
        }

        $verifikasi = Verification::create($data);

        $this->logCreate(
            'verifikasi',
            $verifikasi->id,
            $verifikasi->tool_name . ' - Mode ' . strtoupper($mode) . ' (' . $verifikasi->tahun . ')',
            $verifikasi->toArray()
        );

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('verifikasi.create', ['tahun' => $data['tahun'], 'mode' => $mode, 'company' => $data['company']])
                ->with('success', 'Data berhasil disimpan. Lanjutkan input berikutnya.');
        }

        return redirect()->route('verifikasi.index', ['tahun' => $data['tahun'], 'mode' => $mode, 'company' => $data['company']])
            ->with('success', 'Jadwal verifikasi berhasil ditambahkan!');
    }

    public function edit(Verification $verifikasi)
    {
        return view('verification.edit', compact('verifikasi'));
    }

    public function update(Request $request, Verification $verifikasi)
    {
        $dataLama = $verifikasi->toArray();

        $data['company'] = $request->validate(['company' => 'required|in:HAMATETSU,NISSO GIKEN'])['company'];
        if ($data['company'] === 'NISSO GIKEN' && $verifikasi->tipe !== 'harian') {
            return redirect()->route('verifikasi.index', ['tahun' => $verifikasi->tahun, 'mode' => 'harian', 'company' => 'NISSO GIKEN'])
                ->with('error', 'Jadwal tahunan NISSO GIKEN tidak digunakan. Buat jadwal melalui mode Bulanan.');
        }
        $data['tool_name'] = strtoupper($request->tool_name);
        $data['tahun'] = $request->tahun;
        $data['tipe'] = $verifikasi->tipe;

        if ($verifikasi->tipe == 'harian') {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|integer|between:2000,2100',
                'tanggal' => 'required|date',
                'qa_1' => 'integer|min:0|max:2147483647', 'qa_1_2' => 'integer|min:0|max:2147483647', 'qa_inc' => 'integer|min:0|max:2147483647', 'qa_insp' => 'integer|min:0|max:2147483647',
                'ppic_1' => 'integer|min:0|max:2147483647', 'ppic_2' => 'integer|min:0|max:2147483647', 'line_scc' => 'integer|min:0|max:2147483647',
                'p1' => 'integer|min:0|max:2147483647', 'p2' => 'integer|min:0|max:2147483647', 'p3' => 'integer|min:0|max:2147483647', 'p5' => 'integer|min:0|max:2147483647',
                'p6' => 'integer|min:0|max:2147483647', 'p7' => 'integer|min:0|max:2147483647', 'p8' => 'integer|min:0|max:2147483647', 'eng' => 'integer|min:0|max:2147483647',
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
                'tahun' => 'required|integer|between:2000,2100',
                'jan' => 'integer|min:0|max:2147483647', 'feb' => 'integer|min:0|max:2147483647', 'mar' => 'integer|min:0|max:2147483647', 'apr' => 'integer|min:0|max:2147483647',
                'may' => 'integer|min:0|max:2147483647', 'jun' => 'integer|min:0|max:2147483647', 'jul' => 'integer|min:0|max:2147483647', 'aug' => 'integer|min:0|max:2147483647',
                'sep' => 'integer|min:0|max:2147483647', 'oct' => 'integer|min:0|max:2147483647', 'nov' => 'integer|min:0|max:2147483647', 'dec' => 'integer|min:0|max:2147483647',
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

        // Jika data berubah setelah konfirmasi, wajib diperiksa dan dikonfirmasi ulang.
        if ($message = $this->validateNissoCycle($data, $verifikasi)) {
            return back()->withInput()->withErrors(['tanggal' => $message]);
        }

        $wasVerified = (bool) $verifikasi->verified_at;
        $dataChanged = collect($data)->contains(fn ($value, $key) => (string) ($verifikasi->{$key} ?? '') !== (string) ($value ?? ''));
        $reopened = $wasVerified && $dataChanged;
        $data['verified_by'] = $reopened ? null : $verifikasi->verified_by;
        $data['verified_at'] = $reopened ? null : $verifikasi->verified_at;
        $verifikasi->update($data);

        $this->logUpdate(
            'verifikasi',
            $verifikasi->id,
            $verifikasi->tool_name . ' - Mode ' . strtoupper($verifikasi->tipe) . ' (' . $verifikasi->tahun . ')',
            $dataLama,
            $verifikasi->fresh()->toArray()
        );

        return redirect()->route('verifikasi.index', ['tahun' => $verifikasi->tahun, 'mode' => $verifikasi->tipe, 'company' => $verifikasi->company])
            ->with('success', $reopened
                ? 'Data diperbarui. Status konfirmasi dibuka kembali dan perlu diperiksa ulang.'
                : 'Jadwal verifikasi berhasil diperbarui!');
    }

    public function destroy(Verification $verifikasi)
    {
        $dataLama = $verifikasi->toArray();
        $verifikasiId = $verifikasi->id;
        $keterangan = $verifikasi->tool_name . ' - Mode ' . strtoupper($verifikasi->tipe) . ' (' . $verifikasi->tahun . ')';
        $mode = $verifikasi->tipe;

        $verifikasi->delete();

        $this->logDelete('verifikasi', $verifikasiId, $keterangan, $dataLama);

        return redirect()->route('verifikasi.index', ['tahun' => $verifikasi->tahun, 'mode' => $mode, 'company' => $verifikasi->company])
            ->with('success', 'Jadwal verifikasi berhasil dihapus!');
    }

    public function confirm(Verification $verifikasi)
    {
        if ($verifikasi->verified_at) {
            return back()->with('success', 'Data ini sudah dikonfirmasi sebelumnya.');
        }

        $dataLama = $verifikasi->toArray();
        $verifikasi->update([
            'verified_by' => Auth::user()->name,
            'verified_at' => now(),
        ]);

        $this->logUpdate(
            'verifikasi',
            $verifikasi->id,
            $verifikasi->tool_name . ' - Konfirmasi verifikasi',
            $dataLama,
            $verifikasi->fresh()->toArray()
        );

        return back()->with('success', 'Verification berhasil dikonfirmasi dan nama pemeriksa dicatat.');
    }

    public function reopen(Verification $verifikasi)
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);

        if (!$verifikasi->verified_at) {
            return back()->with('error', 'Data ini belum dikonfirmasi.');
        }

        $dataLama = $verifikasi->toArray();
        $verifikasi->update(['verified_by' => null, 'verified_at' => null]);

        $this->logUpdate(
            'verifikasi',
            $verifikasi->id,
            $verifikasi->tool_name . ' - Konfirmasi dibuka kembali untuk koreksi',
            $dataLama,
            $verifikasi->fresh()->toArray()
        );

        return back()->with('success', 'Konfirmasi dibuka kembali. Silakan edit data, lalu konfirmasi ulang setelah diperiksa.');
    }

    // ==========================================
    // IMPORT
    // ==========================================
    public function importForm(Request $request)
    {
        $filters = $request->validate([
            'mode' => 'nullable|in:bulanan,harian',
            'company' => 'nullable|in:HAMATETSU,NISSO GIKEN',
        ]);
        $mode = $filters['mode'] ?? 'bulanan';
        $company = $filters['company'] ?? 'HAMATETSU';
        if ($company === 'NISSO GIKEN') {
            $mode = 'harian';
        }
        return view('verification.import', compact('mode', 'company'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
            'mode' => 'required|in:bulanan,harian',
            'company' => 'required|in:HAMATETSU,NISSO GIKEN',
        ]);

        if ($request->company === 'NISSO GIKEN' && $request->mode !== 'harian') {
            return back()->withInput()->withErrors(['mode' => 'Import NISSO GIKEN harus menggunakan format jadwal Bulanan/enam bulanan.']);
        }

        try {
            $import = new VerificationImport(
                $request->tahun,
                $request->mode,
                $request->company,
                SpreadsheetHeaderRowDetector::detect($request->file('file'))
            );
            Excel::import(
                $import,
                $request->file('file')
            );

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
                'mode' => $request->mode,
                'company' => $request->company,
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
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'mode' => 'nullable|in:bulanan,harian',
            'company' => 'nullable|in:HAMATETSU,NISSO GIKEN',
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $mode = $filters['mode'] ?? 'bulanan';
        $company = $filters['company'] ?? 'HAMATETSU';
        if ($company === 'NISSO GIKEN') {
            $mode = 'harian';
        }

        $labelMode = $mode === 'bulanan' ? 'tahunan' : 'bulanan';
        $filename = 'verifikasi-' . $labelMode . '-' . strtolower(str_replace(' ', '-', $company)) . '-' . $tahun . '.xlsx';

        $this->logExport('verifikasi', 'Export Excel mode ' . strtoupper($mode) . ' tahun ' . $tahun);

        return Excel::download(new VerificationExport($tahun, $mode, $company), $filename);
    }

    /**
     * Export PDF dengan nomor dokumen otomatis
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'mode' => 'nullable|in:bulanan,harian',
            'company' => 'nullable|in:HAMATETSU,NISSO GIKEN',
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $mode = $filters['mode'] ?? 'bulanan';
        $company = $filters['company'] ?? 'HAMATETSU';
        if ($company === 'NISSO GIKEN') {
            $mode = 'harian';
        }

        $data = Verification::where('tahun', $tahun)
            ->where('company', $company)
            ->where('tipe', $mode)
            ->orderBy('tool_name', 'asc')
            ->get();

        // ===== GENERATE NOMOR DOKUMEN =====
        $nomorDokumen = DocumentCounter::generateNomor('VFR', $tahun, 'QC');

        // ===== LOG ACTIVITY =====
        $this->logExport('verifikasi', 'Export PDF Verification mode ' . strtoupper($mode) . ' - Nomor: ' . $nomorDokumen);

        // ===== GENERATE PDF =====
        $pdf = Pdf::loadView('verification.pdf', compact('data', 'tahun', 'mode', 'company', 'nomorDokumen'))
            ->setPaper('a4', 'landscape');

        DocumentPdfLayout::addPageNumbers($pdf);

        $labelMode = $mode === 'bulanan' ? 'tahunan' : 'bulanan';
        return $pdf->download('verifikasi-' . $labelMode . '-' . strtolower(str_replace(' ', '-', $company)) . '-' . $tahun . '.pdf');
    }

    public function downloadTemplate(Request $request)
    {
        $filters = $request->validate(['mode' => 'nullable|in:bulanan,harian']);
        $mode = $filters['mode'] ?? 'bulanan';
        if ($request->input('company') === 'NISSO GIKEN') {
            $mode = 'harian';
        }

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

    /** Enforce two NISSO GIKEN records per tool and a six-month gap between dates. */
    private function validateNissoCycle(array $data, ?Verification $current = null): ?string
    {
        if (($data['company'] ?? null) !== 'NISSO GIKEN' || ($data['tipe'] ?? null) !== 'harian') {
            return null;
        }

        if (empty($data['tanggal'])) {
            return 'Tanggal jadwal NISSO GIKEN wajib diisi.';
        }

        $records = Verification::query()
            ->where('company', 'NISSO GIKEN')
            ->where('tipe', 'harian')
            ->where('tool_name', strtoupper($data['tool_name']))
            ->whereNotNull('tanggal')
            ->when($current, fn ($query) => $query->where('id', '!=', $current->getKey()))
            ->get(['id', 'tahun', 'tanggal']);

        $sameYearCount = $records->where('tahun', (int) $data['tahun'])->count();
        if ($sameYearCount >= 2) {
            return 'NISSO GIKEN hanya memiliki maksimal dua jadwal untuk alat ini dalam satu tahun.';
        }

        $proposed = Carbon::parse($data['tanggal'])->startOfDay();
        if ($proposed->year !== (int) $data['tahun']) {
            return 'Tahun jadwal harus sama dengan tahun pada tanggal verifikasi.';
        }
        foreach ($records as $record) {
            $existing = Carbon::parse($record->tanggal)->startOfDay();
            $earlier = $proposed->lessThan($existing) ? $proposed : $existing;
            $later = $proposed->greaterThan($existing) ? $proposed : $existing;
            if ($earlier->copy()->addMonthsNoOverflow(6)->greaterThan($later)) {
                return 'Jadwal NISSO GIKEN untuk alat yang sama harus berjarak minimal enam bulan.';
            }
        }

        return null;
    }
}
