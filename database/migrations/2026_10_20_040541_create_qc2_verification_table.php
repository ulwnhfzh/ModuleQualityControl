<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_qc2', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tool_id')->unsigned()->nullable();
            $table->string('tool_name')->nullable();
            $table->string('tool_type', 100)->nullable();
            $table->string('specification')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->smallInteger('tahun')->unsigned();
            $table->string('shop', 100)->nullable();
            
            // HANYA 1 PERIODE (Karena setahun sekali)
            $table->date('verification_date')->nullable();
            $table->date('next_verification_date')->nullable();
            $table->string('jgm', 30)->nullable();
            
            $table->timestamps();
            
            $table->foreign('tool_id')->references('id')->on('tools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_qc2');
    }
};