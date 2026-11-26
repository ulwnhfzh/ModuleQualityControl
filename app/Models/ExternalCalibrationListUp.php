<?php

namespace App\Models;

use App\Casts\ValidDate;
use Illuminate\Database\Eloquent\Model;

class ExternalCalibrationListUp extends Model
{
    protected $fillable = [
        'tahun', 'tool_name', 'specification', 'serial_number', 'registration_number', 'shop',
        'calibration_date', 'next_calibration_date', 'jgm', 'calibration_count', 'note',
    ];

    protected $casts = [
        'calibration_date' => ValidDate::class,
        'next_calibration_date' => ValidDate::class,
    ];
}