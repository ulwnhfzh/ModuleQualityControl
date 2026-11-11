<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1e293b; }
        h1 { margin: 0 0 4px; font-size: 16px; }
        p { margin: 0 0 14px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 4px; text-align: left; }
        th { background: #1e3a8a; color: #fff; font-weight: bold; }
        tr:nth-child(even) td { background: #f1f5f9; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $company }} &middot; Tahun {{ $tahun }} &middot; {{ $schedules->count() }} jadwal</p>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                @foreach($columns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @foreach($columns as $column)
                        @php($value = $schedule->{$column['key']})
                        <td>{{ $value instanceof \DateTimeInterface ? $value->format('d/m/Y') : ($value ?: '-') }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}">Tidak ada jadwal untuk tahun {{ $tahun }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
