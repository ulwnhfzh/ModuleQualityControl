<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verifikasi;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\ActivityLog;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $tab = $request->input('tab', 'ringkasan'); // ringkasan | log | grafik

        // ===== TAB RINGKASAN =====
        $totalTools       = Tool::where('tahun', $tahun)->count();
        $totalVerifikasi  = Verifikasi::where('tahun', $tahun)->count();
        $totalInternal    = Internal::where('tahun', $tahun)->count();
        $totalEksternal   = Eksternal::where('tahun', $tahun)->count();
        $totalMesin       = EksternalMesin::where('tahun', $tahun)->count();

        $toolsSmallTool = Tool::where('tahun', $tahun)->where('category', 'Small Tool')->count();
        $toolsMachine   = Tool::where('tahun', $tahun)->where('category', 'Machine')->count();
        $toolsJig       = Tool::where('tahun', $tahun)->where('category', 'Jig')->count();

        // ===== TAB LOG AKTIVITAS =====
        $query = ActivityLog::query();

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis_data', $request->jenis);
        }
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }
        if ($request->filled('search')) {
            $query->where('keterangan', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $users = User::orderBy('name')->get();

        // ===== TAB GRAFIK (BARU) =====
        $bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        $verifikasiPerBulan = [];
        $internalPerBulan   = [];
        $eksternalPerBulan  = [];
        $mesinPerBulan      = [];

        foreach ($bulanColumns as $col) {
            $verifikasiPerBulan[] = (int) Verifikasi::where('tahun', $tahun)->sum($col);
            $internalPerBulan[]   = (int) Internal::where('tahun', $tahun)->sum($col);
            $eksternalPerBulan[]  = (int) Eksternal::where('tahun', $tahun)->sum($col);
            $mesinPerBulan[]      = (int) EksternalMesin::where('tahun', $tahun)->sum($col);
        }

        $tahunRange = range(date('Y'), date('Y') - 4);
        sort($tahunRange);
        $tahunLabels = array_map('strval', $tahunRange);

        $verifikasiPerTahun = [];
        $internalPerTahun   = [];
        $eksternalPerTahun  = [];
        $mesinPerTahun      = [];
        $toolsPerTahun      = [];

        foreach ($tahunRange as $th) {
            $verifikasiPerTahun[] = Verifikasi::where('tahun', $th)->count();
            $internalPerTahun[]   = Internal::where('tahun', $th)->count();
            $eksternalPerTahun[]  = Eksternal::where('tahun', $th)->count();
            $mesinPerTahun[]      = EksternalMesin::where('tahun', $th)->count();
            $toolsPerTahun[]      = Tool::where('tahun', $th)->count();
        }

        return view('reports.index', compact(
            'tahun', 'tab',
            'totalTools', 'totalVerifikasi', 'totalInternal', 'totalEksternal', 'totalMesin',
            'toolsSmallTool', 'toolsMachine', 'toolsJig',
            'logs', 'users',
            // Data grafik
            'bulanLabels', 'verifikasiPerBulan', 'internalPerBulan', 'eksternalPerBulan', 'mesinPerBulan',
            'tahunLabels', 'verifikasiPerTahun', 'internalPerTahun', 'eksternalPerTahun', 'mesinPerTahun', 'toolsPerTahun'
        ));
    }

    public function exportLogExcel(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis_data', $request->jenis);
        }
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }
        if ($request->filled('search')) {
            $query->where('keterangan', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('id', 'desc')->get();

        $this->logExport('log', 'Export Log Aktivitas ke Excel');

        $filename = 'log-aktivitas-' . date('Y-m-d-His') . '.csv';

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['No', 'Waktu', 'User', 'Aksi', 'Jenis Data', 'Keterangan', 'IP Address']);

            foreach ($logs as $index => $log) {
                fputcsv($file, [
                    $index + 1,
                    $log->created_at->format('d/m/Y H:i:s'),
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

    public function exportLogPdf(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis_data', $request->jenis);
        }
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }
        if ($request->filled('search')) {
            $query->where('keterangan', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('id', 'desc')->limit(500)->get();

        $this->logExport('log', 'Export Log Aktivitas ke PDF');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.log-pdf', compact('logs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('log-aktivitas-' . date('Y-m-d-His') . '.pdf');
    }

    public function exportAllExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        return back()->with('info', 'Export All Excel tahun ' . $tahun . ' sedang dikembangkan.');
    }

    public function exportAllPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        return back()->with('info', 'Export All PDF tahun ' . $tahun . ' sedang dikembangkan.');
    }
}