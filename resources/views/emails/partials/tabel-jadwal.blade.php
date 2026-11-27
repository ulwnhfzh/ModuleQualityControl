@if (count($rows) > 0)
    <p style="margin:18px 0 6px;"><b>{{ $judul }}</b> <span style="color:#666;">({{ $total }} total)</span></p>
    <table style="border-collapse:collapse; width:100%; font-size:13px;" border="1" cellpadding="6">
        <tr style="background:#e3edf9; color:#1e3a5f;">
            <th align="left" style="font-weight:bold;">Jenis</th>
            <th align="left" style="font-weight:bold;">Nama alat</th>
            <th align="left" style="font-weight:bold;">Jatuh tempo</th>
            <th align="left" style="font-weight:bold;">Keterangan</th>
        </tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['jenis'] }}</td>
                <td>{{ $row['nama'] }}@if ($row['detail']) ({{ $row['detail'] }})@endif</td>
                <td>{{ $row['jatuh_tempo'] }}</td>
                <td>{{ $row['keterangan'] }}</td>
            </tr>
        @endforeach
    </table>
    @if ($total > count($rows))
        <p style="font-size:12px; color:#666; margin:6px 0 0;">
            Menampilkan {{ count($rows) }} dari {{ $total }}. Daftar lengkap ada di halaman Notification.
        </p>
    @endif
@endif