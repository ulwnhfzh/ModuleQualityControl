@extends('layouts.app')

@section('content')
<div class="mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Persediaan QA</p>
            <h1 class="mt-1 text-lg font-bold text-slate-900 sm:text-xl">Laporan Stock Alat Ukur QA Plant 2</h1>
            <p class="mt-1 text-sm text-slate-500">Laporan bulanan stok awal, barang masuk, barang keluar, dan stok akhir.</p>
        </div>
        <form method="GET" action="{{ route('stock.summary') }}" class="grid grid-cols-2 gap-2 sm:flex sm:items-end">
            <label class="text-xs font-semibold text-slate-600">Tahun
                <select name="tahun" class="mt-1 block w-full rounded-lg border-slate-300 text-sm sm:w-auto">
                    @for($year = date('Y'); $year >= date('Y') - 12; $year--)
                        <option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>
                    @endfor
                </select>
            </label>
            <label class="text-xs font-semibold text-slate-600">Kategori
                <select name="kategori" class="mt-1 block w-full rounded-lg border-slate-300 text-sm sm:w-auto">
                    @foreach(['Small Tool', 'Machine', 'Jig'] as $option)
                        <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            <button class="col-span-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 sm:col-span-1">Tampilkan</button>
        </form>
    </div>
</div>

<section class="mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-5">
    <h2 class="mb-3 text-sm font-bold text-slate-800">Grafik Pergerakan Stok — {{ $kategori }} {{ $tahun }}</h2>
    <div class="relative h-72 sm:h-96">
        <canvas id="stockMonthlyChart" aria-label="Grafik rekap stok bulanan"></canvas>
    </div>
</section>

<section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-3">
        <h2 class="text-sm font-bold text-slate-800">Tabel Stock Bulanan</h2>
        <p class="mt-1 text-xs text-slate-500">Stock Akhir bulan menjadi Stock Awal bulan berikutnya.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px] border-collapse text-sm text-slate-800">
            <thead class="bg-slate-300">
                <tr>
                    <th class="border border-slate-400 px-3 py-3 text-left">Bulan</th>
                    @foreach($rows as $row)
                        <th class="border border-slate-400 px-3 py-3 text-center">{{ strtoupper($row['label']) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr class="bg-slate-100 hover:bg-slate-200">
                    <th class="border border-slate-300 px-3 py-3 text-left font-semibold">STOCK</th>
                    @foreach($rows as $row)<td class="border border-slate-300 px-3 py-3 text-center tabular-nums">{{ number_format($row['opening']) }}</td>@endforeach
                </tr>
                <tr class="bg-white hover:bg-slate-100">
                    <th class="border border-slate-300 px-3 py-3 text-left font-semibold">IN</th>
                    @foreach($rows as $row)<td class="border border-slate-300 px-3 py-3 text-center tabular-nums">{{ number_format($row['incoming']) }}</td>@endforeach
                </tr>
                <tr class="bg-slate-100 hover:bg-slate-200">
                    <th class="border border-slate-300 px-3 py-3 text-left font-semibold">OUT</th>
                    @foreach($rows as $row)<td class="border border-slate-300 px-3 py-3 text-center tabular-nums">{{ number_format($row['outgoing']) }}</td>@endforeach
                </tr>
                <tr class="bg-slate-200 font-semibold hover:bg-slate-300">
                    <th class="border border-slate-300 px-3 py-3 text-left">STOCK AKHIR</th>
                    @foreach($rows as $row)<td class="border border-slate-300 px-3 py-3 text-center tabular-nums">{{ number_format($row['closing']) }}</td>@endforeach
                </tr>
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const monthlyStock = @json(array_values($rows));
    const chartElement = document.getElementById('stockMonthlyChart');

    new Chart(chartElement, {
        data: {
            labels: monthlyStock.map((month) => month.label),
            datasets: [
                {
                    type: 'line',
                    label: 'Stock Akhir',
                    data: monthlyStock.map((month) => month.closing),
                    yAxisID: 'stock',
                    borderColor: '#059669',
                    backgroundColor: '#059669',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.25,
                    order: 0,
                },
                {
                    type: 'bar',
                    label: 'IN',
                    data: monthlyStock.map((month) => month.incoming),
                    yAxisID: 'movement',
                    backgroundColor: '#34d399',
                    borderRadius: 4,
                    order: 1,
                },
                {
                    type: 'bar',
                    label: 'OUT',
                    data: monthlyStock.map((month) => month.outgoing),
                    yAxisID: 'movement',
                    backgroundColor: '#f59e0b',
                    borderRadius: 4,
                    order: 2,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${new Intl.NumberFormat('id-ID').format(context.raw)}` } },
            },
            scales: {
                x: { grid: { display: false } },
                stock: { type: 'linear', position: 'left', beginAtZero: false, title: { display: true, text: 'Jumlah stok' } },
                movement: { type: 'linear', position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'IN / OUT' } },
            },
        },
    });
</script>
@endsection
