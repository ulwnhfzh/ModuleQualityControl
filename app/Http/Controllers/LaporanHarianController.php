<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\EksternalMesin;
use App\Models\DokumenCounter;
use App\Models\Pengaturan;
use App\Exports\LaporanHarianSummaryExport;
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

        $warningDays = Pengaturan::warningStandar();
        $batasJatuhTempo = $tanggalCarbon->copy()->addDays($warningDays)->startOfDay();

        $alatJatuhTempo = EksternalMesin::whereNotNull('next_date')
            ->whereBetween('next_date', [$tanggalCarbon, $batasJatuhTempo])
            ->orderBy('next_date', 'asc')
            ->get()
            ->map(function ($item) use ($tanggalCarbon) {
                $item->sisa_hari = (int) $tanggalCarbon->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
                return $item;
            });

        return view('laporan-harian.index', compact(
            'tanggal',
            'tanggalCarbon',
            'totalAktivitas',
            'verifikasiHariIni',
            'kalibrasiHariIni',
            'aktivitasPerJenis',
            'alatJatuhTempo',
            'warningDays'
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
        $verifikasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->where('jenis_data', 'verifikasi')->whereIn('aksi', ['CREATE', 'UPDATE'])->count();
        $kalibrasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->whereIn('jenis_data', ['internal', 'eksternal', 'mesin'])
            ->whereIn('aksi', ['CREATE', 'UPDATE'])->count();

        $aktivitasPerJenis = ActivityLog::whereDate('created_at', $tanggal)
            ->select('jenis_data', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis_data')->get();

        $warningDays = Pengaturan::warningStandar();
        $batasJatuhTempo = $tanggalCarbon->copy()->addDays($warningDays)->startOfDay();
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
            'totalAktivitas', 'verifikasiHariIni', 'kalibrasiHariIni',
            'aktivitasPerJenis', 'alatJatuhTempo', 'warningDays'
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

        $totalAktivitas = ActivityLog::whereDate('created_at', $tanggal)->count();
        $verifikasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->where('jenis_data', 'verifikasi')->whereIn('aksi', ['CREATE', 'UPDATE'])->count();
        $kalibrasiHariIni = ActivityLog::whereDate('created_at', $tanggal)
            ->whereIn('jenis_data', ['internal', 'eksternal', 'mesin'])
            ->whereIn('aksi', ['CREATE', 'UPDATE'])->count();
        $aktivitasPerJenis = ActivityLog::whereDate('created_at', $tanggal)
            ->select('jenis_data', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('jenis_data')->get();
        $warningDays = Pengaturan::warningStandar();
        $batasJatuhTempo = $tanggalCarbon->copy()->addDays($warningDays)->startOfDay();
        $alatJatuhTempo = EksternalMesin::whereNotNull('next_date')
            ->whereBetween('next_date', [$tanggalCarbon, $batasJatuhTempo])
            ->orderBy('next_date', 'asc')->get()
            ->map(function ($item) use ($tanggalCarbon) {
                $item->sisa_hari = (int) $tanggalCarbon->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
                return $item;
            });

        $this->logExport('laporan', 'Cetak Excel Laporan Harian tanggal ' . $tanggalCarbon->format('d/m/Y') . ' - Nomor: ' . $nomorDokumen);

        $filename = 'laporan-harian-' . $tanggalCarbon->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new LaporanHarianSummaryExport(
                $tanggalCarbon,
                $nomorDokumen,
                Auth::user()->name ?? '-',
                $totalAktivitas,
                $verifikasiHariIni,
                $kalibrasiHariIni,
                $aktivitasPerJenis,
                $alatJatuhTempo,
                $warningDays,
            ),
            $filename
        );
    }

    private function validatedDate(Request $request): string
    {
        $data = $request->validate(['tanggal' => 'nullable|date_format:Y-m-d']);
        return $data['tanggal'] ?? date('Y-m-d');
    }
}
