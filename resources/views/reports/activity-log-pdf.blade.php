<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log Aktivitas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.sub { text-align: center; color: #666; margin-top: 0; margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 5px; text-align: left; }
        th { background-color: #1e40af; color: #fff; font-size: 9px; text-align: center; }
        td.center { text-align: center; }
        td.waktu { font-size: 8px; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #666; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 8px; font-weight: bold; color: #fff; }
        .badge-create { background-color: #10b981; }
        .badge-update { background-color: #f59e0b; }
        .badge-delete { background-color: #dc2626; }
        .badge-login { background-color: #3b82f6; }
        .badge-logout { background-color: #6b7280; }
        .badge-export { background-color: #8b5cf6; }
    </style>
    @include('pdf.table-theme')
</head>
<body>

    <h2>LOG AKTIVITAS SISTEM</h2>
    <p class="sub">QC Measurement Tools - PT. Hamatetsu Indonesia</p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Waktu</th>
                <th style="width: 12%;">User</th>
                <th style="width: 8%;">Aksi</th>
                <th style="width: 12%;">Jenis Data</th>
                <th style="width: 45%;">Keterangan</th>
                <th style="width: 7%;">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="center waktu">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                <td>{{ $log->user_name ?? '-' }}</td>
                <td class="center">
                    <span class="badge badge-{{ strtolower($log->aksi) }}">{{ $log->aksi }}</span>
                </td>
                <td>{{ $log->label_jenis ?? '-' }}</td>
                <td>{{ $log->keterangan ?? '-' }}</td>
                <td class="center">{{ $log->ip_address ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="center" style="padding: 20px;">Belum ada log aktivitas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d F Y, H:i') }} oleh {{ Auth::user()->name ?? 'System' }}
    </div>

</body>
</html>