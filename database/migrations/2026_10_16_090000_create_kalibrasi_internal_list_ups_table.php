<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kalibrasi_internal_list_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun');
            $table->string('tool_name');
            $table->string('tool_type', 100);
            $table->string('specification')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('shop', 100)->nullable();
            $table->date('calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->string('jgm', 30)->nullable();
            $table->timestamps();

            $table->index(['tahun', 'tool_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalibrasi_internal_list_ups');
    }
};
