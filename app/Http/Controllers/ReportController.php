<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verifikasi;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\ActivityLog;
use App\Models\DataAllProduksi;
use App\Models\KalibrasiInternalListUp;
use App\Models\ToolDefect;
use App\Models\ToolStockMovement;
use App\Models\VerifikasiInternalSchedule;
use App\Models\VerifikasiQc2;
use App\Exports\ActivityLogExport;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $filters = $request->validate(['tab' => 'nullable|in:log,grafik']);
        $tab = $filters['tab'] ?? 'grafik';

        // ===== TAB LOG AKTIVITAS =====
        $logs = $tab === 'log'
            ? $this->filteredActivityLogs($request)->orderBy('id', 'desc')->paginate(25)->withQueryString()
            : null;
        $users = $tab === 'log' ? User::orderBy('name')->get() : collect();

        return view('reports.index', compact('tahun', 'tab', 'logs', 'users'));
    }

    // ==========================================
    // API UNTUK GRAFIK PARETO MANDIRI
    // ==========================================

    public function apiParetoFitur(Request $request)
    {
        $tahun = $this->validatedYear($request);
        return response()->json([
            'labels' => [
                'Daftar Induk', 'Verifikasi', 'Kalibrasi Internal', 'Kalibrasi Eksternal',
                'Kalibrasi Mesin', 'Verifikasi Periode', 'Kalibrasi List Up', 'Verifikasi QC2',
                'Data NG', 'Pergerakan Stock', 'Data All Produksi', 'Log Aktivitas',
            ],
            'values' => [
                Tool::where('tahun', $tahun)->count(),
                Verifikasi::where('tahun', $tahun)->count(),
                Internal::where('tahun', $tahun)->count(),
                Eksternal::where('tahun', $tahun)->count(),
                EksternalMesin::where('tahun', $tahun)->count(),
                VerifikasiInternalSchedule::where('tahun', $tahun)->count(),
                KalibrasiInternalListUp::where('tahun', $tahun)->count(),
                VerifikasiQc2::where('tahun', $tahun)->count(),
                ToolDefect::where('tahun', $tahun)->count(),
                ToolStockMovement::whereYear('movement_date', $tahun)->count(),
                DataAllProduksi::count(),
                ActivityLog::whereYear('created_at', $tahun)->count(),
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

    public function apiChart(Request $request, string $chart): JsonResponse
    {
        $tahun = $this->validatedYear($request);
        $bulanColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $data = match ($chart) {
            'verifikasi-bulanan' => $this->monthlyChartData(Verifikasi::class, $tahun, $bulanColumns, $bulanLabels),
            'internal-bulanan' => $this->monthlyChartData(Internal::class, $tahun, $bulanColumns, $bulanLabels),
            'eksternal-bulanan' => $this->monthlyChartData(Eksternal::class, $tahun, $bulanColumns, $bulanLabels),
            'mesin-bulanan' => $this->monthlyChartData(EksternalMesin::class, $tahun, $bulanColumns, $bulanLabels),
            'produksi-bulanan' => $this->monthlyChartData(DataAllProduksi::class, null, $bulanColumns, $bulanLabels),
            'data-ng' => $this->defectChartData($tahun),
            'stock-bulanan' => $this->stockChartData($tahun, $bulanLabels),
            'verifikasi-periode' => $this->statusChartData(
                VerifikasiInternalSchedule::where('tahun', $tahun)->get(['period1_jgm', 'period2_jgm']),
                ['period1_jgm', 'period2_jgm']
            ),
            'kalibrasi-periode' => $this->statusChartData(
                KalibrasiInternalListUp::where('tahun', $tahun)->get(['jgm', 'period2_jgm']),
                ['jgm', 'period2_jgm']
            ),
            'verifikasi-qc2' => $this->statusChartData(
                VerifikasiQc2::where('tahun', $tahun)->get(['jgm']),
                ['jgm']
            ),
            'activity-actions' => $this->activityChartData($tahun),
            'daily-report' => $this->dailyReportChartData($tahun),
            default => abort(404),
        };

        return response()->json($data);
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, string> $labels
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function monthlyChartData(string $model, ?int $tahun, array $columns, array $labels): array
    {
        $query = $model::query();
        if ($tahun !== null) {
            $query->where('tahun', $tahun);
        }

        $totals = $query->selectRaw(implode(', ', array_map(
            fn (string $column): string => "COALESCE(SUM(`{$column}`), 0) as `{$column}`",
            $columns
        )))->first();

        return [
            'labels' => $labels,
            'values' => array_map(fn (string $column): int => (int) $totals->{$column}, $columns),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function defectChartData(int $tahun): array
    {
        $fields = ToolDefect::COUNT_FIELDS;
        $totals = ToolDefect::where('tahun', $tahun)
            ->selectRaw(implode(', ', array_map(
                fn (string $field): string => "COALESCE(SUM(`{$field}`), 0) as `{$field}`",
                $fields
            )))
            ->first();

        return [
            'labels' => array_map(
                fn (string $field): string => strtoupper(str_replace('_', ' ', $field)),
                $fields
            ),
            'values' => array_map(fn (string $field): int => (int) $totals->{$field}, $fields),
        ];
    }

    /**
     * @param array<int, string> $labels
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function stockChartData(int $tahun, array $labels): array
    {
        $startDate = sprintf('%d-01-01', $tahun);
        $endDate = sprintf('%d-12-31', $tahun);
        $movements = ToolStockMovement::whereBetween('movement_date', [$startDate, $endDate])
            ->get(['movement_date', 'quantity']);
        $monthlyTotals = array_fill(1, 12, 0);

        foreach ($movements as $movement) {
            $monthlyTotals[(int) $movement->movement_date->format('n')] += (int) $movement->quantity;
        }

        return ['labels' => $labels, 'values' => array_values($monthlyTotals)];
    }

    /**
     * @param iterable<object> $records
     * @param array<int, string> $columns
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function statusChartData(iterable $records, array $columns): array
    {
        $counts = [];
        foreach ($records as $record) {
            foreach ($columns as $column) {
                $status = strtoupper(trim((string) ($record->{$column} ?? '')));
                $status = $status !== '' ? $status : 'BELUM DIISI';
                $counts[$status] = ($counts[$status] ?? 0) + 1;
            }
        }
        arsort($counts);

        return ['labels' => array_keys($counts), 'values' => array_values($counts)];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function activityChartData(int $tahun): array
    {
        $counts = ActivityLog::whereYear('created_at', $tahun)
            ->select('aksi')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('aksi')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $counts->pluck('aksi')->all(),
            'values' => $counts->pluck('total')->map(fn ($total): int => (int) $total)->all(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function dailyReportChartData(int $tahun): array
    {
        $counts = ActivityLog::whereYear('created_at', $tahun)
            ->whereIn('jenis_data', ['tools', 'verifikasi', 'internal', 'eksternal', 'mesin', 'user'])
            ->select('jenis_data')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('jenis_data')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $counts->map(fn (ActivityLog $log): string => $log->label_jenis)->all(),
            'values' => $counts->pluck('total')->map(fn ($total): int => (int) $total)->all(),
        ];
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