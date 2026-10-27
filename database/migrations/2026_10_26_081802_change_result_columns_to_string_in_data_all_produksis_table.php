<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_all_produksis', function (Blueprint $table) {
            // Ubah 12 kolom bulan menjadi string agar bisa menampung teks/angka
            $table->string('jan', 50)->nullable()->change();
            $table->string('feb', 50)->nullable()->change();
            $table->string('mar', 50)->nullable()->change();
            $table->string('apr', 50)->nullable()->change();
            $table->string('may', 50)->nullable()->change();
            $table->string('jun', 50)->nullable()->change();
            $table->string('jul', 50)->nullable()->change();
            $table->string('aug', 50)->nullable()->change();
            $table->string('sep', 50)->nullable()->change();
            $table->string('oct', 50)->nullable()->change();
            $table->string('nov', 50)->nullable()->change();
            $table->string('dec', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('data_all_produksis', function (Blueprint $table) {
            $table->integer('jan')->default(0)->change();
            $table->integer('feb')->default(0)->change();
            $table->integer('mar')->default(0)->change();
            $table->integer('apr')->default(0)->change();
            $table->integer('may')->default(0)->change();
            $table->integer('jun')->default(0)->change();
            $table->integer('jul')->default(0)->change();
            $table->integer('aug')->default(0)->change();
            $table->integer('sep')->default(0)->change();
            $table->integer('oct')->default(0)->change();
            $table->integer('nov')->default(0)->change();
            $table->integer('dec')->default(0)->change();
        });
    }
};