<?php

namespace App\Support;

class DocumentPdfLayout
{
    public static function addPageNumbers($pdf): void
    {
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $dompdf->getCanvas()->page_text(700, 20, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, 8, [0.25, 0.25, 0.25]);
    }
}
