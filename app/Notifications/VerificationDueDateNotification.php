<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationDueDateNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $toolName,
        public readonly string $nextDate,
        public readonly string $level,
        public readonly int $daysRemaining,
        public readonly string $category = 'Verifikasi / Kalibrasi',
        public readonly ?string $detailInfo = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $overdue = $this->level === 'terlambat';
        $date = \Carbon\Carbon::parse($this->nextDate)->format('d/m/Y');
        $status = $overdue ? 'TERLAMBAT' : 'EXPIRED H-' . max(0, $this->daysRemaining);

        $mail = (new MailMessage)
            ->subject("[{$this->category}] Pengingat {$status}: {$this->toolName}")
            ->greeting('Halo Admin QC,')
            ->line("Jadwal **{$this->category}** untuk alat ukur **{$this->toolName}** memerlukan tindak lanjut.");

        if ($this->detailInfo) {
            $mail->line("Detail Alat: {$this->detailInfo}");
        }

        return $mail
            ->line("Tanggal Expired / Jatuh Tempo: **{$date}**")
            ->line($overdue
                ? 'Tenggat sudah lewat ' . abs($this->daysRemaining) . ' hari.'
                : ($this->daysRemaining === 0 ? 'Jatuh tempo HARI INI.' : "Tersisa {$this->daysRemaining} hari lagi menuju expired."))
            ->action('Lihat Daftar Notifikasi', url('/notifikasi'));
    }
}