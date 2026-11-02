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

        DB::table('tool_stocks as stocks')
            ->join('tools as tools', 'tools.id', '=', 'stocks.tool_id')
            ->whereNull('stocks.tool_name')
            ->select([
                'stocks.id',
                'tools.tool_name',
                'tools.category',
            ])
            ->orderBy('stocks.id')
            ->chunk(500, function ($stockRows) {
                foreach ($stockRows as $stock) {
                    DB::table('tool_stocks')->where('id', $stock->id)->update([
                        'tool_name' => $stock->tool_name,
                        'category' => $stock->category,
                        'type' => $stock->category,
                    ]);
                }
            });
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
