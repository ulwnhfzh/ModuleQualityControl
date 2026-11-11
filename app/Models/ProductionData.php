<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionData extends Model
{
    use HasFactory;

    protected $table = 'data_all_produksis';

    protected $fillable = [
        'division', 'tool_name', 'type', 'size', 'number_seri', 'number_reg', 
        'part_name', 'part_number', 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'note'
    ];

    public function resultStatus(string $month): ?string
    {
        $value = strtoupper(trim((string) $this->getAttribute($month)));
        if ($value === '') {
            return null;
        }

        if (preg_match('/(^|[^A-Z])NG([^A-Z]|$)/', $value)) {
            return 'NG';
        }
        if (preg_match('/(^|[^A-Z])OK([^A-Z]|$)/', $value)) {
            return 'OK';
        }

        return null;
    }
}
