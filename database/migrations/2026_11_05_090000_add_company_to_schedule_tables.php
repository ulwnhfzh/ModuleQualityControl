<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'verifikasis', 'internals', 'eksternals', 'eksternal_mesins',
        'verifikasi_internal_schedules', 'kalibrasi_internal_list_ups', 'verifikasi_qc2',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'company')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('company', 30)->default('HAMATETSU')->index();
                });
            }
        }

        Schema::table('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->dropForeign(['tool_id']);
            $table->dropUnique('verifikasi_internal_schedules_tool_id_tahun_unique');
            $table->unique(['tool_id', 'tahun', 'company'], 'verifikasi_schedule_tool_year_company_unique');
            $table->foreign('tool_id')->references('id')->on('tools')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->dropForeign(['tool_id']);
            $table->dropUnique('verifikasi_schedule_tool_year_company_unique');
            $table->unique(['tool_id', 'tahun']);
            $table->foreign('tool_id')->references('id')->on('tools')->cascadeOnDelete();
        });

        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['company']);
                $table->dropColumn('company');
            });
        }
    }
};
