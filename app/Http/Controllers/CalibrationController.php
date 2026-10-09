<?php

namespace App\Http\Controllers;

use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\Tool;
use Illuminate\Http\Request;

class CalibrationController extends Controller
{
    // ==========================================
    // KALIBRASI EKSTERNAL - SMALL TOOL
    // ==========================================
    public function eksternalSmall(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $schedules = Eksternal::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('eksternal.small', compact('schedules', 'tahun'));
    }

    public function eksternalSmallCreate()
    {
        $tools = Tool::select('tool_name')
            ->distinct()
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('eksternal.create', compact('tools'));
    }

    public function eksternalSmallStore(Request $request)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['qty_set'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        Eksternal::create($data);

        return redirect()->route('eksternal.small', ['tahun' => $data['tahun']])
            ->with('success', 'Jadwal kalibrasi eksternal berhasil ditambahkan!');
    }

    public function eksternalSmallEdit(Eksternal $eksternal)
    {
        return view('eksternal.edit', compact('eksternal'));
    }

    public function eksternalSmallUpdate(Request $request, Eksternal $eksternal)
    {
        $data = $request->validate([
            'tool_name' => 'required|string|max:255',
            'tahun' => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['qty_set'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $eksternal->update($data);

        return redirect()->route('eksternal.small')->with('success', 'Jadwal kalibrasi eksternal berhasil diperbarui!');
    }

    public function eksternalSmallDestroy(Eksternal $eksternal)
    {
        $eksternal->delete();
        return redirect()->route('eksternal.small')->with('success', 'Jadwal kalibrasi eksternal berhasil dihapus!');
    }

    // ==========================================
    // KALIBRASI EKSTERNAL - MESIN
    // ==========================================
    public function mesin(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        $machines = EksternalMesin::where('tahun', $tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return view('mesin.index', compact('machines', 'tahun'));
    }

    public function mesinCreate()
    {
        return view('mesin.create');
    }

    public function mesinStore(Request $request)
    {
        $data = $request->validate([
            'tool_name'      => 'required|string|max:255',
            'machine_model'  => 'nullable|string|max:255',
            'code_number'    => 'nullable|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'maker'          => 'nullable|string|max:255',
            'no_machine'     => 'nullable|string|max:255',
            'retension'      => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'next_date'      => 'nullable|date',
            'tahun'          => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['total'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        EksternalMesin::create($data);

        return redirect()->route('mesin.index')
            ->with('success', 'Data mesin berhasil ditambahkan!');
    }

    public function mesinEdit(EksternalMesin $mesin)
    {
        return view('mesin.edit', compact('mesin'));
    }

    public function mesinUpdate(Request $request, EksternalMesin $mesin)
    {
        $data = $request->validate([
            'tool_name'      => 'required|string|max:255',
            'machine_model'  => 'nullable|string|max:255',
            'code_number'    => 'nullable|string|max:255',
            'serial_number'  => 'nullable|string|max:255',
            'maker'          => 'nullable|string|max:255',
            'no_machine'     => 'nullable|string|max:255',
            'retension'      => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'next_date'      => 'nullable|date',
            'tahun'          => 'required|digits:4',
            'jan' => 'integer|min:0', 'feb' => 'integer|min:0', 'mar' => 'integer|min:0', 'apr' => 'integer|min:0',
            'may' => 'integer|min:0', 'jun' => 'integer|min:0', 'jul' => 'integer|min:0', 'aug' => 'integer|min:0',
            'sep' => 'integer|min:0', 'oct' => 'integer|min:0', 'nov' => 'integer|min:0', 'dec' => 'integer|min:0',
        ]);

        $data['tool_name'] = strtoupper($data['tool_name']);
        $data['total'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'], $data['may'], $data['jun'],
            $data['jul'], $data['aug'], $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        $mesin->update($data);

        return redirect()->route('mesin.index')
            ->with('success', 'Data mesin berhasil diperbarui!');
    }

    public function mesinDestroy(EksternalMesin $mesin)
    {
        $mesin->delete();

        return redirect()->route('mesin.index')
            ->with('success', 'Data mesin berhasil dihapus!');
    }
}