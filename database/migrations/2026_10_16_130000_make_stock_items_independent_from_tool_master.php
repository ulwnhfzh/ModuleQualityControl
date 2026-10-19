<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tool_stocks', function (Blueprint $table) {
            $table->string('tool_name')->nullable()->after('tool_id');
            $table->string('type')->nullable()->after('tool_name');
            $table->string('category')->nullable()->after('type')->index();
            $table->unsignedBigInteger('tool_id')->nullable()->change();
        });

        DB::statement('UPDATE tool_stocks s INNER JOIN tools t ON t.id = s.tool_id SET s.tool_name = t.tool_name, s.category = t.category, s.type = t.category WHERE s.tool_name IS NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('tool_stocks')) return;

        DB::table('tool_stocks')->whereNull('tool_id')->delete();
        Schema::table('tool_stocks', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['tool_name', 'type', 'category']);
            $table->unsignedBigInteger('tool_id')->nullable(false)->change();
        });
    }
};
