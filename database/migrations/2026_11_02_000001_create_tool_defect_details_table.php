<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_defect_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->index();
            $table->string('tool_name');
            $table->string('division')->nullable()->index();
            $table->string('defect_type')->nullable()->index();
            $table->date('tanggal')->nullable();
            $table->string('serial_no')->nullable();
            $table->timestamps();
            $table->index(['tahun', 'tool_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_defect_details');
    }
};
