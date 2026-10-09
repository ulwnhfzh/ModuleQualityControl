<?php

namespace App\Http\Controllers;

use App\Models\Internal;
use App\Models\Tool;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    // Tampilkan Tabel
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $schedules = Internal::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('internal.index', compact('schedules', 'tahun'));
    }

    // Form Tambah
    public function create()
    {
        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('internal.create', compact('tools'));
    }

    // Simpan Data
    public function store(Request $request)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);

        // Hitung QTY otomatis
        $data['qty'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        Internal::create($data);

        return redirect()->route('internal.index', ['tahun' => $data['tahun']])
            ->with('success', 'Jadwal kalibrasi internal berhasil ditambahkan!');
    }

    // Form Edit
    public function edit(Internal $internal)
    {
        return view('internal.edit', compact('internal'));
    }

    // Update Data
    public function update(Request $request, Internal $internal)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);

        $data['qty'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $internal->update($data);

        return redirect()->route('internal.index')->with('success', 'Jadwal kalibrasi internal berhasil diperbarui!');
    }

    // Hapus Data
    public function destroy(Internal $internal)
    {
        $internal->delete();
        return redirect()->route('internal.index')->with('success', 'Jadwal kalibrasi internal berhasil dihapus!');
    }
}