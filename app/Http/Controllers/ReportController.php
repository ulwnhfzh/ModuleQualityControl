<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verifikasi;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\ActivityLog;
use App\Exports\ActivityLogExport;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $filters = $request->validate(['tab' => 'nullable|in:ringkasan,log,grafik']);
        $tab = $filters['tab'] ?? 'ringkasan';

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
        $logs = $this->filteredActivityLogs($request)->orderBy('id', 'desc')->paginate(25)->withQueryString();

        $users = User::orderBy('name')->get();

        // ===== TAB GRAFIK (DATA AWAL) =====
        $bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $tahunRange = range(date('Y'), date('Y') - 4);
        sort($tahunRange);
        $tahunLabels = array_map('strval', $tahunRange);

        $verifikasiPerBulan = $internalPerBulan = $eksternalPerBulan = $mesinPerBulan = [];
        $verifikasiPerTahun = $internalPerTahun = $eksternalPerTahun = $mesinPerTahun = $toolsPerTahun = [];

        if ($tab === 'grafik') {
            $monthlyTotals = [];
            foreach ([
                'verifikasi' => Verifikasi::class,
                'internal' => Internal::class,
                'eksternal' => Eksternal::class,
                'mesin' => EksternalMesin::class,
            ] as $key => $model) {
                $totals = $model::where('tahun', $tahun)
                    ->selectRaw(implode(', ', array_map(fn ($column) => "SUM(`{$column}`) as `{$column}`", $bulanColumns)))
                    ->first();
                $monthlyTotals[$key] = array_map(fn ($column) => (int) $totals->{$column}, $bulanColumns);
            }

            $verifikasiPerBulan = $monthlyTotals['verifikasi'];
            $internalPerBulan = $monthlyTotals['internal'];
            $eksternalPerBulan = $monthlyTotals['eksternal'];
            $mesinPerBulan = $monthlyTotals['mesin'];

            $yearlyCounts = [];
            foreach ([
                'verifikasi' => Verifikasi::class,
                'internal' => Internal::class,
                'eksternal' => Eksternal::class,
                'mesin' => EksternalMesin::class,
                'tools' => Tool::class,
            ] as $key => $model) {
                $yearlyCounts[$key] = $model::whereIn('tahun', $tahunRange)
                    ->select('tahun')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('tahun')
                    ->pluck('total', 'tahun');
            }

            $verifikasiPerTahun = array_map(fn ($year) => (int) $yearlyCounts['verifikasi']->get($year, 0), $tahunRange);
            $internalPerTahun = array_map(fn ($year) => (int) $yearlyCounts['internal']->get($year, 0), $tahunRange);
            $eksternalPerTahun = array_map(fn ($year) => (int) $yearlyCounts['eksternal']->get($year, 0), $tahunRange);
            $mesinPerTahun = array_map(fn ($year) => (int) $yearlyCounts['mesin']->get($year, 0), $tahunRange);
            $toolsPerTahun = array_map(fn ($year) => (int) $yearlyCounts['tools']->get($year, 0), $tahunRange);
        }

        return view('reports.index', compact(
            'tahun', 'tab',
            'totalTools', 'totalVerifikasi', 'totalInternal', 'totalEksternal', 'totalMesin',
            'toolsSmallTool', 'toolsMachine', 'toolsJig',
            'logs', 'users',
            // Data grafik awal
            'bulanLabels', 'verifikasiPerBulan', 'internalPerBulan', 'eksternalPerBulan', 'mesinPerBulan',
            'tahunLabels', 'verifikasiPerTahun', 'internalPerTahun', 'eksternalPerTahun', 'mesinPerTahun', 'toolsPerTahun'
        ));
    }

    // ==========================================
    // API UNTUK GRAFIK PARETO MANDIRI
    // ==========================================

    public function apiParetoFitur(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return response()->json([
            'labels' => ['Daftar Induk', 'Verifikasi', 'Kalibrasi Internal', 'Kalibrasi Eksternal', 'Kalibrasi Mesin'],
            'values' => [
                Tool::where('tahun', $tahun)->count(),
                Verifikasi::where('tahun', $tahun)->count(),
                Internal::where('tahun', $tahun)->count(),
                Eksternal::where('tahun', $tahun)->count(),
                EksternalMesin::where('tahun', $tahun)->count(),
            ]
        ]);
    }

    public function apiParetoKategori(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return response()->json([
            'labels' => ['Small Tool', 'Machine', 'Jig'],
            'values' => [
                Tool::where('tahun', $tahun)->where('category', 'Small Tool')->count(),
                Tool::where('tahun', $tahun)->where('category', 'Machine')->count(),
                Tool::where('tahun', $tahun)->where('category', 'Jig')->count(),
            ]
        ]);
    }

    public function apiParetoBulanan(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $bulanColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $values = [];
        foreach ($bulanColumns as $col) {
            $values[] = (int) Verifikasi::where('tahun', $tahun)->sum($col)
                      + (int) Internal::where('tahun', $tahun)->sum($col)
                      + (int) Eksternal::where('tahun', $tahun)->sum($col)
                      + (int) EksternalMesin::where('tahun', $tahun)->sum($col);
        }

        return response()->json(['labels' => $bulanLabels, 'values' => $values]);
    }

    public function apiParetoDepartemen(Request $request)
    {
        $tahun = $this->validatedYear($request);
        
        // 1 Query saja untuk mengambil semua sum kolom departemen (Optimasi dari 15 Query)
        $data = Verifikasi::where('tahun', $tahun)
            ->selectRaw('
                SUM(qa_1) as QA_1, SUM(qa_1_2) as QA_12, SUM(qa_inc) as QA_INC, 
                SUM(qa_insp) as QA_INSP, SUM(ppic_1) as PPIC_1, SUM(ppic_2) as PPIC_2, 
                SUM(line_scc) as LINE_SCC, SUM(p1) as P1, SUM(p2) as P2, SUM(p3) as P3, 
                SUM(p5) as P5, SUM(p6) as P6, SUM(p7) as P7, SUM(p8) as P8, SUM(eng) as ENG
            ')->first();

        return response()->json([
            'labels' => ['QA 1', 'QA 1.2', 'QA INC', 'QA INSP', 'PPIC 1', 'PPIC 2', 'LINE SCC', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'ENG'],
            'values' => [
                (int)$data->QA_1, (int)$data->QA_12, (int)$data->QA_INC, (int)$data->QA_INSP,
                (int)$data->PPIC_1, (int)$data->PPIC_2, (int)$data->LINE_SCC,
                (int)$data->P1, (int)$data->P2, (int)$data->P3, (int)$data->P5,
                (int)$data->P6, (int)$data->P7, (int)$data->P8, (int)$data->ENG
            ]
        ]);
    }

    // ==========================================
    // EXPORT LOGS & ALL
    // ==========================================

    public function exportLogExcel(Request $request)
    {
        $logs = $this->filteredActivityLogs($request)->orderBy('id', 'desc')->get();

        $this->logExport('log', 'Export Log Aktivitas ke Excel');

        $filename = 'log-aktivitas-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new ActivityLogExport($logs, 'LOG AKTIVITAS SISTEM', 'Dicetak: ' . now()->format('d/m/Y H:i:s'), 'd/m/Y H:i:s'),
            $filename
        );
    }

    public function exportLogPdf(Request $request)
    {
        $logs = $this->filteredActivityLogs($request)->orderBy('id', 'desc')->limit(500)->get();

        $this->logExport('log', 'Export Log Aktivitas ke PDF');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.log-pdf', compact('logs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('log-aktivitas-' . date('Y-m-d-His') . '.pdf');
    }

    public function exportAllExcel(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return back()->with('info', 'Export All Excel tahun ' . $tahun . ' sedang dikembangkan.');
    }

    public function exportAllPdf(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return back()->with('info', 'Export All PDF tahun ' . $tahun . ' sedang dikembangkan.');
    }

    private function validatedYear(Request $request): int
    {
        $data = $request->validate(['tahun' => 'nullable|integer|between:2000,2100']);
        return (int) ($data['tahun'] ?? date('Y'));
    }

    private function filteredActivityLogs(Request $request)
    {
        $filters = $request->validate([
            'tanggal' => 'nullable|date_format:Y-m-d',
            'user_id' => 'nullable|integer|exists:users,id',
            'jenis' => 'nullable|in:tools,verifikasi,internal,eksternal,mesin,user',
            'aksi' => 'nullable|in:CREATE,UPDATE,DELETE,LOGIN,LOGOUT,EXPORT',
            'search' => 'nullable|string|max:255',
        ]);

        return ActivityLog::query()
            ->when($filters['tanggal'] ?? null, fn ($query, $tanggal) => $query->whereDate('created_at', $tanggal))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['jenis'] ?? null, fn ($query, $jenis) => $query->where('jenis_data', $jenis))
            ->when($filters['aksi'] ?? null, fn ($query, $aksi) => $query->where('aksi', $aksi))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('keterangan', 'like', '%' . $search . '%'));
    }
}