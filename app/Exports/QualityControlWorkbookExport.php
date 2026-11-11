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
            new VerificationExport($year, 'bulanan'),
            new VerificationExport($year, 'harian'),
            new InternalExport($year),
            new ExternalExport($year),
            new MachineExport($year),
            new ToolDefectsExport($this->tahun),
            new StockReportExport($this->tahun, 'Small Tool'),
            new StockReportExport($this->tahun, 'Machine'),
            new StockReportExport($this->tahun, 'Jig'),
            new VerificationPeriodExport($this->tahun),
            new InternalCalibrationListUpExport($this->tahun),
            new Qc2VerificationExport($this->tahun),
            new ProductionDataExport('ALL'),
        ];
    }
}
