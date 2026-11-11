<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasis', function (Blueprint $table) {
            // Kolom untuk mode Harian
            $table->integer('qa_1')->default(0)->after('tahun');
            $table->integer('qa_1_2')->default(0)->after('qa_1');
            $table->integer('qa_inc')->default(0)->after('qa_1_2');
            $table->integer('qa_insp')->default(0)->after('qa_inc');
            $table->integer('ppic_1')->default(0)->after('qa_insp');
            $table->integer('ppic_2')->default(0)->after('ppic_1');
            $table->integer('line_scc')->default(0)->after('ppic_2');
            $table->integer('p1')->default(0)->after('line_scc');
            $table->integer('p2')->default(0)->after('p1');
            $table->integer('p3')->default(0)->after('p2');
            $table->integer('p5')->default(0)->after('p3');
            $table->integer('p6')->default(0)->after('p5');
            $table->integer('p7')->default(0)->after('p6');
            $table->integer('p8')->default(0)->after('p7');
            $table->integer('eng')->default(0)->after('p8');
            
            // Kolom untuk menandai tipe (bulanan/harian)
            $table->string('tipe')->default('bulanan')->after('tahun');
            
            // Tanggal untuk mode harian
            $table->date('tanggal')->nullable()->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('verifikasis', function (Blueprint $table) {
            $table->dropColumn([
                'qa_1', 'qa_1_2', 'qa_inc', 'qa_insp',
                'ppic_1', 'ppic_2', 'line_scc',
                'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng',
                'tipe', 'tanggal'
            ]);
        });
    }
};