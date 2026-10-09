<?php

namespace App\Http\Controllers;

use App\Models\Verifikasi;
use App\Models\Tool;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    // Tampilkan Tabel
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $mode = $request->input('mode', 'bulanan'); // bulanan / harian

        if ($mode == 'harian') {
            $schedules = Verifikasi::where('tahun', $tahun)
                ->where('tipe', 'harian')
                ->orderBy('tool_name', 'asc')
                ->get();
        } else {
            $schedules = Verifikasi::where('tahun', $tahun)
                ->where('tipe', 'bulanan')
                ->orderBy('tool_name', 'asc')
                ->get();
        }

        return view('verifikasi.index', compact('schedules', 'tahun', 'mode'));
    }

    // Form Tambah
    public function create(Request $request)
    {
        $mode = $request->input('mode', 'bulanan');
        
        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('verifikasi.create', compact('tools', 'mode'));
    }

    // Simpan Data
    public function store(Request $request)
    {
        $mode = $request->input('tipe', 'bulanan');
        $data['tool_name'] = strtoupper($request->tool_name);
        $data['tahun'] = $request->tahun;
        $data['tipe'] = $mode;

        if ($mode == 'harian') {
            // Validasi mode harian
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'tanggal' => 'required|date',
                'qa_1' => 'integer|min:0', 'qa_1_2' => 'integer|min:0', 'qa_inc' => 'integer|min:0', 'qa_insp' => 'integer|min:0',
                'ppic_1' => 'integer|min:0', 'ppic_2' => 'integer|min:0', 'line_scc' => 'integer|min:0',
                'p1' => 'integer|min:0', 'p2' => 'integer|min:0', 'p3' => 'integer|min:0', 'p5' => 'integer|min:0',
                'p6' => 'integer|min:0', 'p7' => 'integer|min:0', 'p8' => 'integer|min:0', 'eng' => 'integer|min:0',
            ]);

            $data['tanggal'] = $request->tanggal;
            $data['qa_1'] = $request->qa_1 ?? 0;
            $data['qa_1_2'] = $request->qa_1_2 ?? 0;
            $data['qa_inc'] = $request->qa_inc ?? 0;
            $data['qa_insp'] = $request->qa_insp ?? 0;
            $data['ppic_1'] = $request->ppic_1 ?? 0;
            $data['ppic_2'] = $request->ppic_2 ?? 0;
            $data['line_scc'] = $request->line_scc ?? 0;
            $data['p1'] = $request->p1 ?? 0;
            $data['p2'] = $request->p2 ?? 0;
            $data['p3'] = $request->p3 ?? 0;
            $data['p5'] = $request->p5 ?? 0;
            $data['p6'] = $request->p6 ?? 0;
            $data['p7'] = $request->p7 ?? 0;
            $data['p8'] = $request->p8 ?? 0;
            $data['eng'] = $request->eng ?? 0;
            $data['total'] = array_sum([
                $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
                $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
                $data['p1'], $data['p2'], $data['p3'], $data['p5'],
                $data['p6'], $data['p7'], $data['p8'], $data['eng']
            ]);
        } else {
            // Validasi mode bulanan
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
                'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
                'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
            ]);

            $data['jan'] = $request->jan ?? 0;
            $data['feb'] = $request->feb ?? 0;
            $data['mar'] = $request->mar ?? 0;
            $data['apr'] = $request->apr ?? 0;
            $data['may'] = $request->may ?? 0;
            $data['jun'] = $request->jun ?? 0;
            $data['jul'] = $request->jul ?? 0;
            $data['aug'] = $request->aug ?? 0;
            $data['sep'] = $request->sep ?? 0;
            $data['oct'] = $request->oct ?? 0;
            $data['nov'] = $request->nov ?? 0;
            $data['dec'] = $request->dec ?? 0;
            $data['total'] = array_sum([
                $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
                $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
            ]);
        }

        Verifikasi::create($data);

        return redirect()->route('verifikasi.index', ['tahun' => $data['tahun'], 'mode' => $mode])
            ->with('success', 'Jadwal verifikasi berhasil ditambahkan!');
    }

    public function edit(Verifikasi $verifikasi)
    {
        return view('verifikasi.edit', compact('verifikasi'));
    }

    public function update(Request $request, Verifikasi $verifikasi)
    {
        $data['tool_name'] = strtoupper($request->tool_name);
        $data['tahun'] = $request->tahun;

        if ($verifikasi->tipe == 'harian') {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'tanggal' => 'required|date',
                'qa_1' => 'integer|min:0', 'qa_1_2' => 'integer|min:0', 'qa_inc' => 'integer|min:0', 'qa_insp' => 'integer|min:0',
                'ppic_1' => 'integer|min:0', 'ppic_2' => 'integer|min:0', 'line_scc' => 'integer|min:0',
                'p1' => 'integer|min:0', 'p2' => 'integer|min:0', 'p3' => 'integer|min:0', 'p5' => 'integer|min:0',
                'p6' => 'integer|min:0', 'p7' => 'integer|min:0', 'p8' => 'integer|min:0', 'eng' => 'integer|min:0',
            ]);

            $data['tanggal'] = $request->tanggal;
            $data['qa_1'] = $request->qa_1 ?? 0;
            $data['qa_1_2'] = $request->qa_1_2 ?? 0;
            $data['qa_inc'] = $request->qa_inc ?? 0;
            $data['qa_insp'] = $request->qa_insp ?? 0;
            $data['ppic_1'] = $request->ppic_1 ?? 0;
            $data['ppic_2'] = $request->ppic_2 ?? 0;
            $data['line_scc'] = $request->line_scc ?? 0;
            $data['p1'] = $request->p1 ?? 0;
            $data['p2'] = $request->p2 ?? 0;
            $data['p3'] = $request->p3 ?? 0;
            $data['p5'] = $request->p5 ?? 0;
            $data['p6'] = $request->p6 ?? 0;
            $data['p7'] = $request->p7 ?? 0;
            $data['p8'] = $request->p8 ?? 0;
            $data['eng'] = $request->eng ?? 0;
            $data['total'] = array_sum([
                $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
                $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
                $data['p1'], $data['p2'], $data['p3'], $data['p5'],
                $data['p6'], $data['p7'], $data['p8'], $data['eng']
            ]);
        } else {
            $request->validate([
                'tool_name' => 'required|string|max:255',
                'tahun' => 'required|digits:4',
                'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
                'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
                'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
            ]);

            $data['jan'] = $request->jan ?? 0;
            $data['feb'] = $request->feb ?? 0;
            $data['mar'] = $request->mar ?? 0;
            $data['apr'] = $request->apr ?? 0;
            $data['may'] = $request->may ?? 0;
            $data['jun'] = $request->jun ?? 0;
            $data['jul'] = $request->jul ?? 0;
            $data['aug'] = $request->aug ?? 0;
            $data['sep'] = $request->sep ?? 0;
            $data['oct'] = $request->oct ?? 0;
            $data['nov'] = $request->nov ?? 0;
            $data['dec'] = $request->dec ?? 0;
            $data['total'] = array_sum([
                $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
                $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
            ]);
        }

        $verifikasi->update($data);

        return redirect()->route('verifikasi.index', ['mode' => $verifikasi->tipe])
            ->with('success', 'Jadwal verifikasi berhasil diperbarui!');
    }

    public function destroy(Verifikasi $verifikasi)
    {
        $verifikasi->delete();
        return redirect()->route('verifikasi.index', ['mode' => $verifikasi->tipe])
            ->with('success', 'Jadwal verifikasi berhasil dihapus!');
    }
}