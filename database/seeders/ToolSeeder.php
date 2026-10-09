<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    \App\Models\Tool::create(['tool_name' => 'BALL ANVIL', 'category' => 'Small Tool']);
    \App\Models\Tool::create(['tool_name' => 'BASE (DIAL STAND)', 'category' => 'Small Tool']);
    \App\Models\Tool::create(['tool_name' => 'CMM', 'category' => 'Machine', 'machine_model' => 'Crysta Plus M574', 'maker' => 'Mitutoyo']);
    }
}
