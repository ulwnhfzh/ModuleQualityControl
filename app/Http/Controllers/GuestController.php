<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verifikasi;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->input('tab', 'semua'); // semua | induk | verifikasi | internal | eksternal | mesin
        $search = trim($request->input('search', ''));
        $tahun  = $request->input('tahun', date('Y'));

        // Data untuk semua tab
        $data = [];

        // Helper search
        $applySearch = function ($query, $columns) use ($search) {
            if ($search === '') return $query;
            return $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'LIKE', "%{$search}%");
                }
            });
        };

        // ==== Tab: Induk ====
        if ($tab === 'semua' || $tab === 'induk') {
            $q = Tool::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name', 'category', 'machine_model', 'maker', 'serial_number']);
            $data['induk'] = $q->orderBy('tool_name')->get();
        }

        // ==== Tab: Verifikasi ====
        if ($tab === 'semua' || $tab === 'verifikasi') {
            $q = Verifikasi::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name', 'tipe', 'tanggal']);
            $data['verifikasi'] = $q->orderBy('tool_name')->get();
        }

        // ==== Tab: Internal ====
        if ($tab === 'semua' || $tab === 'internal') {
            $q = Internal::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name']);
            $data['internal'] = $q->orderBy('tool_name')->get();
        }

        // ==== Tab: Eksternal Small ====
        if ($tab === 'semua' || $tab === 'eksternal') {
            $q = Eksternal::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name']);
            $data['eksternal'] = $q->orderBy('tool_name')->get();
        }

        // ==== Tab: Mesin ====
        if ($tab === 'semua' || $tab === 'mesin') {
            $q = EksternalMesin::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name', 'machine_model', 'maker', 'serial_number', 'code_number']);
            $data['mesin'] = $q->orderBy('tool_name')->get();
        }

        return view('guest.index', compact('tab', 'search', 'tahun', 'data'));
    }
}