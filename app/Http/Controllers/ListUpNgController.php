<?php

namespace App\Http\Controllers;

use App\Models\ListUpNg;
use App\Models\ListUpNgCategory;
use App\Models\ProductionData;
use App\Models\ToolDefect;
use Illuminate\Http\Request;
use App\Imports\ListUpNgImport;
use App\Imports\SpreadsheetSheetSelector;
use App\Exports\ListUpNgExport;
use App\Services\EquipmentIdentityMatcher;
use App\Services\ListUpNgRecapSynchronizer;
use App\Services\ListUpNgStatusSynchronizer;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Illuminate\Validation\ValidationException;

class ListUpNgController extends Controller
{
    private const ADD_NEW_TYPE = '__add_new_type__';
    private const ADD_NEW_CATEGORY = '__add_new_category__';
    private const DEPARTMENT_ALIASES = [
        'QA INC', 'QA INSP', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'LSCC', 'ENG',
    ];

    /**
     * Daftar divisi standar yang selalu tampil di dropdown Division,
     * digabung dengan divisi yang sudah ada di database.
     */
    private const STANDARD_DIVISIONS = [
        'QA 1', 'QA 2', 'QA INC', 'QA INSP', 'PPIC 1', 'PPIC 2', 'LINE SCC',
        'PRODUKSI 1', 'PRODUKSI 2', 'PRODUKSI 3', 'PRODUKSI 5', 'PRODUKSI 6', 'PRODUKSI 7', 'PRODUKSI 8',
        'ENGINEERING', 'NISSHO',
    ];

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan');
        $division = $request->input('division');
        $categoryNg = $request->input('category_ng');

        $departments = ListUpNg::where('tahun', $tahun)
            ->whereNotNull('division')
            ->where('division', '<>', '')
            ->distinct()
            ->orderBy('division', 'asc')
            ->pluck('division')
            ->toArray();

        $categories = ListUpNg::where('tahun', $tahun)
            ->whereNotNull('category_ng')
            ->where('category_ng', '<>', '')
            ->distinct()
            ->orderBy('category_ng', 'asc')
            ->pluck('category_ng')
            ->toArray();

        $query = ListUpNg::where('tahun', $tahun);

        if ($bulan) {
            $query->whereMonth('ng_date', $bulan);
        }

        if ($division) {
            $query->where('division', $division);
        }

        if ($categoryNg) {
            $query->where('category_ng', $categoryNg);
        }

        $ngs = $query->orderBy('ng_date', 'desc')->get();

