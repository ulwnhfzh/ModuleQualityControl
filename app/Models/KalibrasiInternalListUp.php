<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalibrasiInternalListUp extends Model
{
    protected $fillable = [
        'tahun', 'tool_name', 'tool_type', 'specification', 'serial_number',
        'registration_number', 'shop', 'calibration_date', 'next_calibration_date', 'jgm',
    ];

    protected $casts = [
        'calibration_date' => 'date',
        'next_calibration_date' => 'date',
    ];
}
