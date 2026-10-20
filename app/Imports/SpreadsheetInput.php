<?php

namespace App\Imports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;
use DateTimeImmutable;

class SpreadsheetInput
{
    public const MONTH_FIELDS = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

    public static function integer(mixed $value, string $context): int
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return 0;
        }

        if (!is_numeric($value)) {
            throw new RuntimeException("{$context} harus berupa angka bulat nol atau lebih.");
        }

        $number = (float) $value;
        if (!is_finite($number) || $number < 0 || $number > 2147483647 || floor($number) !== $number) {
            throw new RuntimeException("{$context} harus berupa angka bulat nol atau lebih.");
        }

        return (int) $number;
    }

    public static function date(mixed $value, string $context): ?string
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $date = ExcelDate::excelToDateTimeObject((float) $value);
            } else {
                $date = self::parseDateString(trim((string) $value));
            }
        } catch (\Throwable) {
            throw new RuntimeException("{$context} harus berisi tanggal yang valid.");
        }

        return $date->format('Y-m-d');
    }

    private static function parseDateString(string $value): DateTimeImmutable
    {
        foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y', '!m/d/Y', '!Y/m/d', '!Y-m-d H:i:s', '!d/m/Y H:i:s'] as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            $errors = DateTimeImmutable::getLastErrors();
            if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date;
            }
        }

        throw new RuntimeException('Invalid date value.');
    }
}