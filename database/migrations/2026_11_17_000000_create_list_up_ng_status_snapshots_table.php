<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_up_ng_status_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 40);
            $table->unsignedSmallInteger('target_year');
            $table->unsignedBigInteger('target_id');
            $table->string('status_field', 40);
            $table->text('original_value')->nullable();
            $table->timestamps();
            $table->unique(['target_type', 'target_id', 'status_field'], 'list_up_ng_status_snapshot_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('list_up_ng_status_snapshots');
    }
};
