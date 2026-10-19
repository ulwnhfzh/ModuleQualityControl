<?php

namespace App\Exports;

use App\Models\ToolStock;
use App\Models\ToolStockMovement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class StockReportExport implements FromCollection, WithHeadings, WithEvents
{
    private array $headings = [];

    public function __construct(private readonly int $tahun, private readonly string $kategori)
    {
    }

    public function headings(): array
    {
        $columns = ['Nama Alat', 'Stock Awal Tahun', 'Type', 'Nomor Order', 'Spesifikasi'];
        foreach (['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $month) {
            $columns = array_merge($columns, ["{$month} Stock", "{$month} IN", "{$month} OUT", "{$month} Stock Akhir"]);
        }
        foreach (['Kedatangan 1', 'Kedatangan 2', 'Kedatangan 3', 'Kedatangan 4', 'Dikeluarkan 1', 'Dikeluarkan 2', 'Dikeluarkan 3', 'Dikeluarkan 4'] as $date) {
            $columns[] = $date;
        }
        $columns[] = 'Stock Akhir Tahun';
        return $this->headings = $columns;
    }

    public function collection(): Collection
    {
        $stocks = ToolStock::where('category', $this->kategori)->orderBy('id')->get();
        $movements = ToolStockMovement::with('stockInfo')
            ->whereBetween('movement_date', ["{$this->tahun}-01-01", "{$this->tahun}-12-31"])
            ->whereHas('stockInfo', fn ($query) => $query->where('category', $this->kategori))
            ->orderByDesc('movement_date')->orderByDesc('id')->get();
        $prior = ToolStockMovement::whereDate('movement_date', '<', "{$this->tahun}-01-01")
            ->whereHas('stockInfo', fn ($query) => $query->where('category', $this->kategori))->get()
            ->groupBy('stock_id')->map(fn ($items) => $items->sum(fn ($item) => $item->direction === 'in' ? $item->quantity : -$item->quantity));

        $totals = [];
        $dates = [];
        foreach ($movements as $movement) {
            $month = (int) $movement->movement_date->format('n');
            $totals[$movement->stock_id][$month][$movement->direction] = ($totals[$movement->stock_id][$month][$movement->direction] ?? 0) + $movement->quantity;
            $dates[$movement->stock_id][$movement->direction][] = $movement->movement_date->format('d/m');
        }

        $rows = collect();
        foreach ($stocks as $stock) {
            $balance = (int) $stock->opening_stock + (int) ($prior->get($stock->id) ?? 0);
            $row = [$stock->tool_name, $balance, $stock->type, $stock->order_number, $stock->specification];
            for ($month = 1; $month <= 12; $month++) {
                $incoming = (int) ($totals[$stock->id][$month]['in'] ?? 0);
                $outgoing = (int) ($totals[$stock->id][$month]['out'] ?? 0);
                $opening = $balance;
                $balance += $incoming - $outgoing;
                array_push($row, $opening, $incoming, $outgoing, $balance);
            }
            $inDates = array_slice($dates[$stock->id]['in'] ?? [], 0, 4);
            $outDates = array_slice($dates[$stock->id]['out'] ?? [], 0, 4);
            foreach (array_pad($inDates, 4, '') as $date) $row[] = $date;
            foreach (array_pad($outDates, 4, '') as $date) $row[] = $date;
            $row[] = $balance;
            $rows->push($row);
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
            DocumentExportLayout::apply($event->sheet->getDelegate(), $lastColumn, "DATA STOK ALAT UKUR - {$this->kategori}", (string) $this->tahun);
        }];
    }
}
