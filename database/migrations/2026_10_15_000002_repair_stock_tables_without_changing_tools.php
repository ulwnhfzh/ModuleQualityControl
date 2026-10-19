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
                DB::statement('UPDATE `tool_stock_movements` AS m INNER JOIN `tool_stocks` AS s ON s.tool_id = m.tool_id SET m.stock_id = s.id WHERE m.stock_id IS NULL');
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
                DB::statement('ALTER TABLE `tool_stock_movements` MODIFY `stock_id` BIGINT UNSIGNED NOT NULL');
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

        DB::statement('UPDATE `tools` AS t INNER JOIN `tool_stocks` AS s ON s.tool_id = t.id SET t.order_number = s.order_number, t.specification = s.specification, t.opening_stock = s.opening_stock');

        if (Schema::hasTable('tool_stock_movements') && !Schema::hasColumn('tool_stock_movements', 'tool_id') && Schema::hasColumn('tool_stock_movements', 'stock_id')) {
            Schema::table('tool_stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('tool_id')->nullable()->after('id');
            });
            DB::statement('UPDATE `tool_stock_movements` AS m INNER JOIN `tool_stocks` AS s ON s.id = m.stock_id SET m.tool_id = s.tool_id');
            Schema::table('tool_stock_movements', function (Blueprint $table) {
                $table->dropForeign('tool_stock_movements_stock_id_foreign');
                $table->dropIndex('tool_stock_movements_stock_id_movement_date_index');
                $table->dropColumn('stock_id');
            });
            DB::statement('ALTER TABLE `tool_stock_movements` MODIFY `tool_id` BIGINT UNSIGNED NOT NULL');
            Schema::table('tool_stock_movements', function (Blueprint $table) {
                $table->index(['tool_id', 'movement_date'], 'tool_stock_movements_tool_id_movement_date_index');
                $table->foreign('tool_id')->references('id')->on('tools')->cascadeOnDelete();
            });
        }

        Schema::dropIfExists('tool_stocks');
    }
};
