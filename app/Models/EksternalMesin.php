<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EksternalMesin extends Model
{
    use HasFactory;

    protected $table = 'eksternal_mesins';

    protected $fillable = [
        'tool_name',
        'machine_model',
        'code_number',
        'serial_number',
        'maker',
        'no_machine',
        'retension',
        'start_date',
        'next_date',
        'tahun',
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
        'total',
    ];
}