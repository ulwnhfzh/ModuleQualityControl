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
        $status = $overdue ? 'TERLAMBAT' : 'MENDEKATI TENGGAT';

        return (new MailMessage)
            ->subject("Pengingat verifikasi {$status}: {$this->toolName}")
            ->greeting('Halo Admin QC,')
            ->line("Verification alat ukur {$this->toolName} memerlukan tindak lanjut.")
            ->line("Tenggat verifikasi: {$date}")
            ->line($overdue ? 'Tenggat sudah lewat ' . abs($this->daysRemaining) . ' hari.' : 'Tersisa ' . $this->daysRemaining . ' hari.')
            ->action('Lihat daftar notifikasi', url('/notifikasi'));
    }
}
