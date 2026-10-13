<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Tool;
use App\Models\Verifikasi;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\DokumenCounter;
use App\Models\User;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanHarianController extends Controller
{
    use LogsActivity;

    /**
     * Halaman Laporan Harian
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $tanggalCarbon = Carbon::parse($tanggal);

        // ===== STATISTIK HARI INI =====
        $totalAktivitas = ActivityLog::whereDate('created_at', $tanggal)->count();

        $userAktif = ActivityLog::whereDate('created_at', $tanggal)
            ->where('aksi', 'LOGIN')
            ->distinct('user_id')
            ->count('user_id');

        $verifikasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->where('jenis_data', 'verifikasi')
            ->whereIn('aksi', ['CREATE', 'UPDATE'])
            ->count();

        $kalibrasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->whereIn('jenis_data', ['internal', 'eksternal', 'mesin'])
            ->whereIn('aksi', ['CREATE', 'UPDATE'])
            ->count();

        $aktivitasPerJenis = ActivityLog::whereDate('created_at', $tanggal)
            ->select('jenis_data', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis_data')
            ->get();

        $aktivitasPerAksi = ActivityLog::whereDate('created_at', $tanggal)
            ->select('aksi', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('aksi')
            ->get();

        // ===== LOG AKTIVITAS HARI INI =====
        $logs = ActivityLog::whereDate('created_at', $tanggal)
            ->orderBy('id', 'desc')
            ->get();

        // ===== ALAT JATUH TEMPO =====
        $batasJatuhTempo = $tanggalCarbon->copy()->addDays(30);

        $alatJatuhTempo = EksternalMesin::whereNotNull('next_date')
            ->whereBetween('next_date', [$tanggalCarbon, $batasJatuhTempo])
            ->orderBy('next_date', 'asc')
            ->get()
            ->map(function ($item) use ($tanggalCarbon) {
                $item->sisa_hari = $tanggalCarbon->diffInDays(Carbon::parse($item->next_date), false);
                return $item;
            });

        $users = User::orderBy('name')->get();

        return view('laporan-harian.index', compact(
            'tanggal',
            'tanggalCarbon',
            'totalAktivitas',
            'userAktif',
            'verifikasiHariIni',
            'kalibrasiHariIni',
            'aktivitasPerJenis',
            'aktivitasPerAksi',
            'logs',
            'alatJatuhTempo',
            'users'
        ));
    }

    /**
     * Preview nomor dokumen berikutnya (AJAX)
     */
    public function previewNomor(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $tahun = Carbon::parse($tanggal)->year;

        $counter = DokumenCounter::where('jenis', 'LAP')
            ->where('tahun', $tahun)
            ->first();

        $nomorBerikutnya = ($counter?->nomor_terakhir ?? 0) + 1;
        $nomorUrut = str_pad($nomorBerikutnya, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'nomor' => 'QC/LAP/' . $tahun . '/' . $nomorUrut,
        ]);
    }

    /**
     * Export PDF Laporan Harian
     */
    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $tanggalCarbon = Carbon::parse($tanggal);
        $tahun = $tanggalCarbon->year;

        $nomorDokumen = DokumenCounter::generateNomor('LAP', $tahun, 'QC');

        $totalAktivitas = ActivityLog::whereDate('created_at', $tanggal)->count();
        $userAktif = ActivityLog::whereDate('created_at', $tanggal)
            ->where('aksi', 'LOGIN')->distinct('user_id')->count('user_id');
        $verifikasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->where('jenis_data', 'verifikasi')->whereIn('aksi', ['CREATE', 'UPDATE'])->count();
        $kalibrasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->whereIn('jenis_data', ['internal', 'eksternal', 'mesin'])
            ->whereIn('aksi', ['CREATE', 'UPDATE'])->count();

        $aktivitasPerJenis = ActivityLog::whereDate('created_at', $tanggal)
            ->select('jenis_data', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis_data')->get();

        $aktivitasPerAksi = ActivityLog::whereDate('created_at', $tanggal)
            ->select('aksi', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('aksi')->get();

        $logs = ActivityLog::whereDate('created_at', $tanggal)
            ->orderBy('id', 'desc')->get();

        $batasJatuhTempo = $tanggalCarbon->copy()->addDays(30);
        $alatJatuhTempo = EksternalMesin::whereNotNull('next_date')
            ->whereBetween('next_date', [$tanggalCarbon, $batasJatuhTempo])
            ->orderBy('next_date', 'asc')->get()
            ->map(function ($item) use ($tanggalCarbon) {
                $item->sisa_hari = $tanggalCarbon->diffInDays(Carbon::parse($item->next_date), false);
                return $item;
            });

        $pencetak = Auth::user();

        $this->logExport('laporan', 'Cetak PDF Laporan Harian tanggal ' . $tanggalCarbon->format('d/m/Y') . ' - Nomor: ' . $nomorDokumen);

        $pdf = Pdf::loadView('laporan-harian.pdf', compact(
            'tanggal', 'tanggalCarbon', 'nomorDokumen', 'pencetak',
            'totalAktivitas', 'userAktif', 'verifikasiHariIni', 'kalibrasiHariIni',
            'aktivitasPerJenis', 'aktivitasPerAksi', 'logs', 'alatJatuhTempo'
        ))->setPaper('a4', 'portrait');

        $filename = 'laporan-harian-' . $tanggalCarbon->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export Excel (CSV) Laporan Harian
     */
    public function exportExcel(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $tanggalCarbon = Carbon::parse($tanggal);
        $tahun = $tanggalCarbon->year;

        $nomorDokumen = DokumenCounter::generateNomor('LAP', $tahun, 'QC');

        $logs = ActivityLog::whereDate('created_at', $tanggal)
            ->orderBy('id', 'desc')->get();

        $this->logExport('laporan', 'Cetak Excel Laporan Harian tanggal ' . $tanggalCarbon->format('d/m/Y') . ' - Nomor: ' . $nomorDokumen);

        $filename = 'laporan-harian-' . $tanggalCarbon->format('Y-m-d') . '.csv';

        $callback = function () use ($logs, $nomorDokumen, $tanggalCarbon) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['LAPORAN HARIAN QC']);
            fputcsv($file, ['PT. Hamatetsu Indonesia']);
            fputcsv($file, ['Nomor Dokumen:', $nomorDokumen]);
            fputcsv($file, ['Tanggal:', $tanggalCarbon->format('d F Y')]);
            fputcsv($file, ['Pencetak:', Auth::user()->name ?? '-']);
            fputcsv($file, ['Dicetak:', now()->format('d/m/Y H:i:s')]);
            fputcsv($file, []);
            fputcsv($file, ['No', 'Waktu', 'User', 'Aksi', 'Jenis Data', 'Keterangan', 'IP Address']);

            foreach ($logs as $index => $log) {
                fputcsv($file, [
                    $index + 1,
                    $log->created_at->format('H:i:s'),
                    $log->user_name ?? '-',
                    $log->aksi,
                    $log->label_jenis ?? '-',
                    $log->keterangan ?? '-',
                    $log->ip_address ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}