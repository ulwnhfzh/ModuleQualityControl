<?php

namespace App\Imports;

use App\Models\InternalVerificationSchedule;
use App\Services\ListUpNgProductionSynchronizer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class ProductionDataFolderImport
{
    /**
     * @return array{
     *     files_seen: int,
     *     files_imported: int,
     *     records_processed: int,
     *     categories: array<string, array{files: int, records: int}>,
     *     skipped: array<int, string>
     * }
     */
    public function import(string $directory): array
    {
        $root = realpath($directory);
        if ($root === false || ! is_dir($root)) {
            throw new RuntimeException("Folder tidak dapat dibaca: {$directory}");
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (! in_array(strtolower($file->getExtension()), ['xls', 'xlsx', 'csv'], true)) {
                continue;
            }

            if (str_starts_with($file->getFilename(), '~$') || strtolower($file->getFilename()) === 'thumbs.db') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files, SORT_NATURAL | SORT_FLAG_CASE);
        if ($files === []) {
            throw new RuntimeException("Tidak ditemukan workbook Excel/CSV di folder: {$directory}");
        }

        $summary = [
            'files_seen' => count($files),
            'files_imported' => 0,
            'records_processed' => 0,
            'categories' => [],
            'skipped' => [],
        ];

        DB::transaction(function () use ($files, $root, &$summary): void {
            foreach ($files as $path) {
                $relativeDirectory = trim(substr(dirname($path), strlen($root)), DIRECTORY_SEPARATOR);
                $category = $relativeDirectory === '' ? basename($root) : basename(dirname($path));
                $relativePath = ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);

                $year = $this->yearFromPath($root) ?? (int) date('Y');
                $import = new ProductionDataImport($path, $category, $year);
                if ($import->getSelectedSheetCount() === 0) {
                    $summary['skipped'][] = "{$relativePath} (tidak ada sheet division yang dikenali)";
                    continue;
                }

                Excel::import($import, $path);
                $records = $import->getImportedCount();
                if ($records === 0) {
                    $summary['skipped'][] = "{$relativePath} (tidak ada baris alat yang dapat dipetakan)";
                    continue;
                }

                $summary['files_imported']++;
                $summary['records_processed'] += $records;
                $summary['categories'][$category] ??= ['files' => 0, 'records' => 0];
                $summary['categories'][$category]['files']++;
                $summary['categories'][$category]['records'] += $records;
            }

            if ($summary['records_processed'] > 0) {
                InternalVerificationSchedule::syncAllProductionRows();
                if (preg_match('/(?<!\d)20\d{2}(?!\d)/', basename($root), $yearMatch) === 1) {
                    app(ListUpNgProductionSynchronizer::class)->syncYear((int) $yearMatch[0]);
                }
            }
        });

        return $summary;
    }

    private function yearFromPath(string $path): ?int
    {
        return preg_match('/(?<!\d)(20\d{2})(?!\d)/', $path, $matches) === 1
            ? (int) $matches[1]
            : null;
    }
}
