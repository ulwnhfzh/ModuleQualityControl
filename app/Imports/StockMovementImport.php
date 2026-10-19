<?php

namespace App\Imports;

use App\Models\ToolStock;
use App\Models\ToolStockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class StockMovementImport implements ToCollection, WithHeadingRow
{
    public function __construct(
        private readonly string $category,
        private readonly int $year,
        private readonly ?int $userId,
        private readonly int $rowHeader = 1,
    ) {}

    public function headingRow(): int { return $this->rowHeader; }

    public function collection(Collection $rows): void
    {
        $prepared = [];
        foreach ($rows as $index => $row) {
            $data = $row->toArray();
            $name = trim((string) ($data['tool_name'] ?? $data['nama_alat'] ?? ''));
            if ($name === '' || in_array(mb_strtoupper($name), ['APPROVED', 'CHECKED', 'ISSUED'], true)) {
                continue;
            }
            $date = $this->dateValue($data['movement_date'] ?? $data['tanggal'] ?? null);
            $direction = strtolower(trim((string) ($data['direction'] ?? $data['transaksi'] ?? '')));
            $direction = match ($direction) {
                'masuk', 'barang masuk', 'in' => 'in',
                'keluar', 'barang keluar', 'out' => 'out',
                default => null,
            };
            $rawQuantity = $data['quantity'] ?? $data['jumlah'] ?? null;
            $quantity = is_numeric($rawQuantity) && (float) $rawQuantity === floor((float) $rawQuantity)
                ? (int) $rawQuantity
                : null;
            if (!$date || !$direction || !$quantity || $quantity < 1) {
                throw new RuntimeException('Baris ' . ($index + 2) . ' harus berisi tanggal, transaksi masuk/keluar, dan jumlah positif.');
            }
            if ((int) substr($date, 0, 4) !== $this->year) {
                throw new RuntimeException('Baris ' . ($index + 2) . ': tanggal transaksi harus berada pada tahun ' . $this->year . '.');
            }
            $prepared[] = [
                'row' => $index + 2,
                'name' => $name,
                'order_number' => trim((string) ($data['order_number'] ?? $data['nomor_order'] ?? '')),
                'movement_date' => $date,
                'direction' => $direction,
                'quantity' => $quantity,
                'note' => trim((string) ($data['note'] ?? $data['keterangan'] ?? '')),
            ];
        }

        usort($prepared, fn ($a, $b) => [$a['movement_date'], $a['row']] <=> [$b['movement_date'], $b['row']]);

        DB::transaction(function () use ($prepared) {
            foreach ($prepared as $line) {
                $matches = ToolStock::query()
                    ->where('tool_name', $line['name'])
                    ->where('category', $this->category)
                    ->when($line['order_number'] !== '', fn ($query) => $query->where('order_number', $line['order_number']))
                    ->lockForUpdate()->get();
                if ($matches->count() !== 1) {
                    throw new RuntimeException('Baris ' . $line['row'] . ': alat tidak ditemukan atau nama alat memiliki lebih dari satu nomor order. Isi order_number dengan tepat.');
                }
                $stock = $matches->first();
                $balance = (int) $stock->opening_stock
                    + (int) ToolStockMovement::where('stock_id', $stock->id)->whereDate('movement_date', '<=', $line['movement_date'])->where('direction', 'in')->sum('quantity')
                    - (int) ToolStockMovement::where('stock_id', $stock->id)->whereDate('movement_date', '<=', $line['movement_date'])->where('direction', 'out')->sum('quantity');
                if ($line['direction'] === 'out' && $line['quantity'] > $balance) {
                    throw new RuntimeException('Baris ' . $line['row'] . ': jumlah barang keluar melebihi saldo pada tanggal tersebut.');
                }

                ToolStockMovement::create([
                    'stock_id' => $stock->id,
                    'movement_date' => $line['movement_date'],
                    'direction' => $line['direction'],
                    'quantity' => $line['quantity'],
                    'note' => $line['note'] ?: null,
                    'user_id' => $this->userId,
                ]);
            }
        });
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return is_numeric($value)
                ? ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d')
                : Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
