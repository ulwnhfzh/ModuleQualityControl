<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        /* Styling Umum */
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { border-collapse: collapse; }
        
        /* Styling Tabel Header Dokumen */
        .header-table { width: 100%; margin-bottom: 10px; }
        .header-table td { padding: 5px; vertical-align: middle; }
        .header-logo { text-align: center; border: 1px solid #000; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; border: 1px solid #000; }
        .header-info-label { border: 1px solid #000; font-weight: bold; width: 120px; background-color: #f3f4f6; }
        .header-info-value { border: 1px solid #000; width: 200px; }
        
        /* Styling Tabel Data */
        .data-table { width: 100%; margin-top: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; text-align: center; padding: 4px; }
        
        /* Header Warna Biru */
        .bg-blue-header { background-color: #1e40af; color: #ffffff; font-weight: bold; }
        /* Header Warna Hijau */
        .bg-green-header { background-color: #10b981; color: #ffffff; font-weight: bold; }
        
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        /* Styling Tanda Tangan */
        .signature-table { width: 50%; margin-top: 30px; float: right; }
        .signature-table th, .signature-table td { border: 1px solid #000; text-align: center; padding: 10px; height: 60px; }
        .signature-title { background-color: #e5e7eb; font-weight: bold; }
    </style>

    <!-- ================= HEADER DOKUMEN ================= -->
    <table class="header-table">
        <tr>
            <td rowspan="4" class="header-logo" style="width: 120px;">
                <!-- Ganti dengan path logo Anda jika ada -->
                <img src="{{ public_path('images/hamatetsu_indonesia_logo.jpg') }}" style="height: 60px;" alt="Logo">
            </td>
            <td rowspan="4" class="header-title">JADWAL VERIFIKASI & REKAP KERUSAKAN ALAT</td>
            <td class="header-info-label">Halaman</td>
            <td class="header-info-value">Otomatis saat dicetak</td>
        </tr>
        <tr>
            <td class="header-info-label">Nomor Dokumen</td>
            <td class="header-info-value">F-NG-01</td>
        </tr>
        <tr>
            <td class="header-info-label">Tanggal Terbit</td>
            <td class="header-info-value">10 Juli 2023</td>
        </tr>
        <tr>
            <td class="header-info-label">Revisi</td>
            <td class="header-info-value">02</td>
        </tr>
    </table>

    <!-- ================= INFO PERIODE ================= -->
    <table style="width: 100%; margin-top: 15px; border: none;">
        <tr>
            <td style="text-align: left; font-weight: bold;">PERIODE: YEAR {{ $tahun }}</td>
            <td style="text-align: right; font-weight: bold;">KATEGORI: ALL DIVISION</td>
        </tr>
    </table>

    <!-- ================= TABEL DATA UTAMA ================= -->
    <table class="data-table">
        <thead>
            <tr class="bg-blue-header">
                <th rowspan="2">No</th>
                <th rowspan="2">Division</th>
                @foreach($bulanList as $bulan)
                    <th colspan="12">{{ $bulan }}</th>
                @endforeach
                <th rowspan="2">Grand Total</th>
            </tr>
            <tr class="bg-green-header">
                @foreach($bulanList as $bulan)
                    @foreach($jenisKerusakan as $jenis)
                        <th>{{ strtoupper(str_replace('_', ' ', $jenis)) }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($urutanDivisi as $divisi)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td class="text-left font-bold">{{ $divisi }}</td>
                    
                    @foreach($bulanList as $bulan)
                        @foreach($jenisKerusakan as $jenis)
                            <td>{{ $matrixData[$divisi][$bulan][$jenis] ?: '0' }}</td>
                        @endforeach
                    @endforeach
                    
                    <td class="font-bold">{{ $grandTotalDivisi[$divisi] ?: '0' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-green-header">
                <th colspan="2" class="text-right">TOTAL</th>
                @foreach($bulanList as $bulan)
                    @foreach($jenisKerusakan as $jenis)
                        <th>{{ $footerBulanan[$bulan][$jenis] ?: '0' }}</th>
                    @endforeach
                @endforeach
                <th>{{ array_sum($grandTotalDivisi) ?: '0' }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- ================= KOLOM TANDA TANGAN ================= -->
    <table class="signature-table">
        <tr>
            <th class="signature-title">APPROVED</th>
            <th class="signature-title">CHECKED</th>
            <th class="signature-title">ISSUED</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>
</html>