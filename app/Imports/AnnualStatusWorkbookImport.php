<?php

namespace App\Imports;

use App\Models\ExternalCalibrationListUp;
use App\Models\InternalCalibrationListUp;
use App\Models\InternalVerificationSchedule;
use App\Models\ListUpNg;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class InvalidAnnualStatusDate extends RuntimeException
{
}

class AnnualStatusWorkbookImport
{
    private array $sheetIndexes = [];
    private Collection $ngRecords;
    private int $importedCount = 0;
    private int $skippedConflictCount = 0;
    private int $protectedOkCount = 0;
    private array $skippedInvalidRows = [];

    public function __construct(
        string $path,
        private readonly string $kind,
        private readonly int $year,
        private readonly string $company = 'HAMATETSU',
    ) {
        if (! in_array($kind, ['internal_calibration', 'external_calibration', 'verification'], true)) {
            throw new RuntimeException('Jenis workbook status tidak dikenal.');
        }

        $this->sheetIndexes = self::supportedSheets($path, $kind, $year);
        if ($this->sheetIndexes === []) {
            throw new RuntimeException('Header workbook bertingkat tidak cocok dengan fitur yang dipilih. Tidak ada data yang diimpor.');
        }

        $category = match ($kind) {
            'internal_calibration' => 'KALIBRASI',
            'external_calibration' => 'KALIBRASI EKSTENAL',
            default => 'VERIFIKASI',
        };
        $this->ngRecords = ListUpNg::query()
            ->where('tahun', $year)
            ->whereRaw('UPPER(TRIM(category_ng)) = ?', [$category])
            ->get();
    }

    public static function supports(string $path, string $kind, int $year): bool
    {
        return self::supportedSheets($path, $kind, $year) !== [];
    }

    public function import(string $path): void
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $maxColumn = $this->kind === 'verification' ? 13 : 11;

