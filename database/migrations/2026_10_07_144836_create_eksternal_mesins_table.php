<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eksternal_mesins', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name');
            $table->string('machine_model')->nullable();
            $table->string('code_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('maker')->nullable();
            $table->string('no_machine')->nullable();
            $table->string('retension')->nullable();
            $table->date('start_date')->nullable();
            $table->date('next_date')->nullable();
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
            $table->integer('total')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eksternal_mesins');
    }
};