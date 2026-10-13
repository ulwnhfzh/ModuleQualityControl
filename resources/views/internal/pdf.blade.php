<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kalibrasi Internal - {{ $tahun }}</title>
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

    <h2>KALIBRASI INTERNAL</h2>
    <p class="sub">Tahun: {{ $tahun }}</p>

    <table>
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
                <th>Qty</th>
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
                <td><strong>{{ $item->qty ?? 0 }}</strong></td>
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
                <td>{{ $data->sum('qty') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d F Y, H:i') }}
    </div>

</body>
</html>