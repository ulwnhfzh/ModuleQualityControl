<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyOverdueNotification extends Notification
{
    use Queueable;

    private const BULAN = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public function __construct(
        public readonly Carbon $date,
        public readonly int $count,
        public readonly ?string $excel,
        public readonly string $notificationUrl,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tanggal = $this->date->day . ' ' . self::BULAN[$this->date->month] . ' ' . $this->date->year;

        $mail = (new MailMessage)
            ->subject('[Rekap Mingguan] Jadwal terlambat lebih dari 90 hari – ' . $tanggal)
            ->view('emails.rekap-mingguan', [
                'count' => $this->count,
                'tanggal' => $tanggal,
                'notificationUrl' => $this->notificationUrl,
            ]);

        if ($this->excel !== null) {
            $mail->attachData(
                $this->excel,
                'terlambat_lebih_90_hari_' . $this->date->format('Y-m-d') . '.xlsx',
                ['mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        }

        return $mail;
    }
}