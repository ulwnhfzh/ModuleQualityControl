<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('tools')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->string('shop', 100)->nullable();
            $table->date('period1_verification_date')->nullable();
            $table->date('period1_next_verification_date')->nullable();
            $table->string('period1_jgm', 30)->nullable();
            $table->date('period2_verification_date')->nullable();
            $table->date('period2_next_verification_date')->nullable();
            $table->string('period2_jgm', 30)->nullable();
            $table->timestamps();
            $table->unique(['tool_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_internal_schedules');
    }
};
