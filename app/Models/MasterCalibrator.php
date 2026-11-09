<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCalibrator extends Model
{
    protected $fillable = [
        'tahun', 'tool_name', 'type', 'size', 'number_seri', 'number_reg',
        'part_name', 'part_number', 'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'note',
    ];
}
