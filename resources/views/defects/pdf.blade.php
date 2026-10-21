<!DOCTYPE html>
<html>
<head>
    <title>Data Record Tool All Division</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #d1d5db; font-weight: bold; }
        .text-left { text-align: left; }
    </style>
    @include('pdf.table-theme')
</head>
<body>
    <h3 style="text-align: center; margin-bottom: 5px;">DATA RECORD TOOL ALL DIVISION {{ $tahun }}</h3>
    <p style="text-align: center; margin-top: 0;">Bulan: {{ $bulan }}</p>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">DIVISION</th>
                <th colspan="12">{{ $bulan }}</th>
                <th rowspan="2">TOTAL</th>
            </tr>
            <tr>
                @foreach($jenisKerusakan as $jenis)
                    <th>{{ strtoupper(str_replace('_', ' ', $jenis)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($dataPerDivisi as $div => $data)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ $div }}</td>
                @foreach($jenisKerusakan as $jenis)
                    <td>{{ $data[$jenis] ?: '-' }}</td>
                @endforeach
                <td><strong>{{ $data['total'] ?: '-' }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>