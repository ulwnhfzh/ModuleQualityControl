<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Induk Alat Ukur - {{ $tahun }}</title>
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
        'judul' => 'DAFTAR INDUK ALAT UKUR',
        'nomorDokumen' => $nomorDokumen ?? null,
    ])

    {{-- INFO TAMBAHAN --}}
    <table style="width: 100%; border: none; font-size: 10px; margin-bottom: 5px;">
        <tr>
            <td style="border: none; padding: 2px 0;">Kategori</td>
            <td style="border: none; padding: 2px 0;">: <strong>{{ $kategori }}</strong></td>
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
            @forelse($tools as $index => $tool)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ strtoupper($tool->tool_name) }}</td>
                <td>{{ $tool->qa_1 ?? 0 }}</td>
                <td>{{ $tool->qa_1_2 ?? 0 }}</td>
                <td>{{ $tool->qa_inc ?? 0 }}</td>
                <td>{{ $tool->qa_insp ?? 0 }}</td>
                <td>{{ $tool->ppic_1 ?? 0 }}</td>
                <td>{{ $tool->ppic_2 ?? 0 }}</td>
                <td>{{ $tool->line_scc ?? 0 }}</td>
                <td>{{ $tool->p1 ?? 0 }}</td>
                <td>{{ $tool->p2 ?? 0 }}</td>
                <td>{{ $tool->p3 ?? 0 }}</td>
                <td>{{ $tool->p5 ?? 0 }}</td>
                <td>{{ $tool->p6 ?? 0 }}</td>
                <td>{{ $tool->p7 ?? 0 }}</td>
                <td>{{ $tool->p8 ?? 0 }}</td>
                <td>{{ $tool->eng ?? 0 }}</td>
                <td><strong>{{ $tool->total ?? 0 }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="18" style="text-align: center; padding: 20px;">
                    Belum ada data untuk kategori {{ $kategori }} tahun {{ $tahun }}.
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($tools->count() > 0)
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td>{{ $tools->sum('qa_1') }}</td>
                <td>{{ $tools->sum('qa_1_2') }}</td>
                <td>{{ $tools->sum('qa_inc') }}</td>
                <td>{{ $tools->sum('qa_insp') }}</td>
                <td>{{ $tools->sum('ppic_1') }}</td>
                <td>{{ $tools->sum('ppic_2') }}</td>
                <td>{{ $tools->sum('line_scc') }}</td>
                <td>{{ $tools->sum('p1') }}</td>
                <td>{{ $tools->sum('p2') }}</td>
                <td>{{ $tools->sum('p3') }}</td>
                <td>{{ $tools->sum('p5') }}</td>
                <td>{{ $tools->sum('p6') }}</td>
                <td>{{ $tools->sum('p7') }}</td>
                <td>{{ $tools->sum('p8') }}</td>
                <td>{{ $tools->sum('eng') }}</td>
                <td>{{ $tools->sum('total') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- ===== FOOTER DOKUMEN ===== --}}
    @include('pdf.footer-dokumen', ['nomorDokumen' => $nomorDokumen ?? null])

</body>
</html>