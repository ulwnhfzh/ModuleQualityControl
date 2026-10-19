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

        DB::table('tools')->orderBy('id')->chunk(500, function ($tools) {
            $now = now();
            DB::table('tool_stocks')->insertOrIgnore($tools->map(fn ($tool) => [
                'tool_id' => $tool->id,
                'opening_stock' => $tool->total,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        });

        if (!Schema::hasTable('tool_stock_movements')) {
            Schema::create('tool_stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_id')->constrained('tool_stocks')->cascadeOnDelete();
                $table->date('movement_date');
                $table->enum('direction', ['in', 'out']);
                $table->unsignedInteger('quantity');
                $table->string('note')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['stock_id', 'movement_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_stock_movements');
        Schema::dropIfExists('tool_stocks');
    }
};
