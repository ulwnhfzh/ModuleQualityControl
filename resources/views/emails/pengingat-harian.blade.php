<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; line-height: 1.6;">

    <p>Halo Admin QC,</p>

    @php
        $adaYangDitindak = $counts['mendekati'] + $counts['terlambat'] + $counts['selesai_baru']
            + $counts['mesin_terlambat'] + $counts['mesin_jatuh_tempo'];
    @endphp

    @if ($adaYangDitindak === 0)
        <p>Selamat pagi. Hari ini tidak ada jadwal atau alat yang perlu ditindaklanjuti.</p>
    @else
        <p>Selamat pagi. Berikut daftar yang perlu ditindaklanjuti hari ini.</p>

        <p>
            Jatuh tempo dalam 5 hari: <b>{{ $counts['mendekati'] }}</b> jadwal.
            Terlambat 1 sampai 90 hari: <b>{{ $counts['terlambat'] }}</b> jadwal.
            @if ($counts['mesin_terlambat'] + $counts['mesin_jatuh_tempo'] > 0)
                Mesin terlambat: <b>{{ $counts['mesin_terlambat'] }}</b>,
                mesin jatuh tempo dalam 7 hari: <b>{{ $counts['mesin_jatuh_tempo'] }}</b>.
            @endif
        </p>
    @endif

    @include('emails.partials.tabel-jadwal', [
        'judul' => 'Jatuh tempo dalam 5 hari',
        'rows' => $sections['mendekati'],
        'total' => $counts['mendekati'],
    ])

    @include('emails.partials.tabel-jadwal', [
        'judul' => 'Terlambat 1 sampai 90 hari',
        'rows' => $sections['terlambat'],
        'total' => $counts['terlambat'],
    ])

    @if ($counts['selesai_baru'] > 0)
        @include('emails.partials.tabel-jadwal', [
            'judul' => 'Baru ditandai selesai, mohon diperiksa',
            'rows' => $sections['selesai_baru'],
            'total' => $counts['selesai_baru'],
        ])
        <p style="font-size:12px; color:#666;">
            Jika tanggal ini keliru, perbaiki di aplikasi. Jadwal akan kembali diingatkan.
        </p>
    @endif

    @include('emails.partials.tabel-jadwal', [
        'judul' => 'Mesin yang sudah terlambat',
        'rows' => $sections['mesin_terlambat'],
        'total' => $counts['mesin_terlambat'],
    ])

    @include('emails.partials.tabel-jadwal', [
        'judul' => 'Mesin yang jatuh tempo dalam 7 hari',
        'rows' => $sections['mesin_jatuh_tempo'],
        'total' => $counts['mesin_jatuh_tempo'],
    ])

    @if ($counts['lebih90'] > 0 || $counts['mesin_lama'] > 0 || $counts['verif_konfirmasi'] > 0)
        <p style="margin-top:18px; font-size:13px; color:#444;">
            @if ($counts['lebih90'] > 0)
                Ada {{ $counts['lebih90'] }} jadwal yang terlambat lebih dari 90 hari. Daftarnya dikirim setiap Senin.<br>
            @endif
            @if ($counts['mesin_lama'] > 0)
                Ada {{ $counts['mesin_lama'] }} mesin yang masih lama (8 sampai 30 hari).<br>
            @endif
            @if ($counts['verif_konfirmasi'] > 0)
                Ada {{ $counts['verif_konfirmasi'] }} data verifikasi yang belum dikonfirmasi.
            @endif
        </p>
    @endif

    <p style="margin-top:18px;">
        Kalau sudah dilakukan verifikasi atau kalibrasi, tolong isi tanggalnya di aplikasi.
        Setelah itu alatnya tidak akan muncul lagi di pengingat.
    </p>

    <p>Daftar lengkapnya bisa dibuka di <a href="{{ $notificationUrl }}">halaman Notification</a>.</p>

    <p>Terima kasih.</p>
    <p>Salam,<br>QC Tools</p>

    <hr style="border:none; border-top:1px solid #ddd; margin-top:20px;">
    <p style="font-size:11px; color:#888;">
        Email ini dikirim otomatis setiap hari ke admin QC yang terdaftar.
    </p>
</div>