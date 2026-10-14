<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('value', 255)->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // Isi default
        DB::table('pengaturan')->insert([
            [
                'key' => 'warning_days_cepat',
                'value' => '7',
                'keterangan' => 'Batas hari untuk notifikasi jatuh tempo cepat (kuning/oranye)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'warning_days_standar',
                'value' => '30',
                'keterangan' => 'Batas hari untuk notifikasi jatuh tempo standar (biru)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notif_dashboard',
                'value' => '1',
                'keterangan' => 'Tampilkan notifikasi di Dashboard (1=Ya, 0=Tidak)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'notif_header',
                'value' => '1',
                'keterangan' => 'Tampilkan badge notifikasi di header (1=Ya, 0=Tidak)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};