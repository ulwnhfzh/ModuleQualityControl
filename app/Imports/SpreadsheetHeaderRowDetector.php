<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetHeaderRowDetector
{
    /** Locate the real column-heading row even when a document header comes first. */
    public static function detect($uploadedFile): int
    {
        $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
        try {
            $sheet = $spreadsheet->getActiveSheet();
            $lastRow = $sheet->getHighestDataRow();
            $lastColumn = $sheet->getHighestDataColumn();

            for ($row = 1; $row <= $lastRow; $row++) {
                $values = $sheet->rangeToArray("A{$row}:{$lastColumn}{$row}", null, true, false)[0] ?? [];
                foreach ($values as $value) {
                    $header = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
                    if (in_array($header, ['toolname', 'namaalat', 'namalatalatukur'], true)) {
                        return $row;
                    }
                }
            }

            return 1;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }
}
