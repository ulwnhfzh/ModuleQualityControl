<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasis', function (Blueprint $table) {
            $table->string('verified_by')->nullable()->after('total');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });

        Schema::create('verification_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eksternal_mesin_id')->constrained('eksternal_mesins')->cascadeOnDelete();
            $table->date('next_date');
            $table->string('reminder_level', 20);
            $table->timestamps();
            $table->unique(['eksternal_mesin_id', 'next_date', 'reminder_level'], 'verif_reminder_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_reminder_logs');
        Schema::table('verifikasis', function (Blueprint $table) {
            $table->dropColumn(['verified_by', 'verified_at']);
        });
    }
};
