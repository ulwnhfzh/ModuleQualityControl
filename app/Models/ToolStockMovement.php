<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolStockMovement extends Model
{
    protected $fillable = [
        'stock_id', 'movement_date', 'direction', 'quantity', 'note', 'user_id',
    ];

    protected function casts(): array
    {
        return ['movement_date' => 'date'];
    }

    public function stockInfo()
    {
        return $this->belongsTo(ToolStock::class, 'stock_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
