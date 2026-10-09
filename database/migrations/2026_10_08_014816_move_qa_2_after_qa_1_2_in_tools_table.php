<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pindahkan posisi kolom qa_2 supaya setelah qa_1_2
        Schema::table('tools', function (Blueprint $table) {
            $table->integer('qa_2')->default(0)->after('qa_1_2')->change();
        });
    }

    public function down(): void
    {
        // Kembalikan ke posisi semula (setelah eng)
        Schema::table('tools', function (Blueprint $table) {
            $table->integer('qa_2')->default(0)->after('eng')->change();
        });
    }
};