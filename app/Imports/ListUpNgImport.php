<?php

namespace App\Imports;

use App\Models\ListUpNg;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use RuntimeException;

class ListUpNgImport implements ToCollection, WithStartRow
{
    private int $importedCount = 0;

    public function __construct(
        private readonly int $rowHeader = 1,
        private readonly ?int $expectedYear = null,
    ) {
    }

    public function startRow(): int
    {
        return $this->rowHeader;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function collection(Collection $rows): void
    {
        $rows = $rows->map(fn ($row) => $row->toArray())->values()->all();
        if ($rows === []) {
            return;
        }

        $header = $rows[0];
        $hasSplitHeader = isset($rows[1]) && $this->isSplitSubheader($rows[1]);
        $subheader = $hasSplitHeader ? $rows[1] : [];
        $columns = $this->columnMap($header, $subheader);
        $required = ['tool_name', 'ng_date', 'division', 'category_ng'];
        if (array_diff($required, array_keys($columns)) !== []) {
            throw new RuntimeException('Header List Up NG tidak memiliki kolom alat, tanggal NG, division, dan kategori NG.');
        }

        foreach (array_slice($rows, $hasSplitHeader ? 2 : 1) as $row) {
            $name = $this->value($row, $columns, 'tool_name');
            if ($name === null || in_array(mb_strtoupper($name), ['APPROVED', 'CHECKED', 'ISSUED', 'TOTAL'], true)) {
                continue;
            }

            $date = SpreadsheetInput::date($this->value($row, $columns, 'ng_date'), "{$name} / ng_date");
            $department = $this->value($row, $columns, 'division');
            $category = $this->value($row, $columns, 'category_ng');
            if ($date === null || $department === null || $category === null) {
                throw new RuntimeException("{$name}: tanggal NG, division, dan kategori NG wajib diisi.");
            }
            if ($this->expectedYear !== null && (int) substr($date, 0, 4) !== $this->expectedYear) {
                throw new RuntimeException("{$name}: tanggal {$date} tidak termasuk tahun {$this->expectedYear}.");
            }

            ListUpNg::create([
                'tahun' => (int) substr($date, 0, 4),
                'tool_name' => mb_strtoupper($name),
                'type' => $this->value($row, $columns, 'type'),
                'size' => $this->value($row, $columns, 'size'),
                'number_seri' => $this->value($row, $columns, 'number_seri'),
                'number_reg' => $this->value($row, $columns, 'number_reg'),
                'part_name' => $this->value($row, $columns, 'part_name'),
                'part_number' => $this->value($row, $columns, 'part_number'),
                'ng_date' => $date,
                'division' => $department,
                'category_ng' => $category,
                'note' => $this->value($row, $columns, 'note'),
            ]);

            $this->importedCount++;
        }
    }

    /**
     * @return array<string, int>
     */
    private function columnMap(array $header, array $subheader): array
    {
        $aliases = [
            'tool_name' => ['tool_name', 'nama_alat', 'nama_alat_ukur'],
            'type' => ['type', 'tipe'],
            'size' => ['size', 'ukuran', 'specification', 'spesifikasi'],
            'number_seri' => ['number_seri', 'number_seri_', 'serial_number', 'nomor_seri'],
            'number_reg' => ['number_reg', 'number_reg_', 'registration_number', 'nomor_registrasi'],
            'part_name' => ['part_name', 'nama_part'],
            'part_number' => ['part_number', 'nomor_part'],
            'ng_date' => ['ng_date', 'tanggal_ng'],
            'division' => ['dept', 'division', 'department'],
            'category_ng' => ['category_ng', 'kategori_ng'],
            'note' => ['note', 'keterangan'],
        ];
        $columns = [];

        foreach ($header as $index => $label) {
            $parentKey = $this->normalizeHeader($label);
            $childKey = $this->normalizeHeader($subheader[$index] ?? null);

            $field = $this->fieldForHeader($parentKey, $aliases);

            if ($field !== null) {
                $columns[$field] = $index;
            }
        }

        return $columns;
    }

    private function fieldForHeader(string $header, array $aliases): ?string
    {
        foreach ($aliases as $field => $names) {
            if (in_array($header, $names, true)) {
                return $field;
            }
        }

        if ($header === 'stock') {
            return null;
        }

        return null;
    }

    private function isSplitSubheader(array $row): bool
    {
        $specialLabels = ['qa', 'stock_qa', 'prod', 'production', 'stock_prod', 'no_spp', 'spp', 'open_closed', 'status_order', 'note'];
        $matched = 0;

        foreach ($row as $value) {
            if (in_array($this->normalizeHeader($value), $specialLabels, true)) {
                $matched++;
            }
        }

        return $matched >= 3;
    }

    private function normalizeHeader(mixed $value): string
    {
        $value = mb_strtolower(trim((string) ($value ?? '')));

        return trim(preg_replace('/[^[:alnum:]]+/u', '_', $value) ?? '', '_');
    }

    private function value(array $row, array $columns, string $field): ?string
    {
        if (! isset($columns[$field])) {
            return null;
        }

        $value = trim((string) ($row[$columns[$field]] ?? ''));
        if ($value === '' || $value === '-') {
            return null;
        }

        return $value;
    }
}
