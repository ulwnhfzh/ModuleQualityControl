<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kalibrasi_internal_list_ups', function (Blueprint $table) {
            // Tambahkan kolom periode 2 setelah kolom 'jgm'
            $table->date('period2_calibration_date')->nullable()->after('jgm');
            $table->date('period2_next_calibration_date')->nullable()->after('period2_calibration_date');
            $table->string('period2_jgm', 30)->nullable()->after('period2_next_calibration_date');
        });
    }

    public function down(): void
    {
        Schema::table('kalibrasi_internal_list_ups', function (Blueprint $table) {
            $table->dropColumn(['period2_calibration_date', 'period2_next_calibration_date', 'period2_jgm']);
        });
    }
};