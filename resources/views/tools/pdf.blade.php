<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Induk Alat Ukur - {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.sub { text-align: center; color: #666; margin-top: 0; margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 5px; text-align: center; }
        th { background-color: #1e40af; color: #fff; font-size: 9px; }
        td.text-left { text-align: left; }
        tfoot td { background-color: #d1fae5; font-weight: bold; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; color: #666; }
    </style>
</head>
<body>

    <h2>DAFTAR INDUK ALAT UKUR</h2>
    <p class="sub">Kategori: {{ $kategori }} | Tahun: {{ $tahun }}</p>

    <table>
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

    <div class="footer">
        Dicetak pada: {{ date('d F Y, H:i') }}
    </div>

</body>
</html>