        return view('list-up-ng.index', compact('ngs', 'tahun', 'bulan', 'division', 'categoryNg', 'departments', 'categories'));
    }

    public function create()
    {
        $selectedToolName = old('tool_name');
        $selectedType = old('type');
        if ($selectedType === self::ADD_NEW_TYPE) {
            $selectedType = null;
        }
        $toolOptions = $this->productionToolOptions($selectedToolName, $selectedType);

        $departments = $this->departmentOptions();
        $categoryOptions = $this->categoryOptions();

        return view('list-up-ng.create', compact('toolOptions', 'selectedToolName', 'selectedType', 'departments', 'categoryOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tool_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'new_type' => 'required_if:type,' . self::ADD_NEW_TYPE . '|nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'number_seri' => 'nullable|string|max:255',
            'number_reg' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'ng_date' => 'required|date',
            'division' => 'required|string|max:255', // UBAH
            'category_ng' => 'required|string|max:255',
            'new_category_ng' => 'required_if:category_ng,' . self::ADD_NEW_CATEGORY . '|nullable|string|max:255',
            'note' => 'nullable|string',
        ]);
        $validated = $this->resolveTypeSelection($validated);
        $validated = $this->resolveCategorySelection($validated);

        $validated['tahun'] = date('Y', strtotime($request->ng_date));

        DB::transaction(function () use ($validated): void {
            $this->saveNewTypeToProductionMaster($validated);
            $this->ensureCategoryExists($validated['category_ng']);
            ListUpNg::create($validated);
        });

        return redirect()->route('list-up-ng.index', ['tahun' => $validated['tahun']])
            ->with('success', 'Data NG berhasil ditambahkan.');
    }

    public function edit(ListUpNg $listUpNg)
    {
        $selectedToolName = old('tool_name', $listUpNg->tool_name);
        $selectedType = old('type', $listUpNg->type);
        if ($selectedType === self::ADD_NEW_TYPE) {
            $selectedType = null;
        }
        $toolOptions = $this->productionToolOptions($selectedToolName, $selectedType);

        $departments = $this->departmentOptions();
        $categoryOptions = $this->categoryOptions();

        return view('list-up-ng.edit', compact('listUpNg', 'toolOptions', 'selectedToolName', 'selectedType', 'departments', 'categoryOptions'));
    }

    public function update(Request $request, ListUpNg $listUpNg)
    {
        $validated = $request->validate([
            'tool_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'new_type' => 'required_if:type,' . self::ADD_NEW_TYPE . '|nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'number_seri' => 'nullable|string|max:255',
            'number_reg' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'ng_date' => 'required|date',
            'division' => 'required|string|max:255', // UBAH
            'category_ng' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);
        $validated = $this->resolveTypeSelection($validated);

        $validated['tahun'] = date('Y', strtotime($request->ng_date));

        DB::transaction(function () use ($validated, $listUpNg): void {
            $this->saveNewTypeToProductionMaster($validated);
            $listUpNg->update($validated);
        });

        return redirect()->route('list-up-ng.index', ['tahun' => $validated['tahun']])
            ->with('success', 'Data NG berhasil diupdate.');
    }

    public function destroy(ListUpNg $listUpNg)
    {
        $tahun = (int) $listUpNg->tahun;
        DB::transaction(fn () => $listUpNg->delete());
        return redirect()->route('list-up-ng.index', ['tahun' => $tahun])
            ->with('success', 'Data NG berhasil dihapus.');
    }

    public function importForm()
    {
        return view('list-up-ng.import', ['tahun' => (int) date('Y')]);
    }

    public function importStore(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        try {
            $sheetImports = [];
            $import = SpreadsheetSheetSelector::forHeaders(
                $request->file('file'),
                [
                    ['tool_name', 'nama_alat'],
                    ['ng_date', 'tanggal_ng'],
                    ['division', 'dept'], // UBAH: mendukung header 'division' atau 'dept' di Excel
                    ['category_ng', 'kategori_ng'],
                ],
                function (int $rowHeader) use (&$sheetImports, $validated): ListUpNgImport {
                    $sheetImport = new ListUpNgImport($rowHeader, (int) $validated['tahun']);
                    $sheetImports[] = $sheetImport;

                    return $sheetImport;
                },
            );

            DB::transaction(function () use ($import, $sheetImports, $request, $validated): void {
                $oldRecords = ListUpNg::where('tahun', $validated['tahun'])
                    ->get()
                    ->map(fn (ListUpNg $row): array => $row->getAttributes())
                    ->all();

                ListUpNgStatusSynchronizer::withoutAutomaticSync(function () use ($import, $sheetImports, $request, $validated): void {
                    ListUpNgRecapSynchronizer::withoutAutomaticSync(function () use ($import, $sheetImports, $request, $validated): void {
                        ListUpNg::where('tahun', $validated['tahun'])->delete();
                        Excel::import($import, $request->file('file'));

                        $importedCount = array_sum(array_map(
                            fn (ListUpNgImport $sheetImport): int => $sheetImport->getImportedCount(),
                            $sheetImports,
                        ));
                        if ($importedCount === 0) {
                            throw new RuntimeException("Tidak ada data List Up NG untuk tahun {$validated['tahun']}.");
                        }
                    });
                });

                $newRecords = ListUpNg::where('tahun', $validated['tahun'])
                    ->get()
                    ->map(fn (ListUpNg $row): array => $row->getAttributes())
                    ->all();
                app(ListUpNgStatusSynchronizer::class)->syncYear((int) $validated['tahun']);
                app(ListUpNgRecapSynchronizer::class)->syncChanges($oldRecords, $newRecords);
            });

            return redirect()->route('list-up-ng.index', ['tahun' => $validated['tahun']])
                ->with('success', "Data List Up NG tahun {$validated['tahun']} berhasil diganti dari workbook.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan');
        $division = $request->input('division');
        $categoryNg = $request->input('category_ng');

        return Excel::download(new ListUpNgExport($tahun, $division, $categoryNg, $bulan), "List_Up_NG_{$tahun}.xlsx");
    }

    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $bulan = $request->input('bulan');
        $division = $request->input('division');
        $categoryNg = $request->input('category_ng');

        $query = ListUpNg::where('tahun', $tahun);
        if ($bulan) {
            $query->whereMonth('ng_date', $bulan);
        }
        if ($division) {
            $query->where('division', $division);
        }
        if ($categoryNg) {
            $query->where('category_ng', $categoryNg);
        }
        $ngs = $query->orderBy('ng_date', 'asc')->get();

        $pdf = Pdf::loadView('list-up-ng.pdf', compact('ngs', 'tahun', 'bulan', 'division', 'categoryNg'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("List_Up_NG_{$tahun}.pdf");
    }

    /**
     * Daftar divisi untuk dropdown form: divisi standar digabung
     * dengan divisi yang sudah ada di List Up NG, tanpa duplikat.
     */
    private function departmentOptions(): array
    {
        return collect(self::STANDARD_DIVISIONS)
            ->merge(
                ListUpNg::whereNotNull('division')
                    ->where('division', '<>', '')
                    ->distinct()
                    ->pluck('division')
            )
            ->map(fn ($division) => trim((string) $division))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function productionToolOptions(?string $selectedToolName = null, ?string $selectedType = null): array
    {
        $records = ProductionData::query()
            ->whereNotNull('tool_name')
            ->where('tool_name', '<>', '')
            ->orderBy('tool_name')
            ->get(['tool_name', 'type'])
            ->groupBy('tool_name');

        $toolOptions = [];
        foreach ($records as $toolName => $items) {
            $toolOptions[$toolName] = $items->pluck('type')
                ->filter(fn ($type) => trim((string) $type) !== '')
                ->map(fn ($type) => trim((string) $type))
                ->unique()
                ->sort()
                ->values()
                ->all();
        }

        if ($selectedToolName !== null && $selectedToolName !== '') {
            $toolOptions[$selectedToolName] ??= [];
            if ($selectedType !== null && $selectedType !== '' && ! in_array($selectedType, $toolOptions[$selectedToolName], true)) {
                $toolOptions[$selectedToolName][] = $selectedType;
            }
        }

        ksort($toolOptions, SORT_NATURAL | SORT_FLAG_CASE);

        return $toolOptions;
    }

    private function resolveTypeSelection(array $validated): array
    {
        if (($validated['type'] ?? null) === self::ADD_NEW_TYPE) {
            $validated['type'] = trim((string) $validated['new_type']);
        }

        unset($validated['new_type']);

        return $validated;
    }

    /**
     * Daftar category untuk dropdown: dari tabel master, lalu
     * category lama yang hanya ada di data List Up NG (tidak bisa dihapus).
     */
    private function categoryOptions(): array
    {
        $masters = ListUpNgCategory::orderBy('id')
            ->get(['id', 'name', 'is_default'])
            ->map(fn (ListUpNgCategory $cat): array => [
                'id' => $cat->id,
                'name' => $cat->name,
                'is_default' => (bool) $cat->is_default,
            ]);

        $knownNames = $masters->pluck('name')->all();

        $legacy = ListUpNg::whereNotNull('category_ng')
            ->where('category_ng', '<>', '')
            ->distinct()
            ->pluck('category_ng')
            ->map(fn ($name) => ListUpNgCategory::normalize($name))
            ->filter(fn (string $name): bool => $name !== '' && ! in_array($name, $knownNames, true))
            ->unique()
            ->map(fn (string $name): array => ['id' => null, 'name' => $name, 'is_default' => true]);

        return $masters->concat($legacy)->values()->all();
    }

    private function resolveCategorySelection(array $validated): array
    {
        if (($validated['category_ng'] ?? null) === self::ADD_NEW_CATEGORY) {
            $name = ListUpNgCategory::normalize($validated['new_category_ng'] ?? '');
            if ($name === '') {
                throw ValidationException::withMessages([
                    'new_category_ng' => 'Nama category baru wajib diisi. Silakan masukkan nama category atau pilih category yang sudah tersedia.',
                ]);
            }
            $validated['category_ng'] = $name;
        } else {
            $validated['category_ng'] = ListUpNgCategory::normalize($validated['category_ng'] ?? '');
        }

        unset($validated['new_category_ng']);

        return $validated;
    }

    private function ensureCategoryExists(string $name): void
    {
        if ($name !== '') {
            ListUpNgCategory::firstOrCreate(['name' => $name]);
        }
    }

    /**
     * Hapus category dari master. Data NG yang sudah memakai category
     * ini tetap tersimpan, hanya tidak muncul lagi di pilihan form.
     */
    public function destroyCategory(ListUpNgCategory $category)
    {
        if ($category->is_default) {
            return response()->json([
                'message' => 'Category "' . $category->name . '" merupakan category bawaan sistem dan tidak dapat dihapus.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category "' . $category->name . '" berhasil dihapus.',
        ]);
    }

    private function saveNewTypeToProductionMaster(array $data): void
    {
        $type = trim((string) ($data['type'] ?? ''));
        $toolName = $this->normalizeKey($data['tool_name'] ?? null);
        if ($type === '' || $toolName === '') {
            return;
        }

        $masterRows = ProductionData::query()
            ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
            ->where('year', (int) ($data['tahun'] ?? date('Y')))
            ->get();

        if ($masterRows->contains(fn (ProductionData $row): bool =>
            $this->normalizeKey($row->type) === $this->normalizeKey($type)
        )) {
            return;
        }

        if ($masterRows->isEmpty()) {
            throw ValidationException::withMessages([
                'tool_name' => 'Nama alat belum terdaftar di Data All Produksi. Silakan tambahkan alat tersebut terlebih dahulu.',
            ]);
        }

        $candidate = new ListUpNg($data);
        if (app(EquipmentIdentityMatcher::class)->productionMatches($candidate)->isNotEmpty()) {
            return;
        }

        $division = $this->canonicalProductionDivision($data['division'] ?? ''); // UBAH: dept -> division
        $prototype = $masterRows->first(
            fn (ProductionData $row): bool => $this->canonicalProductionDivision($row->division) === $division
        ) ?? $masterRows->first();
        $category = trim((string) $prototype?->category);

        if ($category === '') {
            throw ValidationException::withMessages([
                'type' => 'Kategori untuk alat ini belum diatur di Data All Produksi. Silakan lengkapi data tersebut terlebih dahulu.',
            ]);
        }

        ProductionData::create([
            'division' => $division,
            'category' => $category,
            'year' => (int) ($data['tahun'] ?? date('Y')),
            'is_year_template' => false,
            'tool_name' => trim((string) $data['tool_name']),
            'type' => $type,
            'size' => trim((string) ($data['size'] ?? '')) ?: '-',
            'number_seri' => $this->nullableText($data['number_seri'] ?? null),
            'number_reg' => $this->nullableText($data['number_reg'] ?? null),
            'part_name' => $this->nullableText($data['part_name'] ?? null),
            'part_number' => $this->nullableText($data['part_number'] ?? null),
        ]);
    }

    private function canonicalProductionDivision(string $division): string
    {
        $division = mb_strtoupper(trim(preg_replace('/\s+/', ' ', $division) ?? ''));

        return match ($division) {
            'QA1' => 'QA 1',
            'QA2' => 'QA 2',
            'QA INC' => 'QA INCOMING',
            'QA INSP' => 'QA INSPECTION',
            'LSCC', 'L.SCC' => 'LINE SCC',
            'P1' => 'PRODUKSI 1',
            'P2' => 'PRODUKSI 2',
            'P3' => 'PRODUKSI 3',
            'P5' => 'PRODUKSI 5',
            'P6' => 'PRODUKSI 6',
            'P7' => 'PRODUKSI 7',
            'P8' => 'PRODUKSI 8',
            'ENG' => 'ENGINEERING',
            default => $division,
        };
    }

    private function normalizeKey(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}