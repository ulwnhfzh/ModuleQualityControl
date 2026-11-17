<?php

namespace App\Imports;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class SpreadsheetSheetSelector
{
    /**
     * @param  array<int, string|array<int, string>>  $requiredHeaderGroups
     * @param  callable(int, string): object  $importFactory  Receives the header row and sheet title.
     */
    public static function forHeaders(
        UploadedFile|string $file,
        array $requiredHeaderGroups,
        callable $importFactory,
        int $maxHeaderRow = 30,
    ): SelectedSheetsImport {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        if ($path === false || ! is_file($path)) {
            throw new RuntimeException('File Excel tidak dapat dibaca.');
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $selected = [];

        try {
            foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
                $lastRow = min($sheet->getHighestDataRow(), $maxHeaderRow);
                $lastColumn = $sheet->getHighestDataColumn();

                for ($rowIndex = 1; $rowIndex <= $lastRow; $rowIndex++) {
                    $cells = $sheet->rangeToArray("A{$rowIndex}:{$lastColumn}{$rowIndex}", null, true, false)[0] ?? [];
                    $headers = array_fill_keys(array_filter(array_map(self::normalizeHeader(...), $cells)), true);

                    if (self::matchesGroups($headers, $requiredHeaderGroups)) {
                        $selected[$sheetIndex] = $importFactory($rowIndex, $sheet->getTitle());
                        break;
                    }
                }
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        if ($selected === []) {
            throw new RuntimeException('Tidak ditemukan sheet dengan header yang sesuai untuk fitur ini. Tidak ada data yang diimpor.');
        }

        return new SelectedSheetsImport($selected);
    }

    private static function matchesGroups(array $headers, array $groups): bool
    {
        foreach ($groups as $group) {
            $aliases = is_array($group) ? $group : [$group];
            $matched = false;

            foreach ($aliases as $alias) {
                if (isset($headers[self::normalizeHeader($alias)])) {
                    $matched = true;
                    break;
                }
            }

            if (! $matched) {
                return false;
            }
        }

        return true;
    }

    private static function normalizeHeader(mixed $value): string
    {
        return preg_replace('/[^[:alnum:]]/u', '', mb_strtolower(trim((string) $value))) ?? '';
    }
}
