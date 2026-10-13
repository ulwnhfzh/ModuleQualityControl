<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_name', 'category', 'tahun',
        'machine_model', 'code_number', 'serial_number', 'maker', 'no_machine', 'retension', 'start_date', 'next_date',
        'qa_1', 'qa_1_2', 'qa_2', 'qa_inc', 'qa_insp', 'ppic_1', 'ppic_2', 'line_scc',
        'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng', 'total'
    ];
}