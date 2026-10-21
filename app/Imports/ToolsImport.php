<?php

namespace App\Imports;

use App\Models\Tool;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ToolsImport implements ToCollection
{
    protected string $tahun;
    protected string $defaultCategory;
    protected int $importedCount = 0;

    public function __construct(string $tahun, string $defaultCategory = 'Small Tool')
    {
        $this->tahun = $tahun;
        $this->defaultCategory = $defaultCategory;
    }

    public function collection(Collection $rows): void
    {
        $fields = [
            'qa_1', 'qa_1_2', 'qa_2', 'qa_inc', 'qa_insp', 'ppic_1', 'ppic_2',
            'line_scc', 'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng',
        ];
        $headers = null;

        foreach ($rows as $row) {
            $values = $row->toArray();
            $candidateHeaders = [];
            foreach ($values as $column => $value) {
                $candidateHeaders[$column] = $this->normalizeHeader($value);
            }

            $nameColumn = $this->findNameColumn($candidateHeaders);
            if ($nameColumn !== null) {
                $headers = $candidateHeaders;
                continue;
            }

            if ($headers === null) {
                continue;
            }

            $nameColumn = $this->findNameColumn($headers);
            $toolName = trim((string) ($values[$nameColumn] ?? ''));
            if ($toolName === '' || in_array(mb_strtoupper($toolName), ['TOTAL', 'GRAND TOTAL', 'GRAND TOTAL MEASUREMENT', 'APPROVED', 'CHECKED', 'ISSUED'], true)) {
                continue;
            }

            $rowByHeader = [];
            foreach ($headers as $column => $header) {
                if ($header !== '') {
                    $rowByHeader[$header] = $values[$column] ?? null;
                }
            }

            $data = [
                'tool_name' => mb_strtoupper($toolName),
                'category' => trim((string) ($rowByHeader['category'] ?? $rowByHeader['kategori'] ?? $this->defaultCategory)) ?: $this->defaultCategory,
                'tahun' => $this->tahun,
            ];
            foreach ($fields as $field) {
                $data[$field] = SpreadsheetInput::integer($rowByHeader[$field] ?? null, "{$toolName} / {$field}");
            }
            $data['total'] = array_sum(array_map(fn ($field) => $data[$field], $fields));

            $tool = Tool::firstOrNew([
                'tool_name' => $data['tool_name'],
                'category' => $data['category'],
                'tahun' => $data['tahun'],
            ]);
            $tool->fill($data)->save();
            $this->importedCount++;
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    private function normalizeHeader(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = str_replace(['.', '/'], ['_', '_'], $value);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        return trim($value, '_');
    }

    private function findNameColumn(array $headers): int|string|null
    {
        foreach ($headers as $column => $header) {
            if (in_array($header, ['tool_name', 'nama_alat', 'nama_alat_ukur', 'alat_ukur', 'tool'], true)) {
                return $column;
            }
        }
        return null;
    }
}
