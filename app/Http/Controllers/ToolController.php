<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;
use App\Imports\ToolsImport;
use Maatwebsite\Excel\Facades\Excel;

class ToolController extends Controller
{
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

        Tool::create($data);

        return redirect()->route('tools.index')->with('success', 'Alat ukur berhasil ditambahkan!');
    }

    public function edit(Tool $tool)
    {
        return view('tools.edit', compact('tool'));
    }

    public function update(Request $request, Tool $tool)
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

        $data['total'] = array_sum([
            $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
            $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
            $data['p1'], $data['p2'], $data['p3'], $data['p5'],
            $data['p6'], $data['p7'], $data['p8'], $data['eng'],
            $data['qa_2'] ?? 0
        ]);

        $tool->update($data);

        return redirect()->route('tools.index')->with('success', 'Data alat ukur berhasil diperbarui!');
    }

    public function destroy(Tool $tool)
    {
        $tool->delete();
        return redirect()->route('tools.index')->with('success', 'Alat ukur berhasil dihapus!');
    }

    // ==========================================
    // FITUR IMPORT EXCEL
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
            return redirect()->route('tools.index', ['tahun' => $request->tahun])
                ->with('success', 'Data berhasil di-import untuk tahun ' . $request->tahun);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}