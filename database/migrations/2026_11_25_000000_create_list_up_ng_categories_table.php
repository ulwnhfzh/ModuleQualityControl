<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_up_ng_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        $now = now();
        DB::table('list_up_ng_categories')->insert([
            ['name' => 'VERIFIKASI', 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'KALIBRASI', 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'KALIBRASI EKSTENAL', 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('list_up_ng_categories');
    }
};