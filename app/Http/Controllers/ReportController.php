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

    /**
     * Halaman Reports (tab Ringkasan + Log)
     */
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $tab = $request->input('tab', 'ringkasan'); // ringkasan | log

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

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->where('jenis_data', $request->jenis);
        }

        // Filter by aksi
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        // Pencarian keterangan
        if ($request->filled('search')) {
            $query->where('keterangan', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        // Data untuk dropdown filter
        $users = User::orderBy('name')->get();

        return view('reports.index', compact(
            'tahun', 'tab',
            'totalTools', 'totalVerifikasi', 'totalInternal', 'totalEksternal', 'totalMesin',
            'toolsSmallTool', 'toolsMachine', 'toolsJig',
            'logs', 'users'
        ));
    }

    /**
     * Export Log Aktivitas ke Excel (format CSV)
     */
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

        // ===== LOG ACTIVITY =====
        $this->logExport('log', 'Export Log Aktivitas ke Excel');

        $filename = 'log-aktivitas-' . date('Y-m-d-His') . '.csv';

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($file, [
                'No', 'Waktu', 'User', 'Aksi', 'Jenis Data', 'Keterangan', 'IP Address'
            ]);

            // Data
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

    /**
     * Export Log Aktivitas ke PDF
     */
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

        // ===== LOG ACTIVITY =====
        $this->logExport('log', 'Export Log Aktivitas ke PDF');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.log-pdf', compact('logs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('log-aktivitas-' . date('Y-m-d-His') . '.pdf');
    }

    /**
     * Export All (Excel) - dari fitur lama
     */
    public function exportAllExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        return back()->with('info', 'Export All Excel tahun ' . $tahun . ' sedang dikembangkan.');
    }

    /**
     * Export All (PDF) - dari fitur lama
     */
    public function exportAllPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        return back()->with('info', 'Export All PDF tahun ' . $tahun . ' sedang dikembangkan.');
    }
}