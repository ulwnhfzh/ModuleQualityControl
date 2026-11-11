<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alat_id');           // ID dari eksternal_mesins
            $table->string('alat_nama');                     // Nama alat (cache)
            $table->date('next_date');                       // Tanggal jatuh tempo (cache)
            $table->string('status', 20)->default('diproses'); // diproses, selesai, dibatalkan
            $table->string('catatan')->nullable();           // Catatan user
            $table->unsignedBigInteger('user_id')->nullable(); // User yang aksi
            $table->string('user_name')->nullable();         // Nama user (cache)
            $table->timestamp('diproses_at')->nullable();    // Kapan ditandai diproses
            $table->timestamp('selesai_at')->nullable();     // Kapan selesai
            $table->timestamps();

            // Index
            $table->index('alat_id');
            $table->index('status');
            $table->index('next_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_status');
    }
};