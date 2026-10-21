<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlatJatuhTempoNotification extends Notification
{
    use Queueable;

    protected $tool;
    protected $sisaHari;

    public function __construct($tool, $sisaHari)
    {
        $this->tool = $tool;
        $this->sisaHari = $sisaHari;
    }

    public function via(object $notifiable): array
    {
        return ['mail']; // Bisa ditambah 'database' nanti
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->sisaHari < 0 ? 'TERLAMBAT' : 'AKAN JATUH TEMPO';
        
        return (new MailMessage)
                    ->subject("⚠️ Peringatan {$status}: {$this->tool->tool_name}")
                    ->greeting('Halo Admin QC,')
                    ->line("Alat ukur berikut memerlukan perhatian:")
                    ->line("Nama Alat: **{$this->tool->tool_name}**")
                    ->line("Kategori: {$this->tool->category}")
                    ->line("Tanggal Jatuh Tempo: **" . \Carbon\Carbon::parse($this->tool->next_date)->format('d F Y') . "**")
                    ->line("Status: " . ($this->sisaHari < 0 ? "Terlambat {$this->sisaHari} hari" : "Tersisa {$this->sisaHari} hari lagi"))
                    ->action('Lihat Daftar Alat', url('/tools'))
                    ->line('Mohon segera lakukan tindakan untuk melakukan kalibrasi/verifikasi ulang.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tool_id' => $this->tool->id,
            'tool_name' => $this->tool->tool_name,
        ];
    }
}