<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QualityControlWorkbookExport implements WithMultipleSheets
{
    public function __construct(private readonly int $tahun)
    {
    }

    public function sheets(): array
    {
        $year = (string) $this->tahun;

        return [
            new ToolsExport($year, 'Small Tool'),
            new ToolsExport($year, 'Machine'),
            new ToolsExport($year, 'Jig'),
            new VerifikasiExport($year, 'bulanan'),
            new VerifikasiExport($year, 'harian'),
            new InternalExport($year),
            new EksternalExport($year),
            new MesinExport($year),
            new ToolDefectsExport($this->tahun),
            new StockReportExport($this->tahun, 'Small Tool'),
            new StockReportExport($this->tahun, 'Machine'),
            new StockReportExport($this->tahun, 'Jig'),
            new VerifikasiPeriodeExport($this->tahun),
            new KalibrasiInternalListUpExport($this->tahun),
            new VerifikasiQc2Export($this->tahun),
            new DataAllProduksiExport('ALL'),
        ];
    }
}
