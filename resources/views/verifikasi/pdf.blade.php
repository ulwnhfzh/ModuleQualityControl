<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kalibrasi Eksternal Mesin - {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 8px; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.sub { text-align: center; color: #666; margin-top: 0; margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 3px 4px; text-align: center; }
        th { background-color: #1e40af; color: #fff; font-size: 7px; }
        td.text-left { text-align: left; }
        tfoot td { background-color: #d1fae5; font-weight: bold; }
        .footer { margin-top: 20px; text-align: right; font-size: 9px; color: #666; }
    </style>
</head>
<body>

    <h2>KALIBRASI EKSTERNAL - MESIN ALAT UKUR</h2>
    <p class="sub">Tahun: {{ $tahun }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tool Name</th>
                <th>Machine Model</th>
                <th>Code Number</th>
                <th>Serial Number</th>
                <th>Maker</th>
                <th>No. Machine</th>
                <th>Retension</th>
                <th>Start Date</th>
                <th>Next Date</th>
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
                <td>{{ $item->machine_model ?? '-' }}</td>
                <td>{{ $item->code_number ?? '-' }}</td>
                <td>{{ $item->serial_number ?? '-' }}</td>
                <td>{{ $item->maker ?? '-' }}</td>
                <td>{{ $item->no_machine ?? '-' }}</td>
                <td>{{ $item->retension ?? '-' }}</td>
                <td>{{ $item->start_date ?? '-' }}</td>
                <td>{{ $item->next_date ?? '-' }}</td>
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
            <tr><td colspan="23" style="text-align: center; padding: 20px;">Belum ada data.</td></tr>
            @endforelse
        </tbody>
        @if($data->count() > 0)
        <tfoot>
            <tr>
                <td colspan="10">TOTAL</td>
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
    </table>

    <div class="footer">
        Dicetak pada: {{ date('d F Y, H:i') }}
    </div>

</body>
</html>