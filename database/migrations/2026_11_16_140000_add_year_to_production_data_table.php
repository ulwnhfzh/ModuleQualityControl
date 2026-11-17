<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $currentYear = (int) date('Y');

        Schema::table('data_all_produksis', function (Blueprint $table) use ($currentYear) {
            $table->unsignedSmallInteger('year')->default($currentYear)->after('category');
            $table->boolean('is_year_template')->default(false)->after('year');
            $table->index(['year', 'division'], 'data_all_produksis_year_division_index');
        });

        DB::table('data_all_produksis')->orderBy('id')->chunkById(500, function ($rows) use ($currentYear): void {
            foreach ($rows as $row) {
                $category = $row->category === null ? null : trim((string) $row->category);
                $year = $currentYear;

                if ($category !== null && preg_match('/^DATA ALAT UKUR ALL PRODUKSI(?:\s+TAHUN\s+(20\d{2}))?$/i', $category, $matches) === 1) {
                    $category = 'DATA ALAT UKUR ALL PRODUKSI';
                    if (isset($matches[1]) && $matches[1] !== '') {
                        $year = (int) $matches[1];
                    }
                }

                DB::table('data_all_produksis')
                    ->where('id', $row->id)
                    ->update(['category' => $category, 'year' => $year]);
            }
        });

        $sourceRows = DB::table('data_all_produksis')
            ->where('year', $currentYear)
            ->orderBy('id')
            ->get();
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        foreach (range($currentYear - 1, $currentYear - 5) as $year) {
            if ($sourceRows->isEmpty() || DB::table('data_all_produksis')->where('year', $year)->exists()) {
                continue;
            }

            $copies = [];
            foreach ($sourceRows as $sourceRow) {
                $copy = (array) $sourceRow;
                unset($copy['id']);
                $copy['year'] = $year;
                $copy['is_year_template'] = true;
                $copy['note'] = null;
                $copy['created_at'] = now();
                $copy['updated_at'] = now();
                foreach ($months as $month) {
                    $copy[$month] = null;
                }
                $copies[] = $copy;
            }

            foreach (array_chunk($copies, 20) as $chunk) {
                DB::table('data_all_produksis')->insert($chunk);
            }
        }
    }

    public function down(): void
    {
        DB::table('data_all_produksis')->where('is_year_template', true)->delete();

        Schema::table('data_all_produksis', function (Blueprint $table) {
            $table->dropIndex('data_all_produksis_year_division_index');
            $table->dropColumn(['year', 'is_year_template']);
        });
    }
};
