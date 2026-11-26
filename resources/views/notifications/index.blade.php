<?php

namespace App\Services;

use App\Models\ExternalMachine;
use App\Models\User;
use App\Models\Verification;
use App\Notifications\DailyReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ReminderService
{
    /** Mesin yang jatuh tempo dalam batas ini masuk daftar "jatuh tempo 7 hari". */
    private const MESIN_CEPAT_DAYS = 7;

    /** Mesin dalam batas ini dihitung sebagai "masih lama" (hanya jumlah). */
    private const MESIN_LAMA_DAYS = 30;

    private const SUMMARY_KEY = 'summary';
    private const SEEN_LEVEL = 'seen';

    private const BULAN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    public function run(Carbon $today, bool $dryRun = false): array
    {
        $today = $today->copy()->startOfDay();
        $summary = ['items' => 0, 'sent' => 0, 'done' => 0, 'already_today' => 0, 'no_admin' => false];

        $admins = User::where('role', 'admin')->whereNotNull('email')->get();
        if ($admins->isEmpty()) {
            $summary['no_admin'] = true;

            return $summary;
        }

        if ($this->alreadySentToday($today)) {
            $summary['already_today'] = 1;

            return $summary;
        }

        $verifiedByTool = $this->verifiedDatesByTool($today->year);
        $mesin = $this->mesinItems($today, $verifiedByTool);
        $jadwal = $this->jadwalItems($today);
        $summary['items'] = $mesin->count() + $jadwal->count();

        // Mesin: pisahkan yang sudah diisi, terlambat, jatuh tempo 7 hari, dan masih lama
        $mesinAktif = collect();
        $mesinLama = 0;
        foreach ($mesin as $item) {
            if ($this->isDone($item, $today)) {
                $summary['done']++;
                continue;
            }
            if ($item->sisa_hari > self::MESIN_CEPAT_DAYS) {
                $mesinLama++;
                continue;
            }
            $mesinAktif->push($item);
        }

        // Jadwal verifikasi dan kalibrasi
        $jadwalAktif = collect();
        foreach ($jadwal as $item) {
            if ($this->isDone($item, $today)) {
                $summary['done']++;
                continue;
            }
            $jadwalAktif->push($item);
        }

        $mesinTerlambat = $mesinAktif->filter(fn (object $i) => $i->sisa_hari < 0)->sortBy('sisa_hari')->values();
        $mesinCepat = $mesinAktif->filter(fn (object $i) => $i->sisa_hari >= 0)->sortBy('sisa_hari')->values();
        $jadwalAktif = $jadwalAktif->sortBy('due_date')->values();

        $emailed = $jadwalAktif->merge($mesinTerlambat)->merge($mesinCepat)->values();
        if ($emailed->isEmpty()) {
            return $summary;
        }

        $summary['sent'] = $emailed->count();
        if ($dryRun) {
            return $summary;
        }

        Notification::send($admins, new DailyReminderNotification(
            date: $today,
            counts: [
                'terlambat_mesin' => $mesinTerlambat->count(),
                'jatuh_tempo_mesin' => $mesinCepat->count(),
                'masih_lama_mesin' => $mesinLama,
                'jadwal_h5' => $jadwalAktif->count(),
                'verif_konfirmasi' => $this->verifikasiBelumKonfirmasi($today->year),
            ],
            jadwal: $jadwalAktif->map(fn (object $i) => $this->rowJadwal($i))->all(),
            mesinTerlambat: $mesinTerlambat->map(fn (object $i) => $this->rowMesin($i))->all(),
            mesinJatuhTempo: $mesinCepat->map(fn (object $i) => $this->rowMesin($i))->all(),
            notificationUrl: url('/notifikasi'),
        ));

        // Catat tanggal pertama setiap item muncul di email (dipakai untuk aturan "sudah diisi")
        foreach ($emailed as $item) {
            DB::table('reminder_logs')->insertOrIgnore([
                'source_key' => $item->source_key,
                'due_date' => $item->due_date,
                'level' => self::SEEN_LEVEL,
                'sent_on' => $today->toDateString(),
                'recipient_count' => $admins->count(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Catat bahwa email harian hari ini sudah terkirim
        DB::table('reminder_logs')->insert([
            'source_key' => self::SUMMARY_KEY,
            'due_date' => $today->toDateString(),
            'level' => 'summary',
            'sent_on' => $today->toDateString(),
            'recipient_count' => $admins->count(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $summary;
    }

    /**
     * Aturan "sudah diisi": tanggal verifikasi/kalibrasi (actual_date) diisi atau diubah
     * pada atau setelah tanggal pertama item ini muncul di email.
     */
    private function isDone(object $item, Carbon $today): bool
    {
        if ($item->actual_date === null) {
            return false;
        }

        $firstSeen = $this->firstSeen($item->source_key, $item->due_date) ?? $today;

        return $item->actual_date->gte($firstSeen);
    }

    private function firstSeen(string $sourceKey, string $dueDate): ?Carbon
    {
        $min = DB::table('reminder_logs')
            ->where('source_key', $sourceKey)
            ->where('due_date', $dueDate)
            ->where('level', self::SEEN_LEVEL)
            ->min('sent_on');

        return $min ? Carbon::parse($min)->startOfDay() : null;
    }

    private function mesinItems(Carbon $today, array $verifiedByTool): Collection
    {
        return ExternalMachine::whereNotNull('next_date')
            ->whereDate('next_date', '<=', $today->copy()->addDays(self::MESIN_LAMA_DAYS)->toDateString())
            ->orderBy('id')
            ->get()
            ->map(function (ExternalMachine $tool) use ($today, $verifiedByTool): object {
                $nextDate = Carbon::parse($tool->next_date)->startOfDay();

                return (object) [
                    'type' => 'mesin',
                    'source_key' => "mesin:{$tool->id}",
                    'category' => 'Kalibrasi Mesin Eksternal',
                    'tool_name' => $tool->tool_name,
                    'detail' => $this->joinDetail([$tool->maker, $tool->machine_model]),
                    'due_date' => $nextDate->toDateString(),
                    'sisa_hari' => (int) $today->diffInDays($nextDate, false),
                    'actual_date' => $verifiedByTool[$this->toolKey($tool->tool_name)] ?? null,
                ];
            })
            ->values();
    }

    private function jadwalItems(Carbon $today): Collection
    {
        return app(ScheduleExpiryNotifier::class)->dueItems($today)
            ->map(fn (object $item) => (object) [
                'type' => 'jadwal',
                'source_key' => $item->unique_key,
                'category' => $item->category,
                'tool_name' => $item->tool_name,
                'detail' => $this->scheduleDetail($item),
                'due_date' => $item->next_date,
                'sisa_hari' => $item->sisa_hari,
                'actual_date' => $item->actual_date,
            ])
            ->values();
    }

    private function rowJadwal(object $i): array
    {
        return [
            'jenis' => $i->category,
            'nama' => $i->tool_name,
            'detail' => $i->detail,
            'jatuh_tempo' => $this->tanggal($i->due_date),
            'keterangan' => $this->tanggalValid($i->due_date) ? $this->keterangan($i->sisa_hari) : 'Tanggal belum diisi',
        ];
    }

    private function rowMesin(object $i): array
    {
        return [
            'nama' => $i->tool_name,
            'detail' => $i->detail,
            'jatuh_tempo' => $this->tanggal($i->due_date),
            'keterangan' => $this->tanggalValid($i->due_date) ? $this->keterangan($i->sisa_hari) : 'Tanggal belum diisi',
        ];
    }

    private function tanggalValid(string $ymd): bool
    {
        return Carbon::parse($ymd)->year >= ScheduleExpiryNotifier::MIN_VALID_YEAR;
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

        if ($d->year < ScheduleExpiryNotifier::MIN_VALID_YEAR) {
            return '-';
        }

        return $d->day . ' ' . self::BULAN[$d->month] . ' ' . $d->year;
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

    private function alreadySentToday(Carbon $today): bool
    {
        return DB::table('reminder_logs')
            ->where('source_key', self::SUMMARY_KEY)
            ->where('sent_on', $today->toDateString())
            ->exists();
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