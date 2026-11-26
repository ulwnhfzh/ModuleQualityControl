<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListUpNgCategory extends Model
{
    protected $fillable = ['name', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function normalize(?string $name): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $name) ?? ''));
    }
}