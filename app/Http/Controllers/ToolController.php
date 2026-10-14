<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\DokumenCounter;
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
        $tahun = $request->input('tahun', date('Y'));
        $kategori = $request->input('kategori', 'Small Tool');

        $query = Tool::query();

        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        if ($kategori) {
            $query->where('category', $kategori);
        }

        $tools = $query->orderBy('tool_name', 'asc')->get();
        return view('tools.index', compact('tools', 'tahun', 'kategori'));
    }

    public function create()
    {
        return view('tools.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'category' => 'required|string',
            'qa_1' => 'integer|min:0', 'qa_1_2' => 'integer|min:0', 'qa_inc' => 'integer|min:0', 'qa_insp' => 'integer|min:0',
            'ppic_1' => 'integer|min:0', 'ppic_2' => 'integer|min:0', 'line_scc' => 'integer|min:0',
            'p1' => 'integer|min:0', 'p2' => 'integer|min:0', 'p3' => 'integer|min:0', 'p5' => 'integer|min:0',
            'p6' => 'integer|min:0', 'p7' => 'integer|min:0', 'p8' => 'integer|min:0', 'eng' => 'integer|min:0',
            'qa_2' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['tahun'] = date('Y');

        $data['total'] = array_sum([
            $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
            $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
            $data['p1'], $data['p2'], $data['p3'], $data['p5'],
            $data['p6'], $data['p7'], $data['p8'], $data['eng'],
            $data['qa_2'] ?? 0
        ]);

        $tool = Tool::create($data);

        $this->logCreate(
            'tools',
            $tool->id,
            $tool->tool_name . ' - ' . $tool->category . ' (' . $tool->tahun . ')',
            $tool->toArray()
        );

        return redirect()->route('tools.index')->with('success', 'Alat ukur berhasil ditambahkan!');
    }

    public function edit(Tool $tool)
    {
        return view('tools.edit', compact('tool'));
    }

    public function update(Request $request, Tool $tool)
    {
        $dataLama = $tool->toArray();

        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'category' => 'required|string',
            'qa_1' => 'integer|min:0', 'qa_1_2' => 'integer|min:0', 'qa_inc' => 'integer|min:0', 'qa_insp' => 'integer|min:0',
            'ppic_1' => 'integer|min:0', 'ppic_2' => 'integer|min:0', 'line_scc' => 'integer|min:0',
            'p1' => 'integer|min:0', 'p2' => 'integer|min:0', 'p3' => 'integer|min:0', 'p5' => 'integer|min:0',
            'p6' => 'integer|min:0', 'p7' => 'integer|min:0', 'p8' => 'integer|min:0', 'eng' => 'integer|min:0',
            'qa_2' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);

        $data['total'] = array_sum([
            $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
            $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
            $data['p1'], $data['p2'], $data['p3'], $data['p5'],
            $data['p6'], $data['p7'], $data['p8'], $data['eng'],
            $data['qa_2'] ?? 0
        ]);

        $tool->update($data);

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
        return view('tools.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'tahun' => 'required|digits:4',
        ]);

        try {
            Excel::import(new ToolsImport($request->tahun), $request->file('file'));

            $this->logActivity(
                'CREATE',
                'tools',
                null,
                'Import Excel untuk tahun ' . $request->tahun,
                null,
                ['tahun' => $request->tahun, 'file' => $request->file('file')->getClientOriginalName()]
            );

            return redirect()->route('tools.index', ['tahun' => $request->tahun])
                ->with('success', 'Data berhasil di-import untuk tahun ' . $request->tahun);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ==========================================
    // EXPORT
    // ==========================================
    public function exportCsv(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $kategori = $request->input('kategori', 'Small Tool');
        $filename = 'daftar-induk-' . strtolower(str_replace(' ', '-', $kategori)) . '-' . $tahun . '.xlsx';

        $this->logExport('tools', 'Export Excel - ' . $kategori . ' tahun ' . $tahun);

        return Excel::download(new ToolsExport($tahun, $kategori), $filename);
    }

    /**
     * Export PDF dengan nomor dokumen otomatis
     */
    public function exportPdf(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $kategori = $request->input('kategori', 'Small Tool');

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