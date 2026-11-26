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

    public function __construct(
        public readonly Carbon $date,
        public readonly array $counts,
        public readonly array $jadwal,
        public readonly array $mesinTerlambat,
        public readonly array $mesinJatuhTempo,
        public readonly string $notificationUrl,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Pengingat verifikasi dan kalibrasi, '
            . $this->date->day . ' ' . self::BULAN[$this->date->month] . ' ' . $this->date->year;

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.pengingat-harian', [
                'counts' => $this->counts,
                'jadwal' => $this->jadwal,
                'mesinTerlambat' => $this->mesinTerlambat,
                'mesinJatuhTempo' => $this->mesinJatuhTempo,
                'notificationUrl' => $this->notificationUrl,
            ]);
    }
}