<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; line-height: 1.6;">

    <p>Halo Admin QC,</p>

    @if ($count > 0)
        <p>
            Ada <b>{{ $count }} jadwal</b> verifikasi atau kalibrasi yang sudah terlambat lebih dari 90 hari
            dan belum diisi di aplikasi.
        </p>
        <p>Daftar lengkapnya ada di file Excel terlampir, diurutkan dari yang paling lama terlambat.</p>
        <p>
            Jadwal ini kemungkinan sudah dikerjakan tetapi belum dicatat, atau memang terlewat.
            Mohon ditinjau satu per satu. Setelah tanggal verifikasi diisi di aplikasi,
            jadwal akan hilang dari daftar ini.
        </p>
    @else
        <p>Minggu ini tidak ada jadwal yang terlambat lebih dari 90 hari. Bagus.</p>
    @endif

    <p>Buka halaman Notification untuk memperbarui datanya: <a href="{{ $notificationUrl }}">halaman Notification</a>.</p>

    <p>Terima kasih.</p>
    <p>Salam,<br>QC Tools</p>

    <hr style="border:none; border-top:1px solid #ddd; margin-top:20px;">
    <p style="font-size:11px; color:#888;">Rekap ini dikirim otomatis setiap Senin pagi.</p>
</div>