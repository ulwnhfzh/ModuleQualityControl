<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>List Up NG - {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data th, table.data td { border: 1px solid #333; padding: 4px; text-align: center; font-size: 8px; }
        table.data th { background-color: #1e40af; color: #fff; }
        table.data td.text-left { text-align: left; }
        table.data tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>

    <h2 style="text-align: center; margin-bottom: 5px;">LIST UP TOTAL TOOL MEASUREMENT NG {{ $tahun }}</h2>
    @if($division)
        <p style="text-align: center; margin-top: 0;">Departemen: <strong>{{ $division }}</strong></p>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tool Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>No Seri</th>
                <th>No Reg</th>
                <th>Part Name</th>
                <th>Part Number</th>
                <th>NG Date</th>
                <th>Dept.</th>
                <th>Category NG</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ngs as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ strtoupper($item->tool_name) }}</td>
                <td>{{ $item->type ?? '-' }}</td>
                <td>{{ $item->size ?? '-' }}</td>
                <td>{{ $item->number_seri ?? '-' }}</td>
                <td>{{ $item->number_reg ?? '-' }}</td>
                <td>{{ $item->part_name ?? '-' }}</td>
                <td>{{ $item->part_number ?? '-' }}</td>
                <td>{{ $item->ng_date ? $item->ng_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->dept }}</td>
                <td>{{ $item->category_ng }}</td>
                <td>{{ $item->note ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="12" style="text-align: center; padding: 20px;">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>