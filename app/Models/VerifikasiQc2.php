<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiQc2 extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_qc2';
    
    protected $fillable = [
        'tool_id', 'tool_name', 'tool_type', 'specification', 'serial_number',
        'registration_number', 'tahun', 'shop',
        'verification_date', 'next_verification_date', 'jgm', // Hanya 1 periode
    ];

    protected $casts = [
        'verification_date' => 'date',
        'next_verification_date' => 'date',
    ];
}