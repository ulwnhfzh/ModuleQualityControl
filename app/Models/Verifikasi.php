<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_name', 'tahun', 'tipe', 'tanggal',
        // Bulanan
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
        'total',
        // Harian
        'qa_1', 'qa_1_2', 'qa_inc', 'qa_insp',
        'ppic_1', 'ppic_2', 'line_scc',
        'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng'
    ];
}