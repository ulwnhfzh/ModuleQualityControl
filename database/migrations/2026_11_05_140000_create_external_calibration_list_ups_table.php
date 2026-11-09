<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_calibration_list_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->index();
            $table->string('tool_name', 150)->index();
            $table->string('specification', 255)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('shop', 100)->nullable();
            $table->date('calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->string('jgm', 30)->nullable();
            $table->unsignedInteger('calibration_count')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['tahun', 'tool_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_calibration_list_ups');
    }
};
