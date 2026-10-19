<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolStock extends Model
{
    protected $fillable = ['tool_id', 'tool_name', 'type', 'category', 'order_number', 'specification', 'opening_stock'];

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function movements()
    {
        return $this->hasMany(ToolStockMovement::class, 'stock_id');
    }
}
