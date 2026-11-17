<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Master Kalibrator - {{ $tahun }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; color: #1e293b; }
        h1 { margin: 0 0 4px; font-size: 14px; }
        p { margin: 0 0 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 4px 3px; text-align: center; }
        th { background: #1e40af; color: #fff; }
        td.text-left { text-align: left; }
        tr:nth-child(even) td { background: #f1f5f9; }
    </style>
</head>
<body>
    <h1>Master Kalibrator</h1>
    <p>Tahun {{ $tahun }} &middot; {{ $schedules->count() }} alat</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tool Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Number Seri</th>
                <th>Number Reg</th>
                <th>Part Name</th>
                <th>Part Number</th>
                @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $month)
                    <th>{{ $month }}</th>
                @endforeach
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-left">{{ $item->tool_name }}</td>
                    <td>{{ $item->type ?: '-' }}</td>
                    <td>{{ $item->size ?: '-' }}</td>
                    <td>{{ $item->number_seri ?: '-' }}</td>
                    <td>{{ $item->number_reg ?: '-' }}</td>
                    <td>{{ $item->part_name ?: '-' }}</td>
                    <td>{{ $item->part_number ?: '-' }}</td>
                    @foreach(['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $month)
                        <td>{{ $item->{$month} ?? '-' }}</td>
                    @endforeach
                    <td class="text-left">{{ $item->note ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="21">Belum ada data Master Kalibrator untuk tahun {{ $tahun }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
