<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('aksi', 50);                   // CREATE, UPDATE, DELETE, LOGIN, LOGOUT, EXPORT
            $table->string('jenis_data', 50)->nullable(); // tools, verifikasi, internal, eksternal, mesin, user
            $table->unsignedBigInteger('data_id')->nullable();
            $table->string('keterangan')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('user_id');
            $table->index('aksi');
            $table->index('jenis_data');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};