<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Jadwal Verifikasi - {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 3px 4px; text-align: center; font-size: 8px; }
        table.data th { background-color: #1e40af; color: #fff; font-size: 8px; }
        table.data td.text-left { text-align: left; }
        table.data tfoot td { background-color: #d1fae5; font-weight: bold; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>

    @php
        $labelMode = ($mode === 'bulanan') ? 'TAHUNAN' : 'BULANAN';
    @endphp

    {{-- ===== KOP SURAT ===== --}}
    @include('pdf.kop-surat', [
        'judul' => 'JADWAL VERIFIKASI INTERNAL - MODE ' . $labelMode,
        'nomorDokumen' => $nomorDokumen ?? null,
    ])

    {{-- INFO TAMBAHAN --}}
    <table style="width: 100%; border: none; font-size: 10px; margin-bottom: 5px;">
        <tr>
            <td style="border: none; padding: 2px 0;">Mode</td>
            <td style="border: none; padding: 2px 0;">: <strong>{{ $labelMode }}</strong></td>
            <td style="border: none; padding: 2px 0; text-align: right;">Tahun</td>
            <td style="border: none; padding: 2px 0;">: <strong>{{ $tahun }}</strong></td>
        </tr>
    </table>

    {{-- ===== TABEL DATA ===== --}}
    <table class="data">
        @if($mode === 'harian')
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tool Name</th>
                    <th>Tanggal</th>
                    <th>QA 1</th>
                    <th>QA 1.2</th>
                    <th>QA INC</th>
                    <th>QA INSP</th>
                    <th>PPIC 1</th>
                    <th>PPIC 2</th>
                    <th>LINE SCC</th>
                    <th>P1</th>
                    <th>P2</th>
                    <th>P3</th>
                    <th>P5</th>
                    <th>P6</th>
                    <th>P7</th>
                    <th>P8</th>
                    <th>ENG</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ strtoupper($item->tool_name) }}</td>
                    <td>{{ $item->tanggal ?? '-' }}</td>
                    <td>{{ $item->qa_1 ?? 0 }}</td>
                    <td>{{ $item->qa_1_2 ?? 0 }}</td>
                    <td>{{ $item->qa_inc ?? 0 }}</td>
                    <td>{{ $item->qa_insp ?? 0 }}</td>
                    <td>{{ $item->ppic_1 ?? 0 }}</td>
                    <td>{{ $item->ppic_2 ?? 0 }}</td>
                    <td>{{ $item->line_scc ?? 0 }}</td>
                    <td>{{ $item->p1 ?? 0 }}</td>
                    <td>{{ $item->p2 ?? 0 }}</td>
                    <td>{{ $item->p3 ?? 0 }}</td>
                    <td>{{ $item->p5 ?? 0 }}</td>
                    <td>{{ $item->p6 ?? 0 }}</td>
                    <td>{{ $item->p7 ?? 0 }}</td>
                    <td>{{ $item->p8 ?? 0 }}</td>
                    <td>{{ $item->eng ?? 0 }}</td>
                    <td><strong>{{ $item->total ?? 0 }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="19" style="text-align: center; padding: 20px;">Belum ada data.</td></tr>
                @endforelse
            </tbody>
            @if($data->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td>{{ $data->sum('qa_1') }}</td>
                    <td>{{ $data->sum('qa_1_2') }}</td>
                    <td>{{ $data->sum('qa_inc') }}</td>
                    <td>{{ $data->sum('qa_insp') }}</td>
                    <td>{{ $data->sum('ppic_1') }}</td>
                    <td>{{ $data->sum('ppic_2') }}</td>
                    <td>{{ $data->sum('line_scc') }}</td>
                    <td>{{ $data->sum('p1') }}</td>
                    <td>{{ $data->sum('p2') }}</td>
                    <td>{{ $data->sum('p3') }}</td>
                    <td>{{ $data->sum('p5') }}</td>
                    <td>{{ $data->sum('p6') }}</td>
                    <td>{{ $data->sum('p7') }}</td>
                    <td>{{ $data->sum('p8') }}</td>
                    <td>{{ $data->sum('eng') }}</td>
                    <td>{{ $data->sum('total') }}</td>
                </tr>
            </tfoot>
            @endif
        @else
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
                    <th>Total</th>
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
                    <td><strong>{{ $item->total ?? 0 }}</strong></td>
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
                    <td>{{ $data->sum('total') }}</td>
                </tr>
            </tfoot>
            @endif
        @endif
    </table>

    {{-- ===== FOOTER DOKUMEN ===== --}}
    @include('pdf.footer-dokumen', ['nomorDokumen' => $nomorDokumen ?? null])

</body>
</html>