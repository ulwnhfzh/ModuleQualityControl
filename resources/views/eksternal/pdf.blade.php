<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kalibrasi Eksternal Small Tool - {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px 5px; text-align: center; font-size: 9px; }
        table.data th { background-color: #1e40af; color: #fff; font-size: 9px; }
        table.data td.text-left { text-align: left; }
        table.data tfoot td { background-color: #d1fae5; font-weight: bold; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>

    {{-- ===== KOP SURAT ===== --}}
    @include('pdf.kop-surat', [
        'judul' => 'KALIBRASI EKSTERNAL - SMALL TOOL',
        'nomorDokumen' => $nomorDokumen ?? null,
    ])

    {{-- INFO TAMBAHAN --}}
    <table style="width: 100%; border: none; font-size: 10px; margin-bottom: 5px;">
        <tr>
            <td style="border: none; padding: 2px 0; text-align: right;">Tahun</td>
            <td style="border: none; padding: 2px 0;">: <strong>{{ $tahun }}</strong></td>
        </tr>
    </table>

    {{-- ===== TABEL DATA ===== --}}
    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tool Name</th>
                <th>Jan</th>
                <th>Feb</th>
                <th>Mar</th>
                <th>Apr</th>
                <th>May</th>
                <th>Jun</th>
                <th>Jul</th>
                <th>Aug</th>
                <th>Sep</th>
                <th>Oct</th>
                <th>Nov</th>
                <th>Dec</th>
                <th>Qty Set</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ strtoupper($item->tool_name) }}</td>
                <td>{{ $item->jan ?? 0 }}</td>
                <td>{{ $item->feb ?? 0 }}</td>
                <td>{{ $item->mar ?? 0 }}</td>
                <td>{{ $item->apr ?? 0 }}</td>
                <td>{{ $item->may ?? 0 }}</td>
                <td>{{ $item->jun ?? 0 }}</td>
                <td>{{ $item->jul ?? 0 }}</td>
                <td>{{ $item->aug ?? 0 }}</td>
                <td>{{ $item->sep ?? 0 }}</td>
                <td>{{ $item->oct ?? 0 }}</td>
                <td>{{ $item->nov ?? 0 }}</td>
                <td>{{ $item->dec ?? 0 }}</td>
                <td><strong>{{ $item->qty_set ?? 0 }}</strong></td>
            </tr>
            @empty
            <tr><td colspan="15" style="text-align: center; padding: 20px;">Belum ada data.</td></tr>
            @endforelse
        </tbody>
        @if($data->count() > 0)
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td>{{ $data->sum('jan') }}</td>
                <td>{{ $data->sum('feb') }}</td>
                <td>{{ $data->sum('mar') }}</td>
                <td>{{ $data->sum('apr') }}</td>
                <td>{{ $data->sum('may') }}</td>
                <td>{{ $data->sum('jun') }}</td>
                <td>{{ $data->sum('jul') }}</td>
                <td>{{ $data->sum('aug') }}</td>
                <td>{{ $data->sum('sep') }}</td>
                <td>{{ $data->sum('oct') }}</td>
                <td>{{ $data->sum('nov') }}</td>
                <td>{{ $data->sum('dec') }}</td>
                <td>{{ $data->sum('qty_set') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- ===== FOOTER DOKUMEN ===== --}}
    @include('pdf.footer-dokumen', ['nomorDokumen' => $nomorDokumen ?? null])

</body>
</html>