<?php

use App\Models\Tool;
use App\Models\ToolStock;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->string('tool_name')->nullable()->after('tool_id');
            $table->string('specification')->nullable()->after('tool_type');
            $table->string('serial_number', 100)->nullable()->after('specification');
            $table->string('registration_number', 100)->nullable()->after('serial_number');
            $table->foreignId('tool_id')->nullable()->change();
        });

        DB::table('verifikasi_internal_schedules')->whereNotNull('tool_id')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $tool = Tool::find($row->tool_id);
                if (!$tool) {
                    continue;
                }

                $specification = ToolStock::where('tool_id', $tool->id)->value('specification');
                DB::table('verifikasi_internal_schedules')->where('id', $row->id)->update([
                    'tool_name' => $row->tool_name ?: $tool->tool_name,
                    'specification' => $row->specification ?: $specification,
                    'serial_number' => $row->serial_number ?: $tool->serial_number,
                    'registration_number' => $row->registration_number ?: $tool->code_number,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('verifikasi_internal_schedules', function (Blueprint $table) {
            $table->dropColumn(['tool_name', 'specification', 'serial_number', 'registration_number']);
        });
    }
};
