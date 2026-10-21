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
use App\Exports\ActivityLogExport;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanHarianController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $tanggal = $this->validatedDate($request);
        $tanggalCarbon = Carbon::parse($tanggal)->startOfDay();

        // ===== STATISTIK =====
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

        // ===== LOG AKTIVITAS =====
        $logs = ActivityLog::whereDate('created_at', $tanggal)
            ->orderBy('id', 'desc')
            ->get();

        // ===== ALAT JATUH TEMPO (FIXED) =====
        $batasJatuhTempo = $tanggalCarbon->copy()->addDays(30)->startOfDay();

        $alatJatuhTempo = EksternalMesin::whereNotNull('next_date')
            ->whereBetween('next_date', [$tanggalCarbon, $batasJatuhTempo])
            ->orderBy('next_date', 'asc')
            ->get()
            ->map(function ($item) use ($tanggalCarbon) {
                $item->sisa_hari = (int) $tanggalCarbon->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
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

    public function previewNomor(Request $request)
    {
        $tanggal = $this->validatedDate($request);
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

    public function exportPdf(Request $request)
    {
        $tanggal = $this->validatedDate($request);
        $tanggalCarbon = Carbon::parse($tanggal)->startOfDay();
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

        $batasJatuhTempo = $tanggalCarbon->copy()->addDays(30)->startOfDay();
        $alatJatuhTempo = EksternalMesin::whereNotNull('next_date')
            ->whereBetween('next_date', [$tanggalCarbon, $batasJatuhTempo])
            ->orderBy('next_date', 'asc')->get()
            ->map(function ($item) use ($tanggalCarbon) {
                $item->sisa_hari = (int) $tanggalCarbon->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
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

    public function exportExcel(Request $request)
    {
        $tanggal = $this->validatedDate($request);
        $tanggalCarbon = Carbon::parse($tanggal)->startOfDay();
        $tahun = $tanggalCarbon->year;

        $nomorDokumen = DokumenCounter::generateNomor('LAP', $tahun, 'QC');

        $logs = ActivityLog::whereDate('created_at', $tanggal)
            ->orderBy('id', 'desc')->get();

        $this->logExport('laporan', 'Cetak Excel Laporan Harian tanggal ' . $tanggalCarbon->format('d/m/Y') . ' - Nomor: ' . $nomorDokumen);

        $filename = 'laporan-harian-' . $tanggalCarbon->format('Y-m-d') . '.xlsx';
        $details = 'Tanggal: ' . $tanggalCarbon->format('d F Y')
            . ' | Nomor: ' . $nomorDokumen
            . ' | Pencetak: ' . (Auth::user()->name ?? '-')
            . ' | Dicetak: ' . now()->format('d/m/Y H:i:s');

        return Excel::download(
            new ActivityLogExport($logs, 'LAPORAN HARIAN QC', $details, 'H:i:s'),
            $filename
        );
    }

    private function validatedDate(Request $request): string
    {
        $data = $request->validate(['tanggal' => 'nullable|date_format:Y-m-d']);
        return $data['tanggal'] ?? date('Y-m-d');
    }
}