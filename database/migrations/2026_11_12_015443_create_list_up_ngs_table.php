<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_up_ngs', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->string('tool_name');
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->string('number_seri')->nullable();
            $table->string('number_reg')->nullable();
            $table->string('part_name')->nullable();
            $table->string('part_number')->nullable();
            $table->date('ng_date');
            $table->string('dept');
            $table->string('category_ng');
            $table->text('note')->nullable();
            $table->integer('stock_qa')->default(0);
            $table->integer('stock_prod')->default(0);
            $table->string('no_spp')->nullable();
            $table->string('status_order')->default('CLOSED');
            $table->text('note_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_up_ngs');
    }
};