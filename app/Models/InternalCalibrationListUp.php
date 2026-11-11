<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalCalibrationListUp extends Model
{
    protected $table = 'kalibrasi_internal_list_ups';

    protected $fillable = [
        'tahun', 'company', 'tool_name', 'tool_type', 'specification', 'serial_number',
        'registration_number', 'shop', 'calibration_date', 'next_calibration_date', 'jgm',
        'period2_calibration_date', 'period2_next_calibration_date', 'period2_jgm',
    ];

    protected $casts = [
        'calibration_date' => 'date',
        'next_calibration_date' => 'date',
        'period2_calibration_date' => 'date',
        'period2_next_calibration_date' => 'date',
    ];
}
