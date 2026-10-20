<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tool_defects', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->string('bulan'); // Contoh: 'JAN', 'FEB', 'DESEMBER'
            $table->string('division'); // Contoh: 'ENGINEERING', 'P6'
            
            // Kolom kerusakan (NG)
            $table->integer('aus')->default(0);
            $table->integer('erorr')->default(0);
            $table->integer('gompal')->default(0);
            $table->integer('hilang')->default(0);
            $table->integer('karat')->default(0);
            $table->integer('macet')->default(0);
            $table->integer('min_std')->default(0);
            $table->integer('over_std')->default(0);
            $table->integer('patah')->default(0);
            $table->integer('rusak')->default(0);
            $table->integer('seret')->default(0);
            $table->integer('lain_lain')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_defects');
    }
};