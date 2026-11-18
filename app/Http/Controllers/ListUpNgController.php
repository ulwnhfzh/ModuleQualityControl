<?php

namespace App\Http\Controllers;

use App\Models\ListUpNg;
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
    private const DEPARTMENT_ALIASES = [
        'QA INC', 'QA INSP', 'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'LSCC', 'ENG',
    ];

    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $dept = $request->input('dept');
        $departments = array_values(array_unique(array_merge(ToolDefect::DIVISIONS, self::DEPARTMENT_ALIASES)));

        $query = ListUpNg::where('tahun', $tahun);

        if ($dept) {
            $query->where('dept', $dept);
        }

        $ngs = $query->orderBy('ng_date', 'desc')->get();

        return view('list-up-ng.index', compact('ngs', 'tahun', 'dept', 'departments'));
    }

    public function create()
    {
        $selectedToolName = old('tool_name');
        $selectedType = old('type');
        if ($selectedType === self::ADD_NEW_TYPE) {
            $selectedType = null;
        }
        $toolOptions = $this->productionToolOptions($selectedToolName, $selectedType);
        $departments = array_values(array_unique(array_merge(ToolDefect::DIVISIONS, self::DEPARTMENT_ALIASES)));

        return view('list-up-ng.create', compact('toolOptions', 'selectedToolName', 'selectedType', 'departments'));
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
            'dept' => 'required|string|max:255',
            'category_ng' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);
        $validated = $this->resolveTypeSelection($validated);

        $validated['tahun'] = date('Y', strtotime($request->ng_date));

        DB::transaction(function () use ($validated): void {
            $this->saveNewTypeToProductionMaster($validated);
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
        $departments = array_values(array_unique(array_merge(ToolDefect::DIVISIONS, self::DEPARTMENT_ALIASES)));

        return view('list-up-ng.edit', compact('listUpNg', 'toolOptions', 'selectedToolName', 'selectedType', 'departments'));
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
            'dept' => 'required|string|max:255',
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
                    ['dept', 'division'],
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
        $dept = $request->input('dept');
        return Excel::download(new ListUpNgExport($tahun, $dept), "List_Up_NG_{$tahun}.xlsx");
    }

    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $dept = $request->input('dept');

        $query = ListUpNg::where('tahun', $tahun);
        if ($dept) {
            $query->where('dept', $dept);
        }
        $ngs = $query->orderBy('ng_date', 'asc')->get();

        $pdf = Pdf::loadView('list-up-ng.pdf', compact('ngs', 'tahun', 'dept'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("List_Up_NG_{$tahun}.pdf");
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
                'tool_name' => 'Tool Name belum tersedia di Data All Produksi.',
            ]);
        }

        $candidate = new ListUpNg($data);
        if (app(EquipmentIdentityMatcher::class)->productionMatches($candidate)->isNotEmpty()) {
            return;
        }

        $division = $this->canonicalProductionDivision($data['dept'] ?? '');
        $prototype = $masterRows->first(
            fn (ProductionData $row): bool => $this->canonicalProductionDivision($row->division) === $division
        ) ?? $masterRows->first();
        $category = trim((string) $prototype?->category);

        if ($category === '') {
            throw ValidationException::withMessages([
                'type' => 'Kategori Data All Produksi untuk alat ini belum tersedia.',
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