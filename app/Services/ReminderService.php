<?php

namespace App\Services;

use App\Exports\OverdueOver90Export;
use App\Models\ExternalMachine;
use App\Models\Setting;
use App\Models\User;
use App\Models\Verification;
use App\Notifications\DailyReminderNotification;
use App\Notifications\WeeklyOverdueNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class ReminderService
{
    /** Verifikasi/kalibrasi dianggap sudah diisi jika tanggalnya tidak lebih dari 30 hari sebelum jatuh tempo. */
    public const DONE_WINDOW_DAYS = 30;

    /** Jadwal yang terlambat lebih dari ini tidak masuk email harian, masuk rekap mingguan. */
    public const LATE_LIMIT_DAYS = 90;

    /** Maksimal baris per bagian di email harian. */
    public const LIST_LIMIT = 20;

    /** Item yang baru dianggap selesai ditampilkan untuk diperiksa selama sekian hari. */
    public const NEW_DONE_DAYS = 7;

    private const SUMMARY_KEY = 'summary';
    private const WEEKLY_KEY = 'summary_weekly';
    private const DONE_LEVEL = 'done';
    private const DONE_SEED_LEVEL = 'done_seed';

    private const BULAN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    // =====================================================================
    // DIPAKAI JUGA OLEH HALAMAN NOTIFICATION (supaya angkanya sama dengan email)
    // =====================================================================

    /** Jadwal verifikasi/kalibrasi yang masih aktif (belum diisi). */
    public function activeScheduleItems(Carbon $today): Collection
    {
        return app(ScheduleExpiryNotifier::class)->dueItems($today)
            ->reject(fn (object $i) => $this->isDoneDate($i->actual_date, $i->next_date))
            ->values();
    }

    /** ID mesin yang sudah diverifikasi untuk periodenya. */
    public function doneMachineIds(Carbon $today): Collection
    {
        $verified = $this->verifiedDatesByTool($today->year);

        return ExternalMachine::whereNotNull('next_date')
            ->get()
            ->filter(fn (ExternalMachine $m) => $this->isDoneDate(
                $verified[$this->toolKey($m->tool_name)] ?? null,
                Carbon::parse($m->next_date)->toDateString()
            ))
            ->pluck('id')
            ->values();
    }

    // =====================================================================
    // EMAIL HARIAN
    // =====================================================================

    public function runDaily(Carbon $today, bool $dryRun = false): array
    {
        $today = $today->copy()->startOfDay();
        $summary = ['items' => 0, 'sent' => 0, 'done' => 0, 'already_today' => 0, 'no_admin' => false];

        $recipients = $this->recipients();
        if (empty($recipients)) {
            $summary['no_admin'] = true;

            return $summary;
        }

        if ($this->logExists(self::SUMMARY_KEY, $today)) {
            $summary['already_today'] = 1;

            return $summary;
        }

        $rows = $this->allRows($today);
        $summary['items'] = $rows->count();
        $summary['done'] = $rows->where('done', true)->count();

        // Hanya item yang belum selesai yang masuk email
        $active = $rows->where('done', false);
        $jadwal = $active->where('type', 'jadwal');
        $mesin = $active->where('type', 'mesin');

        $mendekati = $jadwal
            ->filter(fn (object $r) => $r->sisa_hari >= 0 && $r->sisa_hari <= $this->jadwalWindow())
            ->sortBy('sisa_hari')->values();

        $terlambat = $jadwal
            ->filter(fn (object $r) => $r->sisa_hari < 0 && $r->sisa_hari >= -self::LATE_LIMIT_DAYS)
            ->sortBy('sisa_hari')->values();

        $lebih90 = $jadwal
            ->filter(fn (object $r) => $r->sisa_hari < -self::LATE_LIMIT_DAYS)
            ->count();

        $mesinTerlambat = $mesin->filter(fn (object $r) => $r->sisa_hari < 0)->sortBy('sisa_hari')->values();
        $mesinCepat = $mesin
            ->filter(fn (object $r) => $r->sisa_hari >= 0 && $r->sisa_hari <= $this->mesinCepat())
            ->sortBy('sisa_hari')->values();
        $mesinLama = $mesin->filter(fn (object $r) => $r->sisa_hari > $this->mesinCepat())->count();

        // Catat dulu yang baru selesai hari ini, baru ambil daftar "baru selesai"
        $seed = ! DB::table('reminder_logs')->where('source_key', self::SUMMARY_KEY)->exists();
        if (! $dryRun) {
            $this->recordDone($rows, $today, $seed);
        }
        $selesaiBaru = $seed ? collect() : $this->newlyDone($rows, $today);

        $counts = [
            'mendekati' => $mendekati->count(),
            'terlambat' => $terlambat->count(),
            'lebih90' => $lebih90,
            'selesai_baru' => $selesaiBaru->count(),
            'mesin_terlambat' => $mesinTerlambat->count(),
            'mesin_jatuh_tempo' => $mesinCepat->count(),
            'mesin_lama' => $mesinLama,
            'verif_konfirmasi' => $this->verifikasiBelumKonfirmasi($today->year),
        ];

        $summary['sent'] = $counts['mendekati'] + $counts['terlambat'] + $counts['selesai_baru']
            + $counts['mesin_terlambat'] + $counts['mesin_jatuh_tempo'];

        if ($dryRun) {
            return $summary;
        }

        $sections = [
            'mendekati' => $mendekati->take(self::LIST_LIMIT)->map(fn ($r) => $this->rowJadwal($r))->values()->all(),
            'terlambat' => $terlambat->take(self::LIST_LIMIT)->map(fn ($r) => $this->rowJadwal($r))->values()->all(),
            'selesai_baru' => $selesaiBaru->take(self::LIST_LIMIT)->map(fn ($r) => $this->rowJadwal($r))->values()->all(),
            'mesin_terlambat' => $mesinTerlambat->take(self::LIST_LIMIT)->map(fn ($r) => $this->rowJadwal($r))->values()->all(),
            'mesin_jatuh_tempo' => $mesinCepat->take(self::LIST_LIMIT)->map(fn ($r) => $this->rowJadwal($r))->values()->all(),
        ];

        $notification = new DailyReminderNotification($today, $counts, $sections, url('/notifikasi'));
        $ok = $this->sendToAll($recipients, $notification);

        if ($ok > 0) {
            $this->logRun(self::SUMMARY_KEY, $today, $ok);
        }

        return $summary;
    }

    // =====================================================================
    // REKAP MINGGUAN (jadwal terlambat lebih dari 90 hari, dengan lampiran Excel)
    // =====================================================================

    public function runWeekly(Carbon $today, bool $dryRun = false): array
    {
        $today = $today->copy()->startOfDay();
        $summary = ['count' => 0, 'sent' => 0, 'already_today' => false, 'no_admin' => false];

        $recipients = $this->recipients();
        if (empty($recipients)) {
            $summary['no_admin'] = true;

            return $summary;
        }

        if ($this->logExists(self::WEEKLY_KEY, $today)) {
            $summary['already_today'] = true;

            return $summary;
        }

        $rows = $this->scheduleRows($today)
            ->where('done', false)
            ->filter(fn (object $r) => $r->sisa_hari < -self::LATE_LIMIT_DAYS)
            ->sortBy('sisa_hari')
            ->values();

        $summary['count'] = $rows->count();
        if ($dryRun) {
            return $summary;
        }

        $excel = null;
        if ($rows->isNotEmpty()) {
            $data = $rows->map(fn (object $r) => [
                $r->category,
                $r->tool_name,
                $r->detail ?? '',
                $this->tanggal($r->due_date),
                abs($r->sisa_hari),
            ])->values()->all();

            $excel = Excel::raw(new OverdueOver90Export($data), ExcelWriter::XLSX);
        }

        $notification = new WeeklyOverdueNotification($today, $rows->count(), $excel, url('/notifikasi'));
        $ok = $this->sendToAll($recipients, $notification);

        $summary['sent'] = $ok;
        if ($ok > 0) {
            $this->logRun(self::WEEKLY_KEY, $today, $ok);
        }

        return $summary;
    }

    // =====================================================================
    // DATA
    // =====================================================================

    private function allRows(Carbon $today): Collection
    {
        $mesin = collect($this->machineRows($today)->all());
        $jadwal = collect($this->scheduleRows($today)->all());

        return $mesin->merge($jadwal)->values();
    }

    private function machineRows(Carbon $today): Collection
    {
        $verified = $this->verifiedDatesByTool($today->year);

        return ExternalMachine::whereNotNull('next_date')
            ->whereDate('next_date', '<=', $today->copy()->addDays($this->mesinLama())->toDateString())
            ->orderBy('id')
            ->get()
            ->toBase()
            ->map(function (ExternalMachine $m) use ($today, $verified) {
                $due = Carbon::parse($m->next_date)->startOfDay();
                $actual = $verified[$this->toolKey($m->tool_name)] ?? null;

                return (object) [
                    'type' => 'mesin',
                    'source_key' => "mesin:{$m->id}",
                    'category' => 'Mesin eksternal',
                    'tool_name' => $m->tool_name,
                    'detail' => $this->joinDetail([$m->maker, $m->machine_model]),
                    'due_date' => $due->toDateString(),
                    'sisa_hari' => (int) $today->diffInDays($due, false),
                    'actual_date' => $actual,
                    'done' => $this->isDoneDate($actual, $due->toDateString()),
                ];
            })
            ->values();
    }

    private function scheduleRows(Carbon $today): Collection
    {
        return app(ScheduleExpiryNotifier::class)->dueItems($today)
            ->map(fn (object $i) => (object) [
                'type' => 'jadwal',
                'source_key' => $i->unique_key,
                'category' => $i->category,
                'tool_name' => $i->tool_name,
                'detail' => $this->scheduleDetail($i),
                'due_date' => $i->next_date,
                'sisa_hari' => $i->sisa_hari,
                'actual_date' => $i->actual_date,
                'done' => $this->isDoneDate($i->actual_date, $i->next_date),
            ])
            ->values();
    }

    /**
     * Aturan "sudah diisi": tanggal verifikasi/kalibrasi tidak lebih dari 30 hari
     * sebelum jatuh tempo (atau sesudahnya).
     */
    private function isDoneDate(?Carbon $actual, string $dueYmd): bool
    {
        if ($actual === null) {
            return false;
        }

        $batas = Carbon::parse($dueYmd)->startOfDay()->subDays(self::DONE_WINDOW_DAYS);

        return $actual->copy()->startOfDay()->gte($batas);
    }

    /** Catat item yang sudah selesai, supaya bisa ditampilkan sebagai "baru selesai" selama 7 hari. */
    private function recordDone(Collection $rows, Carbon $today, bool $seed): void
    {
        foreach ($rows->where('done', true) as $r) {
            $exists = DB::table('reminder_logs')
                ->where('source_key', $r->source_key)
                ->where('due_date', $r->due_date)
                ->whereIn('level', [self::DONE_LEVEL, self::DONE_SEED_LEVEL])
                ->exists();

            if (! $exists) {
                DB::table('reminder_logs')->insertOrIgnore([
                    'source_key' => $r->source_key,
                    'due_date' => $r->due_date,
                    'level' => $seed ? self::DONE_SEED_LEVEL : self::DONE_LEVEL,
                    'sent_on' => $today->toDateString(),
                    'recipient_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /** Item yang baru tercatat selesai dalam 7 hari terakhir (dan memang masih selesai). */
    private function newlyDone(Collection $rows, Carbon $today): Collection
    {
        $keys = DB::table('reminder_logs')
            ->where('level', self::DONE_LEVEL)
            ->where('sent_on', '>=', $today->copy()->subDays(self::NEW_DONE_DAYS - 1)->toDateString())
            ->get(['source_key', 'due_date'])
            ->map(fn ($r) => $r->source_key . '|' . $r->due_date)
            ->flip();

        return $rows
            ->filter(fn (object $r) => $r->done && $keys->has($r->source_key . '|' . $r->due_date))
            ->sortBy('tool_name')
            ->values();
    }

    private function verifiedDatesByTool(int $year): array
    {
        $map = [];

        Verification::where('tahun', $year)
            ->whereNotNull('verified_at')
            ->get(['tool_name', 'verified_at'])
            ->each(function (Verification $row) use (&$map) {
                $key = $this->toolKey($row->tool_name);
                $date = Carbon::parse($row->verified_at)->startOfDay();

                if (! isset($map[$key]) || $date->gt($map[$key])) {
                    $map[$key] = $date;
                }
            });

        return $map;
    }

    private function verifikasiBelumKonfirmasi(int $year): int
    {
        return Verification::where('tahun', $year)
            ->whereNull('verified_at')
            ->where('total', '>', 0)
            ->count();
    }

    // =====================================================================
    // PENGIRIMAN & LOG
    // =====================================================================

    /** Penerima: daftar di pengaturan 'reminder_emails', atau semua admin yang punya email. */
    private function recipients(): array
    {
        $configured = trim((string) Setting::get('reminder_emails', ''));

        $emails = $configured !== ''
            ? array_map('trim', explode(',', $configured))
            : User::where('role', 'admin')->whereNotNull('email')->pluck('email')->all();

        return collect($emails)
            ->map(fn ($e) => mb_strtolower(trim((string) $e)))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    private function sendToAll(array $recipients, $notification): int
    {
        $ok = 0;
        foreach ($recipients as $email) {
            try {
                Notification::route('mail', $email)->notify($notification);
                $ok++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $ok;
    }

    private function logExists(string $key, Carbon $today): bool
    {
        return DB::table('reminder_logs')
            ->where('source_key', $key)
            ->where('sent_on', $today->toDateString())
            ->exists();
    }

    private function logRun(string $key, Carbon $today, int $recipients): void
    {
        DB::table('reminder_logs')->insert([
            'source_key' => $key,
            'due_date' => $today->toDateString(),
            'level' => $key,
            'sent_on' => $today->toDateString(),
            'recipient_count' => $recipients,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // =====================================================================
    // SETTING & FORMAT
    // =====================================================================

    private function jadwalWindow(): int
    {
        $days = (int) Setting::get('warning_days_jadwal', 5);

        return $days > 0 ? $days : 5;
    }

    private function mesinCepat(): int
    {
        return Setting::warningCepat();
    }

    private function mesinLama(): int
    {
        return max(1, Setting::warningStandar());
    }

    private function rowJadwal(object $r): array
    {
        return [
            'jenis' => $r->category,
            'nama' => $r->tool_name,
            'detail' => $r->detail,
            'jatuh_tempo' => $this->tanggal($r->due_date),
            'keterangan' => $this->keterangan($r->sisa_hari),
        ];
    }

    private function keterangan(int $sisa): string
    {
        if ($sisa < 0) {
            return 'Terlambat ' . abs($sisa) . ' hari';
        }

        return $sisa === 0 ? 'Hari ini' : $sisa . ' hari lagi';
    }

    private function tanggal(string $ymd): string
    {
        $d = Carbon::parse($ymd);

        return $d->day . ' ' . self::BULAN[$d->month] . ' ' . $d->year;
    }

    private function scheduleDetail(object $item): ?string
    {
        return $this->joinDetail([
            $item->company,
            $item->shop ? "Shop: {$item->shop}" : null,
            $item->specification ? "Spec: {$item->specification}" : null,
            $item->registration_number ? "Reg: {$item->registration_number}" : null,
            $item->serial_number ? "SN: {$item->serial_number}" : null,
        ]);
    }

    private function joinDetail(array $parts): ?string
    {
        $text = collect($parts)
            ->map(fn ($p) => trim((string) $p))
            ->filter()
            ->implode(' · ');

        return $text !== '' ? $text : null;
    }

    private function toolKey(?string $name): string
    {
        return mb_strtoupper(trim((string) $name));
    }
}