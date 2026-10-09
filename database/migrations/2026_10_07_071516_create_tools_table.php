<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name');
            $table->string('category')->default('Small Tool');
            
            // Kolom Mesin
            $table->string('machine_model')->nullable();
            $table->string('code_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('maker')->nullable();
            $table->string('no_machine')->nullable();
            $table->string('retension')->nullable();
            $table->date('start_date')->nullable();
            $table->date('next_date')->nullable();

            // Kolom QA & Departemen
            $table->integer('qa_1')->default(0);
            $table->integer('qa_1_2')->default(0);
            $table->integer('qa_inc')->default(0);
            $table->integer('qa_insp')->default(0);
            $table->integer('ppic_1')->default(0);
            $table->integer('ppic_2')->default(0);
            $table->integer('line_scc')->default(0);
            $table->integer('p1')->default(0);
            $table->integer('p2')->default(0);
            $table->integer('p3')->default(0);
            $table->integer('p5')->default(0);
            $table->integer('p6')->default(0);
            $table->integer('p7')->default(0);
            $table->integer('p8')->default(0);
            $table->integer('eng')->default(0);
            $table->integer('total')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};