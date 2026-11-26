<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; line-height: 1.6;">

    <p>Halo Admin QC,</p>

    <p>Selamat pagi. Ini pengingat otomatis untuk alat dan jadwal yang perlu ditindaklanjuti hari ini.</p>

    <p>
        Ada {{ $counts['terlambat_mesin'] }} mesin yang sudah terlambat,
        {{ $counts['jatuh_tempo_mesin'] }} mesin yang jatuh tempo dalam 7 hari,
        dan {{ $counts['jadwal_h5'] }} verifikasi atau kalibrasi yang jatuh tempo dalam 5 hari atau sudah lewat.
        Rinciannya ada di bawah.
    </p>

    @if (count($jadwal) > 0)
        <p><b>Verifikasi dan kalibrasi yang perlu segera diisi</b></p>
        <table style="border-collapse:collapse; width:100%; font-size:13px;" border="1" cellpadding="6">
            <tr style="background:#e3edf9; color:#1e3a5f;">
                <th align="left" style="font-weight:bold;">Jenis</th>
                <th align="left" style="font-weight:bold;">Nama alat</th>
                <th align="left" style="font-weight:bold;">Jatuh tempo</th>
                <th align="left" style="font-weight:bold;">Keterangan</th>
            </tr>
            @foreach ($jadwal as $row)
                <tr>
                    <td>{{ $row['jenis'] }}</td>
                    <td>{{ $row['nama'] }}@if ($row['detail']) ({{ $row['detail'] }})@endif</td>
                    <td>{{ $row['jatuh_tempo'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if (count($mesinTerlambat) > 0)
        <p><b>Mesin yang sudah terlambat</b></p>
        <table style="border-collapse:collapse; width:100%; font-size:13px;" border="1" cellpadding="6">
            <tr style="background:#e3edf9; color:#1e3a5f;">
                <th align="left" style="font-weight:bold;">Nama alat</th>
                <th align="left" style="font-weight:bold;">Jatuh tempo</th>
                <th align="left" style="font-weight:bold;">Keterangan</th>
            </tr>
            @foreach ($mesinTerlambat as $row)
                <tr>
                    <td>{{ $row['nama'] }}@if ($row['detail']) ({{ $row['detail'] }})@endif</td>
                    <td>{{ $row['jatuh_tempo'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if (count($mesinJatuhTempo) > 0)
        <p><b>Mesin yang jatuh tempo dalam 7 hari</b></p>
        <table style="border-collapse:collapse; width:100%; font-size:13px;" border="1" cellpadding="6">
            <tr style="background:#e3edf9; color:#1e3a5f;">
                <th align="left" style="font-weight:bold;">Nama alat</th>
                <th align="left" style="font-weight:bold;">Jatuh tempo</th>
                <th align="left" style="font-weight:bold;">Keterangan</th>
            </tr>
            @foreach ($mesinJatuhTempo as $row)
                <tr>
                    <td>{{ $row['nama'] }}@if ($row['detail']) ({{ $row['detail'] }})@endif</td>
                    <td>{{ $row['jatuh_tempo'] }}</td>
                    <td>{{ $row['keterangan'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($counts['masih_lama_mesin'] > 0 || $counts['verif_konfirmasi'] > 0)
        <p>
            Selain itu ada {{ $counts['masih_lama_mesin'] }} mesin yang masih lama (8 sampai 30 hari),
            dan {{ $counts['verif_konfirmasi'] }} data verifikasi yang belum dikonfirmasi.
        </p>
    @endif

    <p>
        Kalau sudah dilakukan verifikasi atau kalibrasi, tolong isi tanggalnya di aplikasi.
        Setelah itu alatnya tidak akan muncul lagi di pengingat.
    </p>

    <p>Daftar lengkapnya bisa dibuka di <a href="{{ $notificationUrl }}">halaman Notification</a>.</p>

    <p>Terima kasih.</p>

    <p>Salam,<br>QC Tools</p>

    <hr style="border:none; border-top:1px solid #ddd; margin-top:20px;">
    <p style="font-size:11px; color:#888;">
        Email ini dikirim otomatis setiap hari pukul 08:00 WIB ke semua admin yang punya email terdaftar.
    </p>

</div>