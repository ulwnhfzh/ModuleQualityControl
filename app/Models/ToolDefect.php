<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolDefect extends Model
{
    use HasFactory;

    public const MONTHS = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    public const DIVISIONS = [
        'ENGINEERING', 'L.SCC', 'PRODUKSI 1', 'PRODUKSI 2', 'PRODUKSI 3', 'PRODUKSI 5',
        'PRODUKSI 6', 'PRODUKSI 7', 'PRODUKSI 8', 'PPIC 1', 'PPIC 2', 'QA INCOMING',
        'QA INSPECTION', 'QA 1', 'QA 1.2', 'QA 2', 'NISSHO',
    ];

    public const COUNT_FIELDS = [
        'aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 'min_std', 'over_std',
        'patah', 'rusak', 'seret', 'lain_lain',
    ];

    protected $fillable = [
        'tahun', 'bulan', 'division',
        'aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 
        'min_std', 'over_std', 'patah', 'rusak', 'seret', 'lain_lain'
    ];
}