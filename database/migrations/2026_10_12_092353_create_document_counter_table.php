<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_counter', function (Blueprint $table) {
            $table->id();
            $table->string('jenis', 20);        // LAP, VFR, INT, EKS, MSN
            $table->year('tahun');               // 2026
            $table->integer('nomor_terakhir')->default(0);  // 0, 1, 2, dst
            $table->timestamps();

            // Kombinasi jenis + tahun harus unik
            $table->unique(['jenis', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_counter');
    }
};