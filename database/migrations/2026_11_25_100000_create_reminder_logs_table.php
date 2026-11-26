<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source_key', 120);      // contoh: mesin:12, verif_int:5:p1, summary
            $table->date('due_date');               // tanggal jatuh tempo
            $table->string('level', 20);            // mendekati / terlambat / seen / summary
            $table->date('sent_on');                // tanggal pencatatan
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamps();

            $table->unique(['source_key', 'due_date', 'sent_on'], 'reminder_daily_once');
            $table->index(['source_key', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};