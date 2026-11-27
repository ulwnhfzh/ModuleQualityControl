<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReminderNotification extends Notification
{
    use Queueable;

    private const BULAN = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    private const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function __construct(
        public readonly Carbon $date,
        public readonly array $counts,
        public readonly array $sections,
        public readonly string $notificationUrl,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tanggal = self::HARI[$this->date->dayOfWeek] . ', ' . $this->date->day . ' '
            . self::BULAN[$this->date->month] . ' ' . $this->date->year;

        return (new MailMessage)
            ->subject('[Pengingat] Verifikasi & Kalibrasi – ' . $tanggal)
            ->view('emails.pengingat-harian', [
                'counts' => $this->counts,
                'sections' => $this->sections,
                'notificationUrl' => $this->notificationUrl,
            ]);
    }
}