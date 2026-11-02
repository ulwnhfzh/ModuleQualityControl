@extends('layouts.app')

@section('content')

@if(session('info'))
<div class="bg-blue-100 border border-blue-400 text-blue-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('info') }}
</div>
@endif

{{-- ===== HEADER ===== --}}
<div class="feature-toolbar bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-4 py-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-base font-bold text-gray-800">Reports</h1>
            <p class="text-xs text-gray-500 mt-0.5">Grafik operasional dan log aktivitas QC Measurement Tools</p>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="text-[10px] sm:text-xs font-semibold text-gray-500 whitespace-nowrap">TAHUN</label>
            <select name="tahun" onchange="this.form.submit()"
                    class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1.5 sm:px-3 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>
</div>

{{-- ===== TAB NAVIGATION ===== --}}
<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-4 py-2">
    <div class="flex gap-2 border-b border-gray-200 overflow-x-auto">
        <a href="{{ route('reports.index', ['tab' => 'log', 'tahun' => $tahun]) }}"
           class="px-4 py-2 text-sm font-semibold transition whitespace-nowrap
                  {{ $tab == 'log' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
            📋 Log Aktivitas
        </a>
        <a href="{{ route('reports.index', ['tab' => 'grafik', 'tahun' => $tahun]) }}"
           class="px-4 py-2 text-sm font-semibold transition whitespace-nowrap
                  {{ $tab == 'grafik' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
            📈 Grafik
        </a>
    </div>
</div>

{{-- ===== TAB: LOG AKTIVITAS ===== --}}
@if($tab == 'log')
<div class="bg-white rounded-lg shadow-sm mb-3 p-4">
    <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <input type="hidden" name="tab" value="log">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div>
            <label class="block text-[10px] font-semibold text-gray-500 mb-1">TANGGAL</label>
            <x-date-entry name="tanggal" :value="request('tanggal')" input-class="w-full border border-gray-300 rounded text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <div>
            <label class="block text-[10px] font-semibold text-gray-500 mb-1">USER</label>
            <select name="user_id" class="w-full border border-gray-300 rounded text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-semibold text-gray-500 mb-1">JENIS DATA</label>
            <select name="jenis" class="w-full border border-gray-300 rounded text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua</option>
                <option value="tools" {{ request('jenis') == 'tools' ? 'selected' : '' }}>Daftar Induk</option>
                <option value="verifikasi" {{ request('jenis') == 'verifikasi' ? 'selected' : '' }}>Verifikasi</option>
                <option value="internal" {{ request('jenis') == 'internal' ? 'selected' : '' }}>Internal</option>
                <option value="eksternal" {{ request('jenis') == 'eksternal' ? 'selected' : '' }}>Eksternal Small</option>
                <option value="mesin" {{ request('jenis') == 'mesin' ? 'selected' : '' }}>Eksternal Mesin</option>
                <option value="user" {{ request('jenis') == 'user' ? 'selected' : '' }}>User</option>
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-semibold text-gray-500 mb-1">AKSI</label>
            <select name="aksi" class="w-full border border-gray-300 rounded text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua</option>
                <option value="CREATE" {{ request('aksi') == 'CREATE' ? 'selected' : '' }}>Tambah</option>
                <option value="UPDATE" {{ request('aksi') == 'UPDATE' ? 'selected' : '' }}>Edit</option>
                <option value="DELETE" {{ request('aksi') == 'DELETE' ? 'selected' : '' }}>Hapus</option>
                <option value="LOGIN" {{ request('aksi') == 'LOGIN' ? 'selected' : '' }}>Login</option>
                <option value="LOGOUT" {{ request('aksi') == 'LOGOUT' ? 'selected' : '' }}>Logout</option>
                <option value="EXPORT" {{ request('aksi') == 'EXPORT' ? 'selected' : '' }}>Export</option>
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-semibold text-gray-500 mb-1">CARI</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan..."
                   class="w-full border border-gray-300 rounded text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition px-3 py-1.5 text-xs">
                🔍 Filter
            </button>
            <a href="{{ route('reports.index', ['tab' => 'log']) }}"
               class="bg-gray-200 text-gray-700 rounded font-semibold hover:bg-gray-300 transition px-3 py-1.5 text-xs">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-sm mb-3 px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
    <div>
        <span class="text-xs text-gray-600">Total: <strong>{{ $logs->total() }}</strong> log</span>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.export.log.excel', request()->query()) }}"
           class="flex items-center gap-2.5 rounded bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
            @include('components.icons.export') Excel
        </a>
        <a href="{{ route('reports.export.log.pdf', request()->query()) }}"
           class="flex items-center gap-2.5 rounded bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700">
            @include('components.icons.export') PDF
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-800 text-white uppercase">
                <tr>
                    <th class="px-3 py-3 text-[11px]">No</th>
                    <th class="px-3 py-3 text-[11px]">Waktu</th>
                    <th class="px-3 py-3 text-[11px]">User</th>
                    <th class="px-3 py-3 text-[11px] text-center">Aksi</th>
                    <th class="px-3 py-3 text-[11px]">Jenis Data</th>
                    <th class="px-3 py-3 text-[11px]">Keterangan</th>
                    <th class="px-3 py-3 text-[11px]">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                <tr class="border-b hover:bg-blue-50">
                    <td class="px-3 py-2 font-semibold">{{ $logs->firstItem() + $index }}</td>
                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-3 py-2 font-medium">{{ $log->user_name ?? '-' }}</td>
                    <td class="px-3 py-2 text-center">
                        @php
                            $badgeColors = [
                                'CREATE' => 'bg-green-100 text-green-700',
                                'UPDATE' => 'bg-amber-100 text-amber-700',
                                'DELETE' => 'bg-red-100 text-red-700',
                                'LOGIN'  => 'bg-blue-100 text-blue-700',
                                'LOGOUT' => 'bg-gray-100 text-gray-700',
                                'EXPORT' => 'bg-purple-100 text-purple-700',
                            ];
                            $color = $badgeColors[$log->aksi] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="{{ $color }} px-2 py-1 rounded text-[10px] font-semibold uppercase">{{ $log->aksi }}</span>
                    </td>
                    <td class="px-3 py-2">{{ $log->label_jenis ?? '-' }}</td>
                    <td class="px-3 py-2 text-gray-700">{{ $log->keterangan ?? '-' }}</td>
                    <td class="px-3 py-2 text-gray-500 text-[10px]">{{ $log->ip_address ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-500 text-sm">Belum ada log aktivitas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endif

{{-- ===== TAB: GRAFIK ===== --}}
@if($tab == 'grafik')
<div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 id="chartTitle" class="text-base font-bold text-slate-800">Total Data per Fitur</h2>
            <p id="chartDescription" class="mt-1 text-xs text-slate-500">Jumlah data tersimpan di setiap fitur QC.</p>
        </div>
        <label class="flex flex-col gap-1 text-xs font-semibold text-slate-600 sm:min-w-64">
            PILIH GRAFIK
            <select id="chartFilter" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm font-normal text-slate-700 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="{{ route('reports.api.pareto.fitur') }}" data-title="Total Data per Fitur" data-description="Jumlah data tersimpan di setiap fitur QC." data-axis="Fitur">Total Data per Fitur</option>
                <option value="{{ route('reports.api.pareto.kategori') }}" data-title="Distribusi Kategori Alat" data-description="Jumlah alat pada setiap kategori Daftar Induk." data-axis="Alat">Distribusi Kategori Alat</option>
                <option value="{{ route('reports.api.pareto.bulanan') }}" data-title="Aktivitas per Bulan" data-description="Total aktivitas kalibrasi dan verifikasi per bulan." data-axis="Aktivitas">Aktivitas per Bulan</option>
                <option value="{{ route('reports.api.pareto.departemen') }}" data-title="Distribusi Data per Departemen" data-description="Jumlah data verifikasi untuk setiap departemen." data-axis="Data">Distribusi Data per Departemen</option>
                <option value="{{ route('reports.api.chart', 'verifikasi-bulanan') }}" data-title="Verifikasi per Bulan" data-description="Jumlah verifikasi tersimpan pada setiap bulan." data-axis="Verifikasi">Verifikasi per Bulan</option>
                <option value="{{ route('reports.api.chart', 'internal-bulanan') }}" data-title="Kalibrasi Internal per Bulan" data-description="Jumlah kalibrasi internal tersimpan pada setiap bulan." data-axis="Alat">Kalibrasi Internal per Bulan</option>
                <option value="{{ route('reports.api.chart', 'eksternal-bulanan') }}" data-title="Kalibrasi Eksternal per Bulan" data-description="Jumlah kalibrasi eksternal Small Tool tersimpan pada setiap bulan." data-axis="Alat">Kalibrasi Eksternal per Bulan</option>
                <option value="{{ route('reports.api.chart', 'mesin-bulanan') }}" data-title="Kalibrasi Mesin per Bulan" data-description="Jumlah kalibrasi mesin tersimpan pada setiap bulan." data-axis="Mesin">Kalibrasi Mesin per Bulan</option>
                <option value="{{ route('reports.api.chart', 'verifikasi-periode') }}" data-title="Hasil Verifikasi Periode" data-description="Distribusi hasil kedua periode verifikasi internal." data-axis="Periode">Hasil Verifikasi Periode</option>
                <option value="{{ route('reports.api.chart', 'kalibrasi-periode') }}" data-title="Hasil Kalibrasi Internal" data-description="Distribusi hasil kalibrasi internal per periode." data-axis="Periode">Hasil Kalibrasi Internal</option>
                <option value="{{ route('reports.api.chart', 'verifikasi-qc2') }}" data-title="Hasil Verifikasi QC2" data-description="Distribusi hasil verifikasi QC2." data-axis="Alat">Hasil Verifikasi QC2</option>
                <option value="{{ route('reports.api.chart', 'data-ng') }}" data-title="Jenis Kerusakan Data NG" data-description="Total tiap jenis kerusakan untuk tahun yang dipilih." data-axis="Jumlah">Jenis Kerusakan Data NG</option>
                <option value="{{ route('reports.api.chart', 'stock-bulanan') }}" data-title="Pergerakan Stock per Bulan" data-description="Jumlah unit yang masuk dan keluar dari persediaan setiap bulan." data-axis="Unit">Pergerakan Stock per Bulan</option>
                <option value="{{ route('reports.api.chart', 'produksi-bulanan') }}" data-title="Data Produksi per Bulan" data-description="Jumlah data bulanan pada Data All Produksi. Tabel ini belum menyimpan tahun." data-axis="Jumlah">Data Produksi per Bulan</option>
                <option value="{{ route('reports.api.chart', 'daily-report') }}" data-title="Laporan Harian per Jenis Data" data-description="Jumlah aktivitas tahunan yang diringkas dalam laporan harian, dikelompokkan berdasarkan fitur." data-axis="Aktivitas">Laporan Harian per Jenis Data</option>
            </select>
        </label>
    </div>
    <div id="chartStatus" class="mb-3 hidden rounded border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800" role="status" aria-live="polite"></div>
    <div id="chartContainer" class="relative h-[360px] sm:h-[440px]">
        <canvas id="reportChart" role="img" aria-label="Grafik laporan QC"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartFilter = document.getElementById('chartFilter');
    const chartCanvas = document.getElementById('reportChart');
    const chartTitle = document.getElementById('chartTitle');
    const chartDescription = document.getElementById('chartDescription');
    const chartStatus = document.getElementById('chartStatus');
    const chartContainer = document.getElementById('chartContainer');
    const yearFilter = document.querySelector('form[action="{{ route('reports.index') }}"] select[name="tahun"]');
    let reportChart = null;
    let activeRequest = 0;

    async function renderSelectedChart() {
        const requestId = ++activeRequest;
        const option = chartFilter.selectedOptions[0];
        chartTitle.textContent = option.dataset.title;
        chartDescription.textContent = option.dataset.description;
        chartStatus.classList.remove('hidden', 'border-rose-200', 'bg-rose-50', 'text-rose-800');
        chartStatus.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-800');
        chartStatus.textContent = 'Memuat grafik...';

        try {
            const url = new URL(option.value, window.location.origin);
            url.searchParams.set('tahun', yearFilter.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error(`Permintaan grafik gagal (HTTP ${response.status}).`);
            }

            const data = await response.json();
            if (requestId !== activeRequest) {
                return;
            }
            if (!Array.isArray(data.labels) || !Array.isArray(data.values) || data.labels.length !== data.values.length) {
                throw new Error('Data grafik dari server tidak valid.');
            }
            if (data.labels.length === 0 || data.values.every(value => Number(value) === 0)) {
                if (reportChart) {
                    reportChart.destroy();
                    reportChart = null;
                }
                chartStatus.classList.remove('hidden', 'border-rose-200', 'bg-rose-50', 'text-rose-800');
                chartStatus.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-800');
                chartStatus.textContent = 'Belum ada data untuk grafik ini pada tahun yang dipilih.';
                return;
            }

            const combined = data.labels.map((label, index) => ({
                label,
                value: Number(data.values[index]) || 0,
            })).sort((a, b) => b.value - a.value);
            const labels = combined.map(item => item.label);
            const values = combined.map(item => item.value);
            const total = values.reduce((sum, value) => sum + value, 0);
            let cumulative = 0;
            const cumulativeValues = values.map(value => {
                cumulative += value;
                return total > 0 ? (cumulative / total) * 100 : 0;
            });

            if (reportChart) {
                reportChart.destroy();
            }

            chartContainer.style.height = '440px';
            Chart.register(ChartDataLabels);

            reportChart = new Chart(chartCanvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        type: 'bar',
                        label: option.dataset.axis,
                        data: values,
                        backgroundColor: '#0070f2',
                        hoverBackgroundColor: '#0054b5',
                        borderRadius: 4,
                        maxBarThickness: 28,
                        yAxisID: 'y',
                        order: 2,
                        datalabels: {
                            display: context => labels.length <= 12 && context.dataset.data[context.dataIndex] > 0,
                            anchor: 'end',
                            align: 'top',
                            color: '#334155',
                            font: { size: 10, weight: 'bold' },
                            formatter: value => value,
                            clip: true,
                        },
                    }, {
                        type: 'line',
                        label: '% Kumulatif',
                        data: cumulativeValues,
                        borderColor: '#dc2626',
                        backgroundColor: '#dc2626',
                        borderWidth: 2,
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        tension: 0.15,
                        yAxisID: 'y1',
                        order: 1,
                        datalabels: { display: false },
                    }],
                },
                options: {
                    indexAxis: 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: { boxWidth: 12, boxHeight: 12, font: { size: 10 }, usePointStyle: true },
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 10,
                            callbacks: {
                                label: context => context.dataset.type === 'line'
                                    ? ` % Kumulatif: ${context.parsed.y.toFixed(1)}%`
                                    : ` ${option.dataset.axis}: ${context.parsed.y}`,
                            },
                        },
                    },
                    scales: {
                        y: { beginAtZero: true, position: 'left', title: { display: true, text: option.dataset.axis }, ticks: { precision: 0 } },
                        y1: { beginAtZero: true, max: 120, position: 'right', title: { display: true, text: '% Kumulatif' }, ticks: { callback: value => value <= 100 ? `${value}%` : '', stepSize: 20 }, grid: { drawOnChartArea: false } },
                        x: { grid: { display: false }, ticks: { maxRotation: labels.length > 8 ? 45 : 0, minRotation: 0 } },
                    },
                },
            });
            chartStatus.classList.add('hidden');
        } catch (error) {
            if (requestId !== activeRequest) {
                return;
            }
            chartStatus.classList.remove('border-blue-200', 'bg-blue-50', 'text-blue-800', 'hidden');
            chartStatus.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
            chartStatus.textContent = error.message || 'Grafik tidak dapat dimuat. Silakan coba lagi.';
        }
    }

    chartFilter.addEventListener('change', renderSelectedChart);
    renderSelectedChart();
});
</script>
@endif
@endsection