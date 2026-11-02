<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tool_stocks')) {
            Schema::create('tool_stocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tool_id')->unique()->constrained('tools')->cascadeOnDelete();
                $table->string('order_number')->nullable();
                $table->string('specification')->nullable();
                $table->unsignedInteger('opening_stock')->default(0);
                $table->timestamps();
            });
        }

        $toolColumns = Schema::getColumnListing('tools');
        DB::table('tools')->orderBy('id')->chunk(500, function ($tools) use ($toolColumns) {
            $now = now();
            $rows = $tools->map(function ($tool) use ($toolColumns, $now) {
                return [
                    'tool_id' => $tool->id,
                    'order_number' => in_array('order_number', $toolColumns, true) ? $tool->order_number : null,
                    'specification' => in_array('specification', $toolColumns, true) ? $tool->specification : null,
                    'opening_stock' => in_array('opening_stock', $toolColumns, true) ? $tool->opening_stock : $tool->total,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            if ($rows) {
                DB::table('tool_stocks')->insertOrIgnore($rows);
            }
        });

        if (Schema::hasTable('tool_stock_movements')) {
            $movementColumns = Schema::getColumnListing('tool_stock_movements');

            if (!in_array('stock_id', $movementColumns, true)) {
                Schema::table('tool_stock_movements', function (Blueprint $table) {
                    $table->unsignedBigInteger('stock_id')->nullable()->after('tool_id');
                });
                DB::table('tool_stock_movements as movements')
                    ->join('tool_stocks as stocks', 'stocks.tool_id', '=', 'movements.tool_id')
                    ->whereNull('movements.stock_id')
                    ->update(['movements.stock_id' => DB::raw('stocks.id')]);
            }

            if (in_array('tool_id', $movementColumns, true)) {
                Schema::table('tool_stock_movements', function (Blueprint $table) {
                    $table->dropForeign('tool_stock_movements_tool_id_foreign');
                    $table->dropIndex('tool_stock_movements_tool_id_movement_date_index');
                    $table->dropColumn('tool_id');
                });
            }

            $movementColumns = Schema::getColumnListing('tool_stock_movements');
            if (in_array('stock_id', $movementColumns, true) && !Schema::hasColumn('tool_stock_movements', 'tool_id')) {
                Schema::table('tool_stock_movements', function (Blueprint $table) {
                    $table->unsignedBigInteger('stock_id')->nullable(false)->change();
                });
                $foreignKeys = Schema::getForeignKeys('tool_stock_movements');
                $indexes = Schema::getIndexListing('tool_stock_movements');
                Schema::table('tool_stock_movements', function (Blueprint $table) use ($foreignKeys, $indexes) {
                    if (!in_array('tool_stock_movements_stock_id_foreign', array_column($foreignKeys, 'name'), true)) {
                        $table->foreign('stock_id')->references('id')->on('tool_stocks')->cascadeOnDelete();
                    }
                    if (!in_array('tool_stock_movements_stock_id_movement_date_index', $indexes, true)) {
                        $table->index(['stock_id', 'movement_date'], 'tool_stock_movements_stock_id_movement_date_index');
                    }
                });
            }
        }

        $extraToolColumns = array_values(array_intersect(
            ['order_number', 'specification', 'opening_stock'],
            Schema::getColumnListing('tools')
        ));
        if ($extraToolColumns) {
            Schema::table('tools', fn (Blueprint $table) => $table->dropColumn($extraToolColumns));
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tool_stocks')) {
            return;
        }

        $toolColumns = Schema::getColumnListing('tools');
        $missing = array_values(array_diff(['order_number', 'specification', 'opening_stock'], $toolColumns));
        if ($missing) {
            Schema::table('tools', function (Blueprint $table) use ($missing) {
                if (in_array('order_number', $missing, true)) $table->string('order_number')->nullable();
                if (in_array('specification', $missing, true)) $table->string('specification')->nullable();
                if (in_array('opening_stock', $missing, true)) $table->unsignedInteger('opening_stock')->default(0);
            });
        }

        DB::table('tool_stocks as stocks')
            ->join('tools as tools', 'tools.id', '=', 'stocks.tool_id')
            ->select([
                'tools.id',
                'stocks.order_number',
                'stocks.specification',
                'stocks.opening_stock',
            ])
            ->orderBy('tools.id')
            ->chunk(500, function ($stockRows) {
                foreach ($stockRows as $stock) {
                    DB::table('tools')->where('id', $stock->id)->update([
                        'order_number' => $stock->order_number,
                        'specification' => $stock->specification,
                        'opening_stock' => $stock->opening_stock,
                    ]);
                }
            });

        if (Schema::hasTable('tool_stock_movements') && !Schema::hasColumn('tool_stock_movements', 'tool_id') && Schema::hasColumn('tool_stock_movements', 'stock_id')) {
            Schema::table('tool_stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('tool_id')->nullable()->after('id');
            });
            DB::table('tool_stock_movements as movements')
                ->join('tool_stocks as stocks', 'stocks.id', '=', 'movements.stock_id')
                ->update(['movements.tool_id' => DB::raw('stocks.tool_id')]);
            Schema::table('tool_stock_movements', function (Blueprint $table) {
                $table->dropForeign('tool_stock_movements_stock_id_foreign');
                $table->dropIndex('tool_stock_movements_stock_id_movement_date_index');
                $table->dropColumn('stock_id');
            });
            Schema::table('tool_stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('tool_id')->nullable(false)->change();
                $table->index(['tool_id', 'movement_date'], 'tool_stock_movements_tool_id_movement_date_index');
                $table->foreign('tool_id')->references('id')->on('tools')->cascadeOnDelete();
            });
        }

        Schema::dropIfExists('tool_stocks');
    }
};
