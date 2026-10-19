<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifikasiInternalSchedule extends Model
{
    protected $fillable = [
        'tool_id', 'tahun', 'tool_name', 'tool_type', 'specification', 'serial_number', 'registration_number', 'shop',
        'period1_verification_date', 'period1_next_verification_date', 'period1_jgm',
        'period2_verification_date', 'period2_next_verification_date', 'period2_jgm',
    ];

    protected $casts = [
        'period1_verification_date' => 'date',
        'period1_next_verification_date' => 'date',
        'period2_verification_date' => 'date',
        'period2_next_verification_date' => 'date',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
}
