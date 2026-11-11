<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verification;
use App\Models\Internal;
use App\Models\External;
use App\Models\ExternalMachine;
use App\Models\ActivityLog;
use App\Models\ProductionData;
use App\Models\InternalCalibrationListUp;
use App\Models\ToolDefect;
use App\Models\ToolStockMovement;
use App\Models\InternalVerificationSchedule;
use App\Models\Qc2Verification;
use App\Exports\ActivityLogExport;
use App\Exports\QualityControlWorkbookExport;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
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

        return view('reports.report', compact('tahun', 'tab', 'logs', 'users'));
    }

    // ==========================================
    // API UNTUK GRAFIK PARETO MANDIRI
    // ==========================================

    public function apiParetoFitur(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $startDate = sprintf('%d-01-01 00:00:00', $tahun);
        $endDate = sprintf('%d-12-31 23:59:59', $tahun);
        $query = DB::query();
        $yearModels = [
            'tools' => Tool::class,
            'verifikasi' => Verification::class,
            'internal' => Internal::class,
            'eksternal' => External::class,
            'mesin' => ExternalMachine::class,
            'periode' => InternalVerificationSchedule::class,
            'list_up' => InternalCalibrationListUp::class,
            'qc2' => Qc2Verification::class,
            'ng' => ToolDefect::class,
        ];
        foreach ($yearModels as $alias => $model) {
            $query->selectSub($model::query()->where('tahun', $tahun)->selectRaw('COUNT(*)'), $alias);
        }
        $query->selectSub(
            ToolStockMovement::query()->whereBetween('movement_date', [substr($startDate, 0, 10), substr($endDate, 0, 10)])->selectRaw('COUNT(*)'),
            'stock'
        );
        $query->selectSub(ProductionData::query()->selectRaw('COUNT(*)'), 'produksi');
        $query->selectSub(ActivityLog::query()->whereBetween('created_at', [$startDate, $endDate])->selectRaw('COUNT(*)'), 'log');
        $counts = $query->first();

        return response()->json([
            'labels' => [
                'Daftar Induk', 'Verification', 'Kalibrasi Internal', 'Kalibrasi External',
                'Kalibrasi Machine', 'Verification Periode', 'Kalibrasi List Up', 'Verification QC2',
                'Data NG', 'Pergerakan Stock', 'Data All Produksi', 'Log Aktivitas',
            ],
            'values' => [
                (int) $counts->tools,
                (int) $counts->verifikasi,
                (int) $counts->internal,
                (int) $counts->eksternal,
                (int) $counts->mesin,
                (int) $counts->periode,
                (int) $counts->list_up,
                (int) $counts->qc2,
                (int) $counts->ng,
                (int) $counts->stock,
                (int) $counts->produksi,
                (int) $counts->log,
            ]
        ]);
    }

    public function apiParetoKategori(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $counts = Tool::where('tahun', $tahun)
            ->select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return response()->json([
            'labels' => ['Small Tool', 'Machine', 'Jig'],
            'values' => [
                (int) ($counts['Small Tool'] ?? 0),
                (int) ($counts['Machine'] ?? 0),
                (int) ($counts['Jig'] ?? 0),
            ],
        ]);
    }

    public function apiParetoBulanan(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $bulanColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        $comparison = $this->monthlyComparisonChartData($tahun, $bulanColumns, $bulanLabels);
        $values = array_map(
            fn (int $index): int => array_sum(array_map(fn (array $dataset): int => (int) $dataset['data'][$index], $comparison['datasets'])),
            array_keys($bulanLabels)
        );

        return response()->json(['labels' => $bulanLabels, 'values' => $values]);
    }

    public function apiParetoDepartemen(Request $request)
    {
        $tahun = $this->validatedYear($request);
        
        // 1 Query saja untuk mengambil semua sum kolom departemen (Optimasi dari 15 Query)
        $data = Verification::where('tahun', $tahun)
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
            'verifikasi-bulanan' => $this->monthlyChartData(Verification::class, $tahun, $bulanColumns, $bulanLabels),
            'internal-bulanan' => $this->monthlyChartData(Internal::class, $tahun, $bulanColumns, $bulanLabels),
            'eksternal-bulanan' => $this->monthlyChartData(External::class, $tahun, $bulanColumns, $bulanLabels),
            'mesin-bulanan' => $this->monthlyChartData(ExternalMachine::class, $tahun, $bulanColumns, $bulanLabels),
            'produksi-bulanan' => $this->productionChartData($bulanColumns, $bulanLabels),
            'operasional-bulanan' => $this->monthlyComparisonChartData($tahun, $bulanColumns, $bulanLabels),
            'data-ng' => $this->defectChartData($tahun),
            'stock-bulanan' => $this->stockChartData($tahun, $bulanLabels),
            'verifikasi-periode' => $this->statusChartData(InternalVerificationSchedule::class, $tahun, ['period1_jgm', 'period2_jgm']),
            'kalibrasi-periode' => $this->statusChartData(InternalCalibrationListUp::class, $tahun, ['jgm', 'period2_jgm']),
            'verifikasi-qc2' => $this->qc2ChartData($tahun),
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
     * Data All Produksi stores values such as OK/NG as text, so SUM() would
     * silently turn those results into zero. Count populated monthly cells instead.
     *
     * @param array<int, string> $columns
     * @param array<int, string> $labels
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function productionChartData(array $columns, array $labels): array
    {
        $selects = array_map(
            fn (string $column): string => "SUM(CASE WHEN `{$column}` IS NOT NULL AND TRIM(CAST(`{$column}` AS CHAR)) <> '' THEN 1 ELSE 0 END) as `{$column}`",
            $columns
        );
        $totals = ProductionData::query()->selectRaw(implode(', ', $selects))->first();

        return [
            'labels' => $labels,
            'values' => array_map(fn (string $column): int => (int) ($totals->{$column} ?? 0), $columns),
        ];
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, string> $labels
     * @return array{labels: array<int, string>, datasets: array<int, array{label: string, data: array<int, int>}>}
     */
    private function monthlyComparisonChartData(int $tahun, array $columns, array $labels): array
    {
        $series = [
            'Verification' => Verification::class,
            'Kalibrasi internal' => Internal::class,
            'Kalibrasi eksternal' => External::class,
            'Kalibrasi mesin' => ExternalMachine::class,
        ];

        $datasets = [];
        foreach ($series as $label => $model) {
            $monthly = $this->monthlyChartData($model, $tahun, $columns, $labels);
            $datasets[] = ['label' => $label, 'data' => $monthly['values']];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function defectChartData(int $tahun): array
    {
        $fields = ToolDefect::COUNT_FIELDS;
        $availableFields = Schema::getColumnListing('tool_defects');
        $errorField = in_array('error', $availableFields, true)
            ? 'error'
            : (in_array('erorr', $availableFields, true) ? 'erorr' : null);

        $selects = [];
        foreach ($fields as $field) {
            if ($field === 'erorr') {
                if ($errorField !== null) {
                    $selects[] = "COALESCE(SUM(`{$errorField}`), 0) as `erorr`";
                }
                continue;
            }
            if (in_array($field, $availableFields, true)) {
                $selects[] = "COALESCE(SUM(`{$field}`), 0) as `{$field}`";
            }
        }

        $totals = empty($selects)
            ? null
            : ToolDefect::where('tahun', $tahun)->selectRaw(implode(', ', $selects))->first();

        return [
            'labels' => array_map(
                fn (string $field): string => $field === 'erorr' ? 'ERROR' : strtoupper(str_replace('_', ' ', $field)),
                $fields
            ),
            'values' => array_map(fn (string $field): int => (int) ($totals->{$field} ?? 0), $fields),
        ];
    }

    /**
     * Supports both the single-period QC2 schema and the two-period schema.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function qc2ChartData(int $tahun): array
    {
        $availableColumns = Schema::getColumnListing('verifikasi_qc2');
        $columns = array_values(array_filter(
            ['jgm', 'period1_jgm', 'period2_jgm'],
            fn (string $column): bool => in_array($column, $availableColumns, true)
        ));

        if (empty($columns)) {
            return ['labels' => [], 'values' => []];
        }

        return $this->statusChartData(Qc2Verification::class, $tahun, $columns);
    }

    /**
     * @param array<int, string> $labels
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function stockChartData(int $tahun, array $labels): array
    {
        $startDate = sprintf('%d-01-01', $tahun);
        $endDate = sprintf('%d-12-31', $tahun);
        $rows = ToolStockMovement::query()
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->selectRaw('MONTH(movement_date) as month_number')
            ->selectRaw("SUM(CASE WHEN direction = 'in' THEN quantity ELSE 0 END) as incoming")
            ->selectRaw("SUM(CASE WHEN direction = 'out' THEN quantity ELSE 0 END) as outgoing")
            ->groupByRaw('MONTH(movement_date)')
            ->get()
            ->keyBy('month_number');

        return [
            'labels' => $labels,
            'values' => array_map(
                fn (int $month): int => (int) ($rows[$month]->incoming ?? 0) + (int) ($rows[$month]->outgoing ?? 0),
                range(1, 12)
            ),
            'datasets' => [
                [
                    'label' => 'Barang masuk',
                    'data' => array_map(fn (int $month): int => (int) ($rows[$month]->incoming ?? 0), range(1, 12)),
                ],
                [
                    'label' => 'Barang keluar',
                    'data' => array_map(fn (int $month): int => (int) ($rows[$month]->outgoing ?? 0), range(1, 12)),
                ],
            ],
        ];
    }

    /**
     * @param iterable<object> $records
     * @param array<int, string> $columns
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function statusChartData(string $model, int $tahun, array $columns): array
    {
        $table = (new $model())->getTable();
        $availableColumns = Schema::getColumnListing($table);
        $columns = array_values(array_filter($columns, fn (string $column): bool => in_array($column, $availableColumns, true)));
        if (empty($columns)) {
            return ['labels' => [], 'values' => []];
        }

        $statusQueries = [];
        foreach ($columns as $column) {
            $statusQueries[] = DB::table($table)
                ->where('tahun', $tahun)
                ->selectRaw("UPPER(TRIM(COALESCE(NULLIF(`{$column}`, ''), 'BELUM DIISI'))) as status")
                ->selectRaw('COUNT(*) as total')
                ->groupBy('status');
        }

        $union = array_shift($statusQueries);
        foreach ($statusQueries as $query) {
            $union->unionAll($query);
        }

        $counts = DB::query()
            ->fromSub($union, 'period_statuses')
            ->select('status')
            ->selectRaw('SUM(total) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $counts->pluck('status')->all(),
            'values' => $counts->pluck('total')->map(fn ($total): int => (int) $total)->all(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    private function activityChartData(int $tahun): array
    {
        $startDate = sprintf('%d-01-01 00:00:00', $tahun);
        $endDate = sprintf('%d-12-31 23:59:59', $tahun);
        $counts = ActivityLog::whereBetween('created_at', [$startDate, $endDate])
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
        $startDate = sprintf('%d-01-01 00:00:00', $tahun);
        $endDate = sprintf('%d-12-31 23:59:59', $tahun);
        $counts = ActivityLog::whereBetween('created_at', [$startDate, $endDate])
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.activity-log-pdf', compact('logs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('log-aktivitas-' . date('Y-m-d-His') . '.pdf');
    }

    public function exportAllExcel(Request $request)
    {
        $tahun = $this->validatedYear($request);
        $this->logExport('report', 'Export seluruh data ke Excel tahun ' . $tahun);

        return Excel::download(
            new QualityControlWorkbookExport($tahun),
            'quality-control-' . $tahun . '-' . date('Ymd-His') . '.xlsx'
        );
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
