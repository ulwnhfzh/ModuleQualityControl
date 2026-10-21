<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Shared visible document header and approval block for spreadsheet exports. */
class DocumentExportLayout
{
    public static function apply(
        Worksheet $sheet,
        string $lastColumn,
        string $title,
        ?string $year = null,
        ?string $category = null
    ): void
    {
        // Add a small page margin on top/left, then move the original table down.
        $sheet->insertNewColumnBefore('A', 1);
        $sheet->insertNewRowBefore(1, 1);
        $sheet->insertNewRowBefore(2, 6);
        $tableStartColumnIndex = 2; // Column A remains a clean left margin.
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn) + 1;
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
        $metaStartIndex = max(6, $lastColumnIndex - 4);
        $metaStart = Coordinate::stringFromColumnIndex($metaStartIndex);
        $metaLabelEnd = Coordinate::stringFromColumnIndex($metaStartIndex + 1);
        $metaValueStart = Coordinate::stringFromColumnIndex($metaStartIndex + 2);

        $sheet->getColumnDimension('A')->setWidth(3);
        $sheet->getRowDimension(1)->setRowHeight(8);
        $sheet->mergeCells('B2:C5');
        $sheet->getStyle('B2:C5')->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['outline' => ['borderStyle' => 'thin', 'color' => ['rgb' => '333333']]],
        ]);
        $titleEnd = Coordinate::stringFromColumnIndex($metaStartIndex - 1);
        $sheet->mergeCells("D2:{$titleEnd}5");
        $sheet->setCellValue("D2", strtoupper($title));
        $sheet->getStyle("D2:{$titleEnd}5")->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => '172033']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['outline' => ['borderStyle' => 'thin', 'color' => ['rgb' => '333333']]],
        ]);

        foreach ([
            ['Halaman', 'Otomatis saat dicetak'],
            ['Nomor Dokumen', 'F-QC-01'],
            ['Tanggal Terbit', '10 Juli 2023'],
            ['Revisi', '02'],
        ] as $offset => [$label, $value]) {
            $row = $offset + 2;
            $sheet->mergeCells("{$metaStart}{$row}:{$metaLabelEnd}{$row}");
            $sheet->mergeCells("{$metaValueStart}{$row}:{$lastColumn}{$row}");
            $sheet->setCellValue("{$metaStart}{$row}", $label);
            $sheet->setCellValue("{$metaValueStart}{$row}", $value);
            $sheet->getStyle("{$metaStart}{$row}:{$lastColumn}{$row}")->applyFromArray([
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '777777']]],
                'alignment' => ['vertical' => 'center', 'wrapText' => true],
            ]);
            $sheet->getStyle("{$metaStart}{$row}")->getFont()->setBold(true);
        }

        for ($row = 2; $row <= 5; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }
        $sheet->getRowDimension(6)->setRowHeight(8);
        $sheet->getRowDimension(7)->setRowHeight(20);
        if ($year !== null) {
            $sheet->setCellValue("B7", 'PERIODE: YEAR ' . $year);
            $sheet->getStyle('B7')->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '172033']],
                'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
            ]);
        }
        if ($category !== null) {
            $sheet->mergeCells("H7:{$lastColumn}7");
            $sheet->setCellValue("H7", 'KATEGORI: ' . strtoupper($category));
            $sheet->getStyle("H7:{$lastColumn}7")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '172033']],
                'alignment' => ['horizontal' => 'right', 'vertical' => 'center'],
            ]);
        }

        $logoPath = public_path('images/hamatetsu_indonesia_logo.jpg');
        if (is_file($logoPath)) {
            $logo = new Drawing();
            $logo->setName('Hamatetsu Indonesia');
            $logo->setDescription('Logo PT. Hamatetsu Indonesia');
            $logo->setPath($logoPath);
            $logo->setCoordinates('B2');
            $logo->setOffsetX(8);
            $logo->setOffsetY(8);
            $logo->setHeight(55);
            $logo->setWorksheet($sheet);
        }

        $tableHeaderRow = 8;
        $lastRow = $sheet->getHighestRow();
        $tableStartColumn = Coordinate::stringFromColumnIndex($tableStartColumnIndex);
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getFont()->setName('Arial');
        $sheet->getStyle("{$tableStartColumn}{$tableHeaderRow}:{$lastColumn}{$tableHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '111827']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'A0A8B4']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '000000']]],
        ]);
        if ($lastRow > $tableHeaderRow) {
            $sheet->getStyle("{$tableStartColumn}{$tableHeaderRow}:{$lastColumn}{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '000000']]],
            ]);
        }
        if (strtoupper((string) $sheet->getCell("{$tableStartColumn}{$lastRow}")->getValue()) === 'TOTAL') {
            $sheet->getStyle("{$tableStartColumn}{$lastRow}:{$lastColumn}{$lastRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '111827']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'A0A8B4']],
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '000000']]],
            ]);
        }
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $tableHeaderRow);

        $signatureRow = $lastRow + 2;
        // Match the paper form: keep the approval area under the right side.
        $tableColumnCount = $lastColumnIndex - $tableStartColumnIndex + 1;
        $signatureStart = min($lastColumnIndex - 5, $tableStartColumnIndex + (int) floor(($tableColumnCount - 1) * 0.50));
        $signatureColumns = $lastColumnIndex - $signatureStart + 1;
        $firstWidth = (int) round($signatureColumns / 3);
        $secondWidth = (int) round(($signatureColumns * 2) / 3) - $firstWidth;
        $ranges = [
            [$signatureStart, $signatureStart + $firstWidth - 1],
            [$signatureStart + $firstWidth, $signatureStart + $firstWidth + $secondWidth - 1],
            [$signatureStart + $firstWidth + $secondWidth, $lastColumnIndex],
        ];
        foreach (['APPROVED', 'CHECKED', 'ISSUED'] as $index => $label) {
            [$start, $end] = $ranges[$index];
            $startColumn = Coordinate::stringFromColumnIndex($start);
            $endColumn = Coordinate::stringFromColumnIndex(max($start, $end));
            $sheet->mergeCells("{$startColumn}{$signatureRow}:{$endColumn}{$signatureRow}");
            $sheet->mergeCells("{$startColumn}" . ($signatureRow + 1) . ":{$endColumn}" . ($signatureRow + 4));
            $sheet->setCellValue("{$startColumn}{$signatureRow}", $label);
            $sheet->getStyle("{$startColumn}{$signatureRow}:{$endColumn}" . ($signatureRow + 4))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '333333']]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
            $sheet->getStyle("{$startColumn}{$signatureRow}")->getFont()->setBold(true);
        }
        $sheet->getRowDimension($signatureRow)->setRowHeight(20);
        for ($row = $signatureRow + 1; $row <= $signatureRow + 4; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(16);
        }
        $sheet->getStyle('A1:' . $lastColumn . ($signatureRow + 4))->getFont()->setName('Arial');
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setFitToPage(true)
            ->setPrintArea('A1:' . $lastColumn . ($signatureRow + 4));
        $sheet->getPageMargins()->setTop(0.35)->setBottom(0.45)->setLeft(0.25)->setRight(0.25);
        $sheet->getHeaderFooter()->setOddHeader('');
        $sheet->getHeaderFooter()->setOddFooter('&LQC Measurement Tools&RHalaman &P dari &N');
    }
}
