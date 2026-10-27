<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_all_produksis', function (Blueprint $table) {
            $table->id();
            $table->string('division'); // Menyimpan nama Division (QA 1, LINE SCC, dll)
            $table->string('tool_name');
            $table->string('type');
            $table->string('size');
            $table->string('number_seri')->nullable();
            $table->string('number_reg')->nullable();
            $table->string('part_name')->nullable();
            $table->string('part_number')->nullable();
            // Kolom Result per Bulan
            $table->integer('jan')->default(0);
            $table->integer('feb')->default(0);
            $table->integer('mar')->default(0);
            $table->integer('apr')->default(0);
            $table->integer('may')->default(0);
            $table->integer('jun')->default(0);
            $table->integer('jul')->default(0);
            $table->integer('aug')->default(0);
            $table->integer('sep')->default(0);
            $table->integer('oct')->default(0);
            $table->integer('nov')->default(0);
            $table->integer('dec')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_all_produksis');
    }
};