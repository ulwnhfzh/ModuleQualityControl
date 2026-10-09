<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eksternal extends Model
{
    use HasFactory;

    protected $table = 'eksternals';

    protected $fillable = [
        'tool_name',
        'tahun',
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
        'qty_set',
    ];
}