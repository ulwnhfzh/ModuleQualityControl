<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_calibrators', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->index();
            $table->string('tool_name', 150)->index();
            $table->string('type', 100)->nullable();
            $table->string('size', 150)->nullable();
            $table->string('number_seri', 100)->nullable();
            $table->string('number_reg', 100)->nullable();
            $table->string('part_name', 150)->nullable();
            $table->string('part_number', 150)->nullable();
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month) {
                $table->string($month, 30)->nullable();
            }
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['tahun', 'tool_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_calibrators');
    }
};