        try {
            DB::transaction(function () use ($spreadsheet, $maxColumn): void {
                foreach ($this->sheetIndexes as $index => $startRow) {
                    $sheet = $spreadsheet->getSheet($index);
                    for ($rowIndex = $startRow; $rowIndex <= $sheet->getHighestDataRow(); $rowIndex++) {
                        $row = [];
                        for ($column = 1; $column <= $maxColumn; $column++) {
                            $cell = $sheet->getCell([$column, $rowIndex]);
                            $value = $cell->getValue();
                            if ($cell->getDataType() === 'f') {
                                $value = $cell->getOldCalculatedValue();
                            }
                            $row[$column - 1] = $value;
                        }
                        $this->processRow($row, $sheet->getTitle(), $rowIndex);
                    }
                }
                if ($this->kind === 'verification' && $this->year === (int) now()->year) {
                    InternalVerificationSchedule::syncAllProductionRows();
                }
            });
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function processRow(array $data, string $sheetTitle, int $rowIndex): void
    {
        $name = trim((string) ($data[1] ?? ''));
        if (! is_numeric($data[0] ?? null)
            || $name === ''
            || in_array(mb_strtoupper($name), ['APPROVED', 'CHECKED', 'ISSUED', 'TOTAL', 'GRAND TOTAL'], true)) {
            return;
        }

        try {
            $record = $this->mapRow($data, $name);
        } catch (InvalidAnnualStatusDate $exception) {
            $this->skippedInvalidRows[] = [
                'sheet' => $sheetTitle,
                'row' => $rowIndex,
                'tool_name' => $name,
                'error' => $exception->getMessage(),
            ];

            return;
        }
        if ($this->conflictsWithListUpNg($record)) {
            $this->skippedConflictCount++;
            return;
        }

        $this->save($record);
        $this->importedCount++;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedConflictCount(): int
    {
        return $this->skippedConflictCount;
    }

    public function getSkippedInvalidRows(): array
    {
        return $this->skippedInvalidRows;
    }

    public function getProtectedOkCount(): int
    {
        return $this->protectedOkCount;
    }

    private function mapRow(array $row, string $name): array
    {
        if ($this->kind === 'internal_calibration') {
            return [
                'tool_name' => mb_strtoupper($name),
                'tool_type' => $this->text($row[2] ?? null) ?? '-',
                'specification' => $this->text($row[3] ?? null),
                'serial_number' => $this->text($row[4] ?? null),
                'registration_number' => $this->text($row[5] ?? null),
                'shop' => $this->text($row[6] ?? null),
                'calibration_date' => $this->date($row[7] ?? null, "{$name} / calibration_date"),
                'next_calibration_date' => $this->date($row[8] ?? null, "{$name} / next_calibration_date"),
                'jgm' => $this->status($row[9] ?? null, $name),
            ];
        }

        if ($this->kind === 'external_calibration') {
            return [
                'tool_name' => mb_strtoupper($name),
                'specification' => $this->text($row[2] ?? null),
                'serial_number' => $this->text($row[3] ?? null),
                'registration_number' => $this->text($row[4] ?? null),
                'shop' => $this->text($row[5] ?? null),
                'calibration_date' => $this->date($row[6] ?? null, "{$name} / calibration_date"),
                'next_calibration_date' => $this->date($row[7] ?? null, "{$name} / next_calibration_date"),
                'jgm' => $this->status($row[8] ?? null, $name),
                'note' => $this->text($row[10] ?? null),
            ];
        }

        return [
            'tool_name' => mb_strtoupper($name),
            'tool_type' => $this->text($row[2] ?? null),
            'specification' => $this->text($row[3] ?? null),
            'serial_number' => $this->text($row[4] ?? null),
            'registration_number' => $this->text($row[5] ?? null),
            'shop' => $this->text($row[6] ?? null),
            'period1_verification_date' => $this->date($row[7] ?? null, "{$name} / period1_verification_date"),
            'period1_next_verification_date' => $this->date($row[8] ?? null, "{$name} / period1_next_verification_date"),
            'period1_jgm' => $this->status($row[9] ?? null, $name),
            'period2_verification_date' => $this->date($row[10] ?? null, "{$name} / period2_verification_date"),
            'period2_next_verification_date' => $this->date($row[11] ?? null, "{$name} / period2_next_verification_date"),
            'period2_jgm' => $this->status($row[12] ?? null, $name),
        ];
    }

    private function save(array $record): void
    {
        if ($this->kind === 'internal_calibration') {
            $identity = [
                'tahun' => $this->year,
                'company' => $this->company,
                'tool_name' => $record['tool_name'],
                'tool_type' => $record['tool_type'],
                'specification' => $record['specification'],
                'serial_number' => $record['serial_number'],
                'registration_number' => $record['registration_number'],
                'shop' => $record['shop'],
            ];
            if ($this->hasNoEquipmentNumber($record)) {
                $identity['calibration_date'] = $record['calibration_date'];
            }
            $values = $this->withoutNulls($record);
            $this->preserveExistingOk(
                InternalCalibrationListUp::class,
                $identity,
                $record,
                $values,
                [['date' => 'calibration_date', 'status' => 'jgm']],
            );
            InternalCalibrationListUp::updateOrCreate($identity, $values);

            return;
        }

        if ($this->kind === 'external_calibration') {
            $identity = [
                'tahun' => $this->year,
                'tool_name' => $record['tool_name'],
                'specification' => $record['specification'],
                'serial_number' => $record['serial_number'],
                'registration_number' => $record['registration_number'],
                'shop' => $record['shop'],
            ];
            if ($this->hasNoEquipmentNumber($record)) {
                $identity['calibration_date'] = $record['calibration_date'];
            }
            $values = $this->withoutNulls($record);
            $this->preserveExistingOk(
                ExternalCalibrationListUp::class,
                $identity,
                $record,
                $values,
                [['date' => 'calibration_date', 'status' => 'jgm']],
            );
            ExternalCalibrationListUp::updateOrCreate($identity, $values);

            return;
        }

        $identity = [
            'tahun' => $this->year,
            'company' => $this->company,
            'tool_name' => $record['tool_name'],
            'tool_type' => $record['tool_type'],
            'specification' => $record['specification'],
            'serial_number' => $record['serial_number'],
            'registration_number' => $record['registration_number'],
            'shop' => $record['shop'],
        ];
        if ($this->hasNoEquipmentNumber($record)) {
            $identity['period1_verification_date'] = $record['period1_verification_date'];
            $identity['period2_verification_date'] = $record['period2_verification_date'];
        }
        $values = $this->withoutNulls($record);
        $this->preserveExistingOk(
            InternalVerificationSchedule::class,
            $identity,
            $record,
            $values,
            [
                ['date' => 'period1_verification_date', 'status' => 'period1_jgm'],
                ['date' => 'period2_verification_date', 'status' => 'period2_jgm'],
            ],
        );
        InternalVerificationSchedule::withoutEvents(
            fn () => InternalVerificationSchedule::updateOrCreate($identity, $values),
        );
    }

    private function preserveExistingOk(string $modelClass, array $identity, array $record, array &$values, array $periods): void
    {
        $existing = $modelClass::query()->where($identity)->first();
        if (! $existing) {
            return;
        }

        foreach ($periods as $period) {
            if (strtoupper(trim((string) $existing->getAttribute($period['status']))) !== 'OK'
                || strtoupper(trim((string) ($record[$period['status']] ?? ''))) !== 'NG') {
                continue;
            }

            $existingDate = $existing->getAttribute($period['date']);
            $incomingDate = $record[$period['date']] ?? null;
            if ($existingDate && $incomingDate
                && substr((string) $existingDate, 0, 7) !== substr((string) $incomingDate, 0, 7)) {
                continue;
            }

            unset($values[$period['status']]);
            $this->protectedOkCount++;
        }
    }

    private function hasNoEquipmentNumber(array $record): bool
    {
        return $this->normalize($record['serial_number'] ?? null) === ''
            && $this->normalize($record['registration_number'] ?? null) === '';
    }

    private function conflictsWithListUpNg(array $record): bool
    {
        $periods = match ($this->kind) {
            'internal_calibration' => [['date' => 'calibration_date', 'status' => 'jgm']],
            'external_calibration' => [['date' => 'calibration_date', 'status' => 'jgm']],
            default => [
                ['date' => 'period1_verification_date', 'status' => 'period1_jgm'],
                ['date' => 'period2_verification_date', 'status' => 'period2_jgm'],
            ],
        };

        foreach ($this->ngRecords as $ng) {
            if (! $this->matchesIdentity($ng->getAttributes(), $record)) {
                continue;
            }

            foreach ($periods as $period) {
                $status = strtoupper(trim((string) ($record[$period['status']] ?? '')));
                $recordDate = $record[$period['date']] ?? null;
                if ($status !== 'OK' || $recordDate === null || $ng->ng_date === null) {
                    continue;
                }
                if (substr((string) $recordDate, 0, 7) === $ng->ng_date->format('Y-m')) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchesIdentity(array $ng, array $record): bool
    {
        if ($this->normalize($ng['tool_name'] ?? null) !== $this->normalize($record['tool_name'] ?? null)
            || $this->canonicalDivision($ng['dept'] ?? null) !== $this->canonicalDivision($record['shop'] ?? null)
            || $this->normalize($ng['size'] ?? null) !== $this->normalize($record['specification'] ?? null)) {
            return false;
        }

        if ($this->kind !== 'external_calibration'
            && $this->normalize($ng['type'] ?? null) !== $this->normalize($record['tool_type'] ?? null)) {
            return false;
        }

        $sourceReg = $this->normalize($ng['number_reg'] ?? null);
        $targetReg = $this->normalize($record['registration_number'] ?? null);
        if ($sourceReg !== '' && $targetReg !== '') {
            return $sourceReg === $targetReg;
        }

        $sourceSerial = $this->normalize($ng['number_seri'] ?? null);
        $targetSerial = $this->normalize($record['serial_number'] ?? null);
        if ($sourceSerial !== '' && $targetSerial !== '') {
            return $sourceSerial === $targetSerial;
        }

        return $sourceReg === ''
            && $targetReg === ''
            && $sourceSerial === ''
            && $targetSerial === '';
    }

    private function withoutNulls(array $record): array
    {
        $values = $record;
        foreach ($values as $field => $value) {
            if ($value === null) {
                unset($values[$field]);
            }
        }

        return $values;
    }

    private function status(mixed $value, string $name): ?string
    {
        $status = strtoupper(trim((string) ($value ?? '')));
        if ($status === '') {
            return null;
        }
        if (! in_array($status, ['OK', 'NG'], true)) {
            throw new RuntimeException("Status JGM tidak valid pada {$name}: {$status}.");
        }

        return $status;
    }

    private static function supportedSheets(string $path, string $kind, int $year): array
    {
        if (! in_array($kind, ['internal_calibration', 'external_calibration', 'verification'], true)) {
            return [];
        }
        if (! is_file($path)) {
            throw new RuntimeException('File workbook tidak dapat dibaca.');
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $selected = [];

        try {
            foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
                if (self::hasExpectedHeaders($sheet, $kind, $year)) {
                    $selected[$index] = $kind === 'verification' ? 6 : 5;
                }
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        return $selected;
    }

    private static function hasExpectedHeaders($sheet, string $kind, int $year): bool
    {
        $read = fn (string $cell): string => self::header($sheet->getCell($cell)->getValue());
        if ($read('B3') !== 'toolname') {
            return false;
        }

        if ($kind === 'internal_calibration') {
            return $read('C3') === 'type'
                && $read('D3') === 'specification'
                && $read('F3') === 'numberregistration'
                && $read('G3') === 'shop'
                && $read('H3') === self::header("YEAR {$year}")
                && $read('H4') === 'calibrationdate'
                && $read('I4') === 'nextcalibrationdate'
                && $read('J4') === 'jgm';
        }

        if ($kind === 'external_calibration') {
            return $read('C3') === 'specification'
                && $read('E3') === 'numberregistration'
                && $read('F3') === 'shop'
                && $read('G3') === self::header("YEAR {$year}")
                && $read('G4') === 'calibrationdate'
                && $read('H4') === 'nextcalibrationdate'
                && $read('I4') === 'jgm';
        }

        return $read('C3') === 'type'
            && $read('D3') === 'specification'
            && $read('G3') === 'shop'
            && $read('H3') === self::header("YEAR {$year}")
            && $read('H5') === 'verificationdate'
            && $read('J5') === 'jgm'
            && $read('K5') === 'verificationdate'
            && $read('M5') === 'jgm';
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' || $value === '-' ? null : $value;
    }

    private function date(mixed $value, string $context): ?string
    {
        try {
            return SpreadsheetInput::date($this->text($value), $context);
        } catch (RuntimeException $exception) {
            throw new InvalidAnnualStatusDate($exception->getMessage(), 0, $exception);
        }
    }

    private function normalize(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }

    private function canonicalDivision(mixed $value): ?string
    {
        $value = $this->normalize($value);
        if ($value === '') {
            return null;
        }

        return match ($value) {
            'QA1' => 'QA 1',
            'QA2' => 'QA 2',
            'LSCC', 'L.SCC' => 'LINE SCC',
            'QA INCOMING' => 'QA INC',
            'QA INSPECTION' => 'QA INSP',
            default => $value,
        };
    }

    private static function header(mixed $value): string
    {
        return preg_replace('/[^[:alnum:]]/u', '', mb_strtolower(trim((string) $value))) ?? '';
    }
}
