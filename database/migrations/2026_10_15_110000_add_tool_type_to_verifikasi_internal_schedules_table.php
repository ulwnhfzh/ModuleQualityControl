<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->string('tool_type', 100)->nullable()->after('tool_id');
        });
    }

    public function down(): void
    {
        Schema::table('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->dropColumn('tool_type');
        });
    }
};
