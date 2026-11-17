<?php

namespace App\Imports;

class SpreadsheetImportHeaders
{
    public const TOOL_NAME = ['tool_name', 'nama_alat', 'nama_alat_ukur', 'alat_ukur', 'tool'];

    public const TYPE = ['type', 'tool_type', 'tipe'];

    public const MONTHS = [
        ['jan', 'january', 'januari'],
        ['feb', 'february', 'februari'],
        ['mar', 'march', 'maret'],
        ['apr', 'april'],
        ['may', 'mei'],
        ['jun', 'june', 'juni'],
        ['jul', 'july', 'juli'],
        ['aug', 'agu', 'august', 'agustus', 'ags'],
        ['sep', 'september'],
        ['oct', 'okt', 'october', 'oktober'],
        ['nov', 'november'],
        ['dec', 'des', 'december', 'desember'],
    ];

    public const INTERNAL_MONTHLY = [
        self::TOOL_NAME,
        ...self::MONTHS,
        ['qty'],
    ];

    public const EXTERNAL_MONTHLY = [
        self::TOOL_NAME,
        ...self::MONTHS,
        ['qty_set'],
    ];

    public const VERIFICATION_MONTHLY = [
        self::TOOL_NAME,
        ...self::MONTHS,
        ['tipe'],
    ];
}
