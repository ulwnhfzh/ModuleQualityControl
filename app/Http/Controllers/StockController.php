<?php

namespace App\Http\Controllers;

use App\Models\ToolStock;
use App\Models\ToolStockMovement;
use App\Imports\StockMovementImport;
use App\Imports\SpreadsheetHeaderRowDetector;
use App\Exports\StockReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    public function summary(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'kategori' => 'nullable|in:Small Tool,Machine,Jig',
        ]);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $kategori = $filters['kategori'] ?? 'Small Tool';
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $stocks = ToolStock::where('category', $kategori)->get();
        $stockIds = $stocks->pluck('id');
        $balance = (int) $stocks->sum('opening_stock');

        if ($stockIds->isNotEmpty()) {
            $priorMovements = ToolStockMovement::whereIn('stock_id', $stockIds)
                ->whereDate('movement_date', '<', "{$tahun}-01-01")
                ->get(['direction', 'quantity']);
            $balance += $priorMovements->sum(fn ($movement) => $movement->direction === 'in'
                ? (int) $movement->quantity
                : -(int) $movement->quantity);
        }

        $monthlyTotals = [];
        if ($stockIds->isNotEmpty()) {
            $yearMovements = ToolStockMovement::whereIn('stock_id', $stockIds)
                ->whereBetween('movement_date', ["{$tahun}-01-01", "{$tahun}-12-31"])
                ->get(['movement_date', 'direction', 'quantity']);

            foreach ($yearMovements as $movement) {
                $month = (int) $movement->movement_date->format('n');
                $monthlyTotals[$month][$movement->direction] =
                    ($monthlyTotals[$month][$movement->direction] ?? 0) + (int) $movement->quantity;
            }
        }

        $rows = [];
        foreach ($months as $month => $label) {
            $incoming = (int) ($monthlyTotals[$month]['in'] ?? 0);
            $outgoing = (int) ($monthlyTotals[$month]['out'] ?? 0);
            $closing = $balance + $incoming - $outgoing;
            $rows[$month] = [
                'label' => $label,
                'opening' => $balance,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'closing' => $closing,
            ];
            $balance = $closing;
        }

        return view('stock.summary', compact('tahun', 'kategori', 'months', 'rows'));
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'kategori' => 'nullable|in:Small Tool,Machine,Jig',
        ]);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $kategori = $filters['kategori'] ?? 'Small Tool';
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $stocks = ToolStock::query()
            ->where('category', $kategori)
            ->orderBy('id')
            ->get();

        $yearStart = "{$tahun}-01-01";
        $priorMovements = ToolStockMovement::query()
            ->whereDate('movement_date', '<', $yearStart)
            ->when($kategori, fn ($query) => $query->whereHas('stockInfo', fn ($stockQuery) => $stockQuery->where('category', $kategori)))
            ->get(['stock_id', 'direction', 'quantity'])
            ->groupBy('stock_id');
        $openingByTool = [];
        foreach ($priorMovements as $toolId => $items) {
            $openingByTool[$toolId] = $items->sum(fn ($item) => $item->direction === 'in' ? $item->quantity : -$item->quantity);
        }

        $monthlyTotals = [];
        $movements = ToolStockMovement::with('stockInfo')
            ->whereBetween('movement_date', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($kategori, fn ($query) => $query->whereHas('stockInfo', fn ($stockQuery) => $stockQuery->where('category', $kategori)))
            ->orderByDesc('movement_date')->orderByDesc('id')->get();

        foreach ($movements as $movement) {
            $month = (int) $movement->movement_date->format('n');
            $monthlyTotals[$movement->stock_id][$month][$movement->direction] =
                ($monthlyTotals[$movement->stock_id][$month][$movement->direction] ?? 0) + $movement->quantity;
        }

        $movementSlots = $movements->groupBy('stock_id')->map(function ($items) {
            return $items->groupBy(fn ($item) => (int) $item->movement_date->format('n'))->map(function ($monthItems) {
                return $monthItems->groupBy('direction')->map(fn ($directionItems) => $directionItems
                    ->take(4)
                    ->map(fn ($item) => [
                        'quantity' => (int) $item->quantity,
                        'date' => $item->movement_date->format('d/m'),
                    ])->values()->all())->all();
            });
        });

        $movementDates = $movements->groupBy('stock_id')->map(function ($items) {
            return $items->groupBy(fn ($item) => (int) $item->movement_date->format('n'))->map(function ($monthItems) {
                return [
                    'in' => $monthItems->where('direction', 'in')->map(fn ($item) => $item->movement_date->format('d/m'))->values()->all(),
                    'out' => $monthItems->where('direction', 'out')->map(fn ($item) => $item->movement_date->format('d/m'))->values()->all(),
                ];
            });
        });

        $rows = $stocks->map(function (ToolStock $stock) use ($months, $monthlyTotals, $openingByTool, $movementDates, $movementSlots) {
            $balance = (int) $stock->opening_stock + (int) ($openingByTool[$stock->id] ?? 0);
            $monthly = [];
            foreach ($months as $monthNumber => $monthName) {
                $incoming = (int) ($monthlyTotals[$stock->id][$monthNumber]['in'] ?? 0);
                $outgoing = (int) ($monthlyTotals[$stock->id][$monthNumber]['out'] ?? 0);
                $opening = $balance;
                $balance += $incoming - $outgoing;
                $monthly[$monthNumber] = compact('opening', 'incoming', 'outgoing', 'balance');
            }

            $dates = collect($movementDates->get($stock->id, []));

            return [
                'stock' => $stock,
                'monthly' => $monthly,
                'movement_dates' => $dates,
                'movement_slots' => collect($movementSlots->get($stock->id, [])),
                'closing_stock' => $balance,
            ];
        });

        return view('stock.index', compact('tahun', 'kategori', 'months', 'stocks', 'rows', 'movements'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stock_id' => 'required|exists:tool_stocks,id',
            'movement_date' => 'required|date',
            'direction' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1|max:2147483647',
            'note' => 'nullable|string|max:255',
        ]);

        $stock = DB::transaction(function () use ($data) {
            $stock = ToolStock::whereKey($data['stock_id'])->lockForUpdate()->firstOrFail();
            $movementsToDate = ToolStockMovement::where('stock_id', $stock->id)
                ->whereDate('movement_date', '<=', $data['movement_date']);
            $balanceAtDate = (int) $stock->opening_stock
                + (int) (clone $movementsToDate)->where('direction', 'in')->sum('quantity')
                - (int) (clone $movementsToDate)->where('direction', 'out')->sum('quantity');

            if ($data['direction'] === 'out' && $data['quantity'] > $balanceAtDate) {
                return null;
            }

            ToolStockMovement::create($data + ['user_id' => Auth::id()]);
            return $stock;
        });

        if (!$stock) {
            return back()->withInput()->withErrors([
                'quantity' => 'Jumlah keluar melebihi stok yang tersedia pada tanggal transaksi.',
            ]);
        }

        return redirect()->route('stock.index', [
            'tahun' => substr($data['movement_date'], 0, 4),
            'kategori' => $stock->category,
        ])->with('success', 'Transaksi stok berhasil dicatat.');
    }

    public function storeGrid(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'kategori' => 'required|in:Small Tool,Machine,Jig',
            'entries' => 'nullable|array',
            'entries.*.stock_id' => 'nullable|integer|exists:tool_stocks,id',
            'entries.*.tool_name' => 'nullable|string|max:255',
            'entries.*.type' => 'nullable|string|max:100',
            'entries.*.order_number' => 'nullable|string|max:100',
            'entries.*.specification' => 'nullable|string|max:255',
            'entries.*.opening_stock' => 'nullable|integer|min:0|max:2147483647',
            'entries.*.months' => 'nullable|array',
            'entries.*.months.*.in.*.quantity' => 'nullable|integer|min:0|max:2147483647',
            'entries.*.months.*.in.*.date' => 'nullable|date',
            'entries.*.months.*.out.*.quantity' => 'nullable|integer|min:0|max:2147483647',
            'entries.*.months.*.out.*.date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['entries'] ?? [] as $entry) {
                $name = trim((string) ($entry['tool_name'] ?? ''));
                $type = trim((string) ($entry['type'] ?? ''));
                $orderNumber = trim((string) ($entry['order_number'] ?? ''));
                $specification = trim((string) ($entry['specification'] ?? ''));
                $hasMovement = collect($entry['months'] ?? [])->contains(function ($monthData) {
                    foreach (['in', 'out'] as $direction) {
                        foreach ($monthData[$direction] ?? [] as $slot) {
                            if ((int) ($slot['quantity'] ?? 0) > 0) return true;
                        }
                    }
                    return false;
                });

                if (empty($entry['stock_id'])) {
                    if ($name === '' && $type === '' && $orderNumber === '' && $specification === '' && !$hasMovement) continue;
                    if ($name === '' || $type === '') {
                        throw ValidationException::withMessages(['entries' => 'Setiap baris alat baru wajib diisi Nama Alat Ukur dan Type.']);
                    }
                    $duplicate = ToolStock::where('category', $data['kategori'])
                        ->whereRaw('LOWER(tool_name) = ?', [mb_strtolower($name)])
                        ->where('order_number', $orderNumber !== '' ? $orderNumber : null)
                        ->exists();
                    if ($duplicate) {
                        throw ValidationException::withMessages(['entries' => "Alat {$name} dengan nomor order tersebut sudah terdaftar di Data Stock."]);
                    }
                    $stock = ToolStock::create([
                        'tool_name' => mb_strtoupper($name),
                        'type' => mb_strtoupper($type),
                        'category' => $data['kategori'],
                        'order_number' => $orderNumber !== '' ? $orderNumber : null,
                        'specification' => $specification !== '' ? $specification : null,
                        'opening_stock' => (int) ($entry['opening_stock'] ?? 0),
                    ]);
                } else {
                    $stock = ToolStock::whereKey($entry['stock_id'])->lockForUpdate()->firstOrFail();
                    if ($stock->category !== $data['kategori']) {
                        throw ValidationException::withMessages(['entries' => 'Ada alat yang tidak termasuk kategori yang dipilih. Muat ulang halaman lalu coba lagi.']);
                    }
                }

                foreach ($entry['months'] ?? [] as $month => $directions) {
                    $month = (int) $month;
                    if ($month < 1 || $month > 12) {
                        throw ValidationException::withMessages(['entries' => 'Bulan transaksi tidak valid.']);
                    }
                    foreach (['in', 'out'] as $direction) {
                        foreach ($directions[$direction] ?? [] as $slot => $movement) {
                            $quantity = (int) ($movement['quantity'] ?? 0);
                            if ($quantity === 0) continue;

                            $date = $movement['date'] ?? null;
                            if (!$date) {
                                throw ValidationException::withMessages(['entries' => "Tanggal untuk transaksi {$direction} bulan {$month}, slot {$slot} wajib diisi."]);
                            }
                            $dateValue = \Illuminate\Support\Carbon::parse($date);
                            if ((int) $dateValue->format('Y') !== (int) $data['tahun'] || (int) $dateValue->format('n') !== $month) {
                                throw ValidationException::withMessages(['entries' => 'Tanggal transaksi harus sesuai dengan tahun dan bulan pada kolomnya.']);
                            }

                            if ($direction === 'out') {
                                $movementsToDate = ToolStockMovement::where('stock_id', $stock->id)->whereDate('movement_date', '<=', $date);
                                $balanceAtDate = (int) $stock->opening_stock
                                    + (int) (clone $movementsToDate)->where('direction', 'in')->sum('quantity')
                                    - (int) (clone $movementsToDate)->where('direction', 'out')->sum('quantity');
                                if ($quantity > $balanceAtDate) {
                                    throw ValidationException::withMessages(['entries' => "Jumlah keluar {$stock->tool_name} melebihi stok yang tersedia pada tanggal {$dateValue->format('d/m/Y')}."]);
                                }
                            }

                            ToolStockMovement::create([
                                'stock_id' => $stock->id,
                                'movement_date' => $dateValue->toDateString(),
                                'direction' => $direction,
                                'quantity' => $quantity,
                                'note' => 'Input tabel stock ' . $data['tahun'],
                                'user_id' => Auth::id(),
                            ]);
                        }
                    }
                }
            }
        });

        $returnFilters = ['tahun' => $data['tahun'], 'kategori' => $data['kategori']];
        if ($request->input('save_mode') === 'another') {
            $returnFilters['tambah'] = 1;
        }

        return redirect()->route('stock.index', $returnFilters)
            ->with('success', 'Input stock berhasil disimpan.');
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'kategori' => 'required|in:Small Tool,Machine,Jig',
            'tool_name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'order_number' => 'nullable|string|max:100',
            'specification' => 'nullable|string|max:255',
            'opening_stock' => 'required|integer|min:0|max:2147483647',
        ]);

        $duplicate = ToolStock::where('category', $data['kategori'])
            ->whereRaw('LOWER(tool_name) = ?', [mb_strtolower(trim($data['tool_name']))])
            ->where('order_number', $data['order_number'] ?? null)
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['tool_name' => 'Alat dengan nama dan nomor order yang sama sudah ada pada kategori ini.']);
        }

        ToolStock::create([
            'tool_name' => mb_strtoupper(trim($data['tool_name'])),
            'type' => mb_strtoupper(trim($data['type'])),
            'category' => $data['kategori'],
            'order_number' => $data['order_number'] ?: null,
            'specification' => $data['specification'] ?: null,
            'opening_stock' => $data['opening_stock'],
        ]);

        return redirect()->route('stock.index', [
            'tahun' => $data['tahun'], 'kategori' => $data['kategori'],
        ])->with('success', 'Alat ukur berhasil ditambahkan ke Data Stock QA Plant 2.');
    }

    public function importMovements(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'required|digits:4',
            'kategori' => 'required|in:Small Tool,Machine,Jig',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $rowHeader = SpreadsheetHeaderRowDetector::detect($request->file('file'));
            Excel::import(new StockMovementImport($data['kategori'], (int) $data['tahun'], Auth::id(), $rowHeader), $request->file('file'));
        } catch (\Throwable $exception) {
            return redirect()->route('stock.import.form', ['tahun' => $data['tahun'], 'kategori' => $data['kategori']])
                ->withErrors(['file' => 'Import dibatalkan: ' . $exception->getMessage()]);
        }

        return redirect()->route('stock.index', ['tahun' => $data['tahun'], 'kategori' => $data['kategori']])
            ->with('success', 'Transaksi stok berhasil diimpor.');
    }

    public function importForm(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'kategori' => 'nullable|in:Small Tool,Machine,Jig',
        ]);
        $tahun = (int) ($data['tahun'] ?? date('Y'));
        $kategori = $data['kategori'] ?? 'Small Tool';
        return view('stock.import', compact('tahun', 'kategori'));
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'kategori' => 'nullable|in:Small Tool,Machine,Jig',
        ]);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $kategori = $filters['kategori'] ?? 'Small Tool';

        return Excel::download(new StockReportExport($tahun, $kategori), "stok-{$kategori}-{$tahun}.xlsx");
    }

}
