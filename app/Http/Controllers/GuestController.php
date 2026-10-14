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
        $tab    = $request->input('tab', 'semua');
        $search = trim($request->input('search', ''));
        $tahun  = $request->input('tahun', date('Y'));

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
            $induk = $q->orderBy('tool_name')->get();

            $data['induk'] = $induk;
            $data['induk_total'] = [
                'qa_1' => $induk->sum('qa_1'),
                'qa_1_2' => $induk->sum('qa_1_2'),
                'qa_2' => $induk->sum('qa_2'),
                'qa_inc' => $induk->sum('qa_inc'),
                'qa_insp' => $induk->sum('qa_insp'),
                'ppic_1' => $induk->sum('ppic_1'),
                'ppic_2' => $induk->sum('ppic_2'),
                'line_scc' => $induk->sum('line_scc'),
                'p1' => $induk->sum('p1'),
                'p2' => $induk->sum('p2'),
                'p3' => $induk->sum('p3'),
                'p5' => $induk->sum('p5'),
                'p6' => $induk->sum('p6'),
                'p7' => $induk->sum('p7'),
                'p8' => $induk->sum('p8'),
                'eng' => $induk->sum('eng'),
                'total' => $induk->sum('total'),
            ];
        }

        // ==== Tab: Verifikasi ====
        if ($tab === 'semua' || $tab === 'verifikasi') {
            $q = Verifikasi::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name', 'tipe', 'tanggal']);
            $verif = $q->orderBy('tool_name')->get();

            $data['verifikasi'] = $verif;
            $data['verifikasi_total'] = [
                'total' => $verif->sum('total'),
            ];
        }

        // ==== Tab: Internal ====
        if ($tab === 'semua' || $tab === 'internal') {
            $q = Internal::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name']);
            $internal = $q->orderBy('tool_name')->get();

            $data['internal'] = $internal;
            $data['internal_total'] = [
                'jan' => $internal->sum('jan'),
                'feb' => $internal->sum('feb'),
                'mar' => $internal->sum('mar'),
                'apr' => $internal->sum('apr'),
                'may' => $internal->sum('may'),
                'jun' => $internal->sum('jun'),
                'jul' => $internal->sum('jul'),
                'aug' => $internal->sum('aug'),
                'sep' => $internal->sum('sep'),
                'oct' => $internal->sum('oct'),
                'nov' => $internal->sum('nov'),
                'dec' => $internal->sum('dec'),
                'qty' => $internal->sum('qty'),
            ];
        }

        // ==== Tab: Eksternal Small ====
        if ($tab === 'semua' || $tab === 'eksternal') {
            $q = Eksternal::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name']);
            $eksternal = $q->orderBy('tool_name')->get();

            $data['eksternal'] = $eksternal;
            $data['eksternal_total'] = [
                'jan' => $eksternal->sum('jan'),
                'feb' => $eksternal->sum('feb'),
                'mar' => $eksternal->sum('mar'),
                'apr' => $eksternal->sum('apr'),
                'may' => $eksternal->sum('may'),
                'jun' => $eksternal->sum('jun'),
                'jul' => $eksternal->sum('jul'),
                'aug' => $eksternal->sum('aug'),
                'sep' => $eksternal->sum('sep'),
                'oct' => $eksternal->sum('oct'),
                'nov' => $eksternal->sum('nov'),
                'dec' => $eksternal->sum('dec'),
                'qty_set' => $eksternal->sum('qty_set'),
            ];
        }

        // ==== Tab: Mesin ====
        if ($tab === 'semua' || $tab === 'mesin') {
            $q = EksternalMesin::where('tahun', $tahun);
            $q = $applySearch($q, ['tool_name', 'machine_model', 'maker', 'serial_number', 'code_number']);
            $mesin = $q->orderBy('tool_name')->get();

            $data['mesin'] = $mesin;
            $data['mesin_total'] = [
                'jan' => $mesin->sum('jan'),
                'feb' => $mesin->sum('feb'),
                'mar' => $mesin->sum('mar'),
                'apr' => $mesin->sum('apr'),
                'may' => $mesin->sum('may'),
                'jun' => $mesin->sum('jun'),
                'jul' => $mesin->sum('jul'),
                'aug' => $mesin->sum('aug'),
                'sep' => $mesin->sum('sep'),
                'oct' => $mesin->sum('oct'),
                'nov' => $mesin->sum('nov'),
                'dec' => $mesin->sum('dec'),
                'qty_set' => $mesin->sum('qty_set'),
            ];
        }

        return view('guest.index', compact('tab', 'search', 'tahun', 'data'));
    }
}