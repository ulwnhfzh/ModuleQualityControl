<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_all_produksis', function (Blueprint $table) {
            $table->string('category')->nullable()->after('division');
            $table->index(['category', 'division'], 'data_all_produksis_category_division_index');
        });
    }

    public function down(): void
    {
        Schema::table('data_all_produksis', function (Blueprint $table) {
            $table->dropIndex('data_all_produksis_category_division_index');
            $table->dropColumn('category');
        });
    }
};
