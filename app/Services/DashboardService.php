<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ExternalMachine;
use App\Models\ListUpNg;
use App\Models\Setting;
use App\Models\ToolDefect;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    private const LATE_LIMIT_DAYS = 90;
    private const BULAN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /** Data utama dashboard. Jadwal dan mesin memakai logika yang sama dengan email dan halaman Notification. */
    public function build(int $tahun): array
    {
        $today = Carbon::today()->startOfDay();
        $jadwal = $this->jadwal($today);
        $mesin = $this->mesin($today);

        $tindakan = $jadwal->concat($mesin)
            ->filter(fn (array $r) => $r['sisa'] >= -self::LATE_LIMIT_DAYS && $r['sisa'] <= $r['batas'])
            ->sortBy('sisa')
            ->take(5)
            ->values()
            ->all();

        $ngBase = ListUpNg::where('tahun', $tahun);

        return [
            'cards' => [
                'jadwal_terlambat' => $jadwal->filter(fn (array $r) => $r['sisa'] < 0 && $r['sisa'] >= -self::LATE_LIMIT_DAYS)->count(),
                'jadwal_h5' => $jadwal->filter(fn (array $r) => $r['sisa'] >= 0)->count(),
                'jadwal_lewat_90' => $jadwal->filter(fn (array $r) => $r['sisa'] < -self::LATE_LIMIT_DAYS)->count(),
                'mesin_terlambat' => $mesin->filter(fn (array $r) => $r['sisa'] < 0)->count(),
                'mesin_cepat' => $mesin->filter(fn (array $r) => $r['sisa'] >= 0)->count(),
                'ng_total' => (clone $ngBase)->count(),
                'ng_terbuka' => (clone $ngBase)->where('status_order', '<>', 'CLOSED')->count(),
            ],
            'tindakan' => $tindakan,
            'pareto' => $this->pareto($tahun, $jadwal, $mesin),
        ];
    }

    /** Log aktivitas terbaru (hanya untuk admin). */
    public function aktivitas(int $limit = 5): array
    {
        return ActivityLog::latest('id')
            ->whereNotIn('aksi', ['LOGIN', 'LOGOUT'])
            ->limit($limit)
            ->get(['user_name', 'aksi', 'jenis_data', 'keterangan', 'created_at'])
            ->map(fn (ActivityLog $a) => [
                'user' => $a->user_name ?? '-',
                'aksi' => $a->aksi,
                'jenis' => $a->jenis_data ?? '-',
                'keterangan' => $a->keterangan ?? '-',
                'waktu' => $a->created_at?->format('d M Y H:i') ?? '-',
            ])
            ->all();
    }

    /**
     * Enam sumber data untuk grafik Pareto. Setiap sumber dikirim sekaligus ke browser,
     * sehingga dropdown bisa berganti grafik tanpa memuat ulang halaman.
     */
    public function pareto(int $tahun, Collection $jadwal, Collection $mesin): array
    {
        // Jenis kerusakan dari Rekap Data NG
        $defect = [];
        foreach (ToolDefect::where('tahun', $tahun)->get() as $row) {
            foreach (ToolDefect::COUNT_FIELDS as $field) {
                $value = $field === 'erorr'
                    ? (int) ($row->getAttribute('error') ?? $row->getAttribute('erorr') ?? 0)
                    : (int) ($row->getAttribute($field) ?? 0);
                $label = strtoupper(str_replace('_', ' ', $field));
                $defect[$label] = ($defect[$label] ?? 0) + $value;
            }
        }

        // Bulan dari order NG (List Up NG). Dihitung di PHP agar tidak bergantung pada fungsi SQL tertentu.
        $bulan = [];
        foreach (ListUpNg::where('tahun', $tahun)->whereNotNull('ng_date')->pluck('ng_date') as $tanggal) {
            $label = self::BULAN[(int) Carbon::parse($tanggal)->format('n')];
            $bulan[$label] = ($bulan[$label] ?? 0) + 1;
        }

        // Jadwal dan mesin yang belum selesai, dikelompokkan per jenis
        $belumSelesai = $jadwal->concat($mesin)
            ->filter(fn (array $r) => $r['sisa'] >= -self::LATE_LIMIT_DAYS && $r['sisa'] <= $r['batas'])
            ->groupBy('jenis')
            ->map(fn (Collection $items) => $items->count())
            ->all();

        return [
            'defect' => $this->paretoBlock('Jenis kerusakan NG', 'Frekuensi tiap jenis kerusakan dari Rekap Data NG.', $defect, 12),
            'divisi' => $this->paretoBlock('Kontribusi NG per divisi', 'Jumlah order NG (List Up NG) per divisi.', $this->countBy($tahun, 'division'), 12),
            'alat' => $this->paretoBlock('Alat penyumbang NG', 'Jumlah order NG per nama alat, 15 teratas.', $this->countBy($tahun, 'tool_name'), 15),
            'kategori' => $this->paretoBlock('Kategori NG', 'Jumlah order NG per kategori.', $this->countBy($tahun, 'category_ng'), 12),
            'bulan' => $this->paretoBlock('Order NG per bulan', 'Jumlah order NG setiap bulan.', $bulan, 12),
            'belum_selesai' => $this->paretoBlock('Jadwal belum selesai per jenis', 'Verifikasi, kalibrasi, dan mesin yang terlambat atau jatuh tempo dalam jendela pengingat.', $belumSelesai, 12),
        ];
    }

    private function countBy(int $tahun, string $column): array
    {
        return ListUpNg::where('tahun', $tahun)
            ->selectRaw("{$column} as label, COUNT(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn ($r) => [(string) $r->label => (int) $r->total])
            ->all();
    }

    /** Hitung data Pareto: diurutkan menurun, lalu persentase kumulatif terhadap total semua data. */
    private function paretoBlock(string $judul, string $deskripsi, array $data, int $limit): array
    {
        $semua = collect($data)
            ->filter(fn ($value, $label) => trim((string) $label) !== '' && (int) $value > 0)
            ->sortDesc();

        $total = (int) $semua->sum();
        $teratas = $semua->take($limit);

        $labels = [];
        $values = [];
        $kumulatif = [];
        $jalan = 0;

        foreach ($teratas as $label => $value) {
            $jalan += (int) $value;
            $labels[] = (string) $label;
            $values[] = (int) $value;
            $kumulatif[] = $total ? round($jalan / $total * 100, 1) : 0;
        }

        return [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'labels' => $labels,
            'values' => $values,
            'kumulatif' => $kumulatif,
            'total' => $total,
        ];
    }

    private function jadwal(Carbon $today): Collection
    {
        return app(ReminderService::class)->activeScheduleItems($today)
            ->map(fn ($i) => $this->row(
                $i->category,
                $i->tool_name,
                $i->shop ? 'Shop: ' . $i->shop : null,
                $i->next_date,
                (int) $i->sisa_hari,
                5
            ))
            ->values();
    }

    private function mesin(Carbon $today): Collection
    {
        $selesai = app(ReminderService::class)->doneMachineIds($today)->flip();
        $batas = Setting::warningStandar();

        return ExternalMachine::whereNotNull('next_date')
            ->whereDate('next_date', '<=', $today->copy()->addDays($batas)->toDateString())
            ->orderBy('next_date')
            ->get()
            ->reject(fn (ExternalMachine $m) => $selesai->has($m->id))
            ->map(function (ExternalMachine $m) use ($today) {
                $due = Carbon::parse($m->next_date)->startOfDay();

                return $this->row(
                    'Mesin eksternal',
                    $m->tool_name,
                    null,
                    $due->toDateString(),
                    (int) $today->diffInDays($due, false),
                    Setting::warningCepat()
                );
            })
            ->values();
    }

    private function row(string $jenis, string $nama, ?string $detail, string $due, int $sisa, int $batas): array
    {
        return [
            'jenis' => $jenis,
            'nama' => $nama,
            'detail' => $detail,
            'jatuh_tempo' => $this->tanggal($due),
            'sisa' => $sisa,
            'batas' => $batas,
            'keterangan' => $sisa < 0
                ? 'Terlambat ' . abs($sisa) . ' hari'
                : ($sisa === 0 ? 'Hari ini' : $sisa . ' hari lagi'),
        ];
    }

    private function tanggal(string $ymd): string
    {
        $d = Carbon::parse($ymd);

        return $d->day . ' ' . self::BULAN[$d->month] . ' ' . $d->year;
    }
}