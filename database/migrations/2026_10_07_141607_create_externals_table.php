<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eksternals', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name');
            $table->year('tahun');
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
            $table->integer('qty_set')->default(0); // <-- Kolom QTY SET
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eksternals');
    }
};