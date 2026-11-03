<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_all_produksis', function (Blueprint $table) {
            $table->index(['division', 'id'], 'data_all_produksis_division_id_index');
            $table->index(['division', 'tool_name'], 'data_all_produksis_division_tool_index');
        });
    }

    public function down(): void
    {
        Schema::table('data_all_produksis', function (Blueprint $table) {
            $table->dropIndex('data_all_produksis_division_id_index');
            $table->dropIndex('data_all_produksis_division_tool_index');
        });
    }
};
