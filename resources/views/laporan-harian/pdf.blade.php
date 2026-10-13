<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Harian QC - {{ $tanggalCarbon->format('d/m/Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; color: #1e40af; }
        .header .sub { font-size: 11px; color: #666; }
        .info-dokumen { margin-bottom: 15px; }
        .info-dokumen table { width: 100%; }
        .info-dokumen td { padding: 3px 5px; vertical-align: top; }
        .info-dokumen .label { font-weight: bold; width: 120px; }
        .section { margin-bottom: 15px; }
        .section h3 { background-color: #1e40af; color: #fff; padding: 6px 10px; margin: 0 0 8px 0; font-size: 11px; }
        .kartu { display: inline-block; width: 24%; text-align: center; border: 1px solid #ddd; padding: 8px; margin-right: 1%; vertical-align: top; box-sizing: border-box; }
        .kartu .angka { font-size: 24px; font-weight: bold; color: #1e40af; }
        .kartu .label { font-size: 9px; color: #666; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px 5px; font-size: 9px; }
        table.data th { background-color: #1e40af; color: #fff; text-align: center; }
        table.data td.center { text-align: center; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 8px; font-weight: bold; color: #fff; }
        .badge-create { background-color: #16a34a; }
        .badge-update { background-color: #f59e0b; }
        .badge-delete { background-color: #dc2626; }
        .badge-login { background-color: #3b82f6; }
        .badge-logout { background-color: #6b7280; }
        .badge-export { background-color: #8b5cf6; }
        .ttd { margin-top: 30px; }
        .ttd table { width: 100%; }
        .ttd td { text-align: center; padding-top: 50px; vertical-align: bottom; }
        .ttd .line { border-top: 1px solid #333; padding-top: 5px; display: inline-block; width: 200px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <h2>PT. HAMATETSU INDONESIA</h2>
        <div class="sub">QC Measurement Tools - Laporan Harian</div>
    </div>

    {{-- INFO DOKUMEN --}}
    <div class="info-dokumen">
        <table>
            <tr>
                <td class="label">Nomor Dokumen</td>
                <td>: <strong>{{ $nomorDokumen }}</strong></td>
                <td class="label">Tanggal Laporan</td>
                <td>: <strong>{{ $tanggalCarbon->format('d F Y') }}</strong></td>
            </tr>
            <tr>
                <td class="label">Pencetak</td>
                <td>: {{ $pencetak->name ?? '-' }}</td>
                <td class="label">Waktu Cetak</td>
                <td>: {{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    {{-- RINGKASAN --}}
    <div class="section">
        <h3>RINGKASAN HARI INI</h3>
        <div class="kartu">
            <div class="angka">{{ $totalAktivitas }}</div>
            <div class="label">Total Aktivitas</div>
        </div>
        <div class="kartu">
            <div class="angka">{{ $userAktif }}</div>
            <div class="label">User Aktif</div>
        </div>
        <div class="kartu">
            <div class="angka">{{ $verifikasiHariIni }}</div>
            <div class="label">Verifikasi</div>
        </div>
        <div class="kartu">
            <div class="angka">{{ $kalibrasiHariIni }}</div>
            <div class="label">Kalibrasi</div>
        </div>
    </div>

    {{-- BREAKDOWN PER JENIS --}}
    <div class="section">
        <h3>AKTIVITAS PER JENIS DATA</h3>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 70%;">Jenis Data</th>
                    <th style="width: 30%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitasPerJenis as $item)
                <tr>
                    <td>
                        @php
                            $labels = [
                                'tools' => 'Daftar Induk',
                                'verifikasi' => 'Verifikasi',
                                'internal' => 'Kalibrasi Internal',
                                'eksternal' => 'Eksternal Small',
                                'mesin' => 'Eksternal Mesin',
                                'user' => 'User Management',
                                'laporan' => 'Laporan',
                            ];
                        @endphp
                        {{ $labels[$item->jenis_data] ?? $item->jenis_data ?? '-' }}
                    </td>
                    <td class="center"><strong>{{ $item->jumlah }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="2" class="center">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- LOG AKTIVITAS --}}
    <div class="section">
        <h3>LOG AKTIVITAS HARI INI</h3>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Waktu</th>
                    <th style="width: 15%;">User</th>
                    <th style="width: 12%;">Aksi</th>
                    <th style="width: 16%;">Jenis</th>
                    <th style="width: 40%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $log->created_at->format('H:i:s') }}</td>
                    <td>{{ $log->user_name ?? '-' }}</td>
                    <td class="center">
                        <span class="badge badge-{{ strtolower($log->aksi) }}">{{ $log->aksi }}</span>
                    </td>
                    <td>{{ $log->label_jenis ?? '-' }}</td>
                    <td>{{ $log->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="center" style="padding: 15px;">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ALAT JATUH TEMPO --}}
    <div class="section">
        <h3>ALAT JATUH TEMPO KALIBRASI (30 HARI KE DEPAN)</h3>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Tool / Machine</th>
                    <th style="width: 20%;">Machine Model</th>
                    <th style="width: 20%;">Maker</th>
                    <th style="width: 15%;">Next Date</th>
                    <th style="width: 15%;">Sisa Hari</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alatJatuhTempo as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->tool_name }}</td>
                    <td>{{ $item->machine_model ?? '-' }}</td>
                    <td>{{ $item->maker ?? '-' }}</td>
                    <td class="center">{{ $item->next_date ? \Carbon\Carbon::parse($item->next_date)->format('d/m/Y') : '-' }}</td>
                    <td class="center">
                        @if($item->sisa_hari < 0)
                            <strong style="color: #dc2626;">TERLAMBAT {{ abs($item->sisa_hari) }} hari</strong>
                        @elseif($item->sisa_hari <= 7)
                            <strong style="color: #f59e0b;">{{ $item->sisa_hari }} hari lagi</strong>
                        @else
                            {{ $item->sisa_hari }} hari lagi
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="center" style="padding: 15px;">Tidak ada alat yang jatuh tempo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TANDA TANGAN --}}
    <div class="ttd">
        <table>
            <tr>
                <td>
                    <div>Dibuat oleh,</div>
                    <div class="line">{{ $pencetak->name ?? '-' }}</div>
                    <div style="font-size: 9px; color: #666;">QC Staff</div>
                </td>
                <td>
                    <div>Disetujui oleh,</div>
                    <div class="line">_________________________</div>
                    <div style="font-size: 9px; color: #666;">QC Manager</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Dokumen ini dicetak otomatis dari sistem QC Measurement Tools - PT. Hamatetsu Indonesia<br>
        Nomor: {{ $nomorDokumen }} | Dicetak: {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>