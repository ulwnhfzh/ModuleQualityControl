<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolStock;
use App\Models\DokumenCounter;
use App\Support\DocumentPdfLayout;
use Illuminate\Http\Request;
use App\Imports\ToolsImport;
use App\Exports\ToolsExport;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ToolController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'kategori' => 'nullable|string|max:100',
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $kategori = $filters['kategori'] ?? 'Small Tool';
        $categories = Tool::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $query = Tool::query();

        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        if ($kategori) {
            $query->where('category', $kategori);
        }

        $tools = $query->orderBy('tool_name', 'asc')->get();
        return view('tools.index', compact('tools', 'tahun', 'kategori', 'categories'));
    }

    public function create()
    {
        $categories = Tool::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        return view('tools.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'category_new' => 'required_if:category,__new|nullable|string|max:100',
            'order_number' => 'nullable|string|max:255',
            'specification' => 'nullable|string|max:255',
            'opening_stock' => 'required|integer|min:0|max:2147483647',
            'qa_1' => 'integer|min:0|max:2147483647', 'qa_1_2' => 'integer|min:0|max:2147483647', 'qa_inc' => 'integer|min:0|max:2147483647', 'qa_insp' => 'integer|min:0|max:2147483647',
            'ppic_1' => 'integer|min:0|max:2147483647', 'ppic_2' => 'integer|min:0|max:2147483647', 'line_scc' => 'integer|min:0|max:2147483647',
            'p1' => 'integer|min:0|max:2147483647', 'p2' => 'integer|min:0|max:2147483647', 'p3' => 'integer|min:0|max:2147483647', 'p5' => 'integer|min:0|max:2147483647',
            'p6' => 'integer|min:0|max:2147483647', 'p7' => 'integer|min:0|max:2147483647', 'p8' => 'integer|min:0|max:2147483647', 'eng' => 'integer|min:0|max:2147483647',
            'qa_2' => 'integer|min:0|max:2147483647',
        ]);

        if ($data['category'] === '__new') {
            $data['category'] = trim($data['category_new']);
        }
        unset($data['category_new']);
        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['tahun'] = date('Y');

        $data['total'] = array_sum([
            $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
            $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
            $data['p1'], $data['p2'], $data['p3'], $data['p5'],
            $data['p6'], $data['p7'], $data['p8'], $data['eng'],
            $data['qa_2'] ?? 0
        ]);

        $stockData = [
            'order_number' => $data['order_number'] ?? null,
            'specification' => $data['specification'] ?? null,
            'opening_stock' => $data['opening_stock'],
        ];
        unset($data['order_number'], $data['specification'], $data['opening_stock']);
        $tool = Tool::create($data);
        $tool->stockInfo()->create($stockData);

        $this->logCreate(
            'tools',
            $tool->id,
            $tool->tool_name . ' - ' . $tool->category . ' (' . $tool->tahun . ')',
            $tool->toArray()
        );

        if ($request->input('save_mode') === 'another') {
            return redirect()->route('tools.create', ['kategori' => $tool->category])
                ->with('success', 'Alat berhasil disimpan. Lanjutkan input alat berikutnya.');
        }

        return redirect()->route('tools.index')->with('success', 'Alat ukur berhasil ditambahkan!');
    }

    public function edit(Tool $tool)
    {
        $tool->load('stockInfo');
        $categories = Tool::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        return view('tools.edit', compact('tool', 'categories'));
    }

    public function update(Request $request, Tool $tool)
    {
        $dataLama = $tool->toArray();

        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'category_new' => 'required_if:category,__new|nullable|string|max:100',
            'order_number' => 'nullable|string|max:255',
            'specification' => 'nullable|string|max:255',
            'opening_stock' => 'required|integer|min:0|max:2147483647',
            'qa_1' => 'integer|min:0|max:2147483647', 'qa_1_2' => 'integer|min:0|max:2147483647', 'qa_inc' => 'integer|min:0|max:2147483647', 'qa_insp' => 'integer|min:0|max:2147483647',
            'ppic_1' => 'integer|min:0|max:2147483647', 'ppic_2' => 'integer|min:0|max:2147483647', 'line_scc' => 'integer|min:0|max:2147483647',
            'p1' => 'integer|min:0|max:2147483647', 'p2' => 'integer|min:0|max:2147483647', 'p3' => 'integer|min:0|max:2147483647', 'p5' => 'integer|min:0|max:2147483647',
            'p6' => 'integer|min:0|max:2147483647', 'p7' => 'integer|min:0|max:2147483647', 'p8' => 'integer|min:0|max:2147483647', 'eng' => 'integer|min:0|max:2147483647',
            'qa_2' => 'integer|min:0|max:2147483647',
        ]);

        if ($data['category'] === '__new') {
            $data['category'] = trim($data['category_new']);
        }
        unset($data['category_new']);
        $data['tool_name'] = strtoupper($data['tool_name']);

        $data['total'] = array_sum([
            $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
            $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
            $data['p1'], $data['p2'], $data['p3'], $data['p5'],
            $data['p6'], $data['p7'], $data['p8'], $data['eng'],
            $data['qa_2'] ?? 0
        ]);

        $stockData = [
            'order_number' => $data['order_number'] ?? null,
            'specification' => $data['specification'] ?? null,
            'opening_stock' => $data['opening_stock'],
        ];
        unset($data['order_number'], $data['specification'], $data['opening_stock']);
        $tool->update($data);
        ToolStock::updateOrCreate(['tool_id' => $tool->id], $stockData);

        $this->logUpdate(
            'tools',
            $tool->id,
            $tool->tool_name . ' - ' . $tool->category . ' (' . $tool->tahun . ')',
            $dataLama,
            $tool->fresh()->toArray()
        );

        return redirect()->route('tools.index')->with('success', 'Data alat ukur berhasil diperbarui!');
    }

    public function destroy(Tool $tool)
    {
        $dataLama = $tool->toArray();
        $toolId = $tool->id;
        $toolName = $tool->tool_name . ' - ' . $tool->category . ' (' . $tool->tahun . ')';

        $tool->delete();

        $this->logDelete('tools', $toolId, $toolName, $dataLama);

        return redirect()->route('tools.index')->with('success', 'Alat ukur berhasil dihapus!');
    }

    // ==========================================
    // IMPORT EXCEL
    // ==========================================
    public function importForm()
    {
        $categories = Tool::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        return view('tools.import', compact('categories'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
            'kategori' => 'required|string|max:100',
        ]);

        try {
            $import = new ToolsImport($request->tahun, $request->kategori);
            Excel::import($import, $request->file('file'));

            if ($import->getImportedCount() === 0) {
                return back()->withInput()->with('error', 'Header TOOL NAME atau NAMA ALAT tidak ditemukan. Pastikan file memiliki kolom nama alat; header boleh berada setelah judul laporan.');
            }

            $this->logActivity(
                'CREATE',
                'tools',
                null,
                'Import Excel untuk tahun ' . $request->tahun,
                null,
                ['tahun' => $request->tahun, 'kategori' => $request->kategori, 'file' => $request->file('file')->getClientOriginalName()]
            );

            return redirect()->route('tools.index', ['tahun' => $request->tahun, 'kategori' => $request->kategori])
                ->with('success', $import->getImportedCount() . ' baris data berhasil di-import untuk tahun ' . $request->tahun);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ==========================================
    // EXPORT
    // ==========================================
    public function exportCsv(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'kategori' => 'nullable|string|max:100',
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $kategori = $filters['kategori'] ?? 'Small Tool';
        $filename = 'daftar-induk-' . strtolower(str_replace(' ', '-', $kategori)) . '-' . $tahun . '.xlsx';

        $this->logExport('tools', 'Export Excel - ' . $kategori . ' tahun ' . $tahun);

        return Excel::download(new ToolsExport($tahun, $kategori), $filename);
    }

    /**
     * Export PDF dengan nomor dokumen otomatis
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'kategori' => 'nullable|string|max:100',
        ]);
        $tahun = $filters['tahun'] ?? date('Y');
        $kategori = $filters['kategori'] ?? 'Small Tool';

        $tools = Tool::where('tahun', $tahun)
            ->where('category', $kategori)
            ->orderBy('tool_name', 'asc')
            ->get();

        // ===== GENERATE NOMOR DOKUMEN =====
        $nomorDokumen = DokumenCounter::generateNomor('DIN', $tahun, 'QC');

        // ===== LOG ACTIVITY =====
        $this->logExport('tools', 'Export PDF Daftar Induk - ' . $kategori . ' - Nomor: ' . $nomorDokumen);

        // ===== GENERATE PDF =====
        $pdf = Pdf::loadView('tools.pdf', compact('tools', 'tahun', 'kategori', 'nomorDokumen'))
            ->setPaper('a4', 'landscape');

        DocumentPdfLayout::addPageNumbers($pdf);

        return $pdf->download('daftar-induk-' . strtolower(str_replace(' ', '-', $kategori)) . '-' . $tahun . '.pdf');
    }

    public function downloadTemplate()
    {
        $headers = [
            'tool_name', 'category',
            'qa_1', 'qa_1_2', 'qa_inc', 'qa_insp',
            'ppic_1', 'ppic_2', 'line_scc',
            'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng'
        ];

        $filename = 'template-import-daftar-induk.csv';

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers);
            fputcsv($file, [
                'CONTOH ALAT', 'Small Tool',
                1, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1, 1
            ]);
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
