@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Ringkasan data QC Measurement Tools tahun {{ $tahun }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-500 font-medium">Tahun:</span>
            <form method="GET" action="{{ route('dashboard.index') }}">
                <select name="tahun" onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer">
                    @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    {{-- INFO MODE ADMIN --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-emerald-800">Mode Administrator</p>
            <p class="text-xs text-emerald-600 mt-0.5">Anda dapat mengelola seluruh data: tambah, edit, dan hapus.</p>
        </div>
    </div>

    {{-- KARTU RINGKASAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        @php
            $cards = [
                ['label' => 'Daftar Induk', 'value' => $totalTools, 'color' => 'blue', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Verifikasi', 'value' => $totalVerifikasi, 'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Internal', 'value' => $totalInternal, 'color' => 'purple', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ['label' => 'Eksternal', 'value' => $totalEksternal, 'color' => 'amber', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
                ['label' => 'Mesin', 'value' => $totalMesin, 'color' => 'rose', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $card['value'] }}</p>
                </div>
                <div class="bg-{{ $card['color'] }}-100 text-{{ $card['color'] }}-600 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                    </svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ====== 5 KARTU GRAFIK PER FITUR ====== --}}
    @php
        $fiturGrafik = [
            ['id' => 'tools',       'title' => 'Daftar Induk',              'color' => '#3b82f6', 'bulanan' => array_fill(0, 12, 0), 'tahunan' => $toolsPerTahun],
            ['id' => 'verifikasi',  'title' => 'Jadwal Verifikasi',         'color' => '#10b981', 'bulanan' => $verifikasiPerBulan, 'tahunan' => $verifikasiPerTahun],
            ['id' => 'internal',    'title' => 'Kalibrasi Internal',        'color' => '#8b5cf6', 'bulanan' => $internalPerBulan,   'tahunan' => $internalPerTahun],
            ['id' => 'eksternal',   'title' => 'Kalibrasi Eksternal (Small Tool)', 'color' => '#f59e0b', 'bulanan' => $eksternalPerBulan, 'tahunan' => $eksternalPerTahun],
            ['id' => 'mesin',       'title' => 'Kalibrasi Eksternal (Mesin Alat Ukur)', 'color' => '#f43f5e', 'bulanan' => $mesinPerBulan, 'tahunan' => $mesinPerTahun],
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($fiturGrafik as $f)
        @php
            $totalTahunan = array_sum($f['bulanan']);
        @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-800">{{ $f['title'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tren data tahun {{ $tahun }}</p>
                </div>
                <div class="flex gap-1 bg-slate-100 rounded-lg p-1">
                    <button onclick="switchTab('{{ $f['id'] }}', 'bulanan', this)"
                            class="tab-btn-{{ $f['id'] }} px-3 py-1 text-xs font-medium rounded-md bg-white text-slate-800 shadow-sm">
                        Bulanan
                    </button>
                    <button onclick="switchTab('{{ $f['id'] }}', 'tahunan', this)"
                            class="tab-btn-{{ $f['id'] }} px-3 py-1 text-xs font-medium rounded-md text-slate-500">
                        Tahunan
                    </button>
                </div>
            </div>
            <div class="h-64">
                <canvas id="chart-{{ $f['id'] }}"></canvas>
            </div>
            {{-- Info Total Setahun --}}
            <div class="mt-3 text-[11px] text-slate-500 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Total tahun {{ $tahun }}: <strong class="text-slate-700">{{ $totalTahunan }} data</strong>
            </div>
        </div>
        @endforeach
    </div>

    {{-- INSIGHT --}}
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
        <div class="flex items-start gap-3">
            <div class="bg-amber-100 text-amber-600 rounded-full p-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-800">Insight Tahun {{ $tahun }}</h4>
                <ul class="text-xs text-slate-600 mt-1 space-y-1 list-disc list-inside">
                    <li>Total <strong>{{ $totalVerifikasi + $totalInternal + $totalEksternal + $totalMesin }} aktivitas</strong> kalibrasi & verifikasi tercatat tahun ini.</li>
                    @if($totalVerifikasi > 0)
                        <li>Proses <strong>Verifikasi</strong> mendominasi dengan {{ $totalVerifikasi }} data.</li>
                    @endif
                    @if($totalInternal == 0)
                        <li class="text-rose-600 font-medium">Perhatian: Belum ada data <strong>Kalibrasi Internal</strong> untuk tahun ini.</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
const bulanLabels = @json($bulanLabels);
const tahunLabels = @json($tahunLabels);

const fiturData = {
    tools:      { bulanan: Array(12).fill(0), tahunan: @json($toolsPerTahun),      color: '#3b82f6' },
    verifikasi: { bulanan: @json($verifikasiPerBulan), tahunan: @json($verifikasiPerTahun), color: '#10b981' },
    internal:   { bulanan: @json($internalPerBulan),   tahunan: @json($internalPerTahun),   color: '#8b5cf6' },
    eksternal:  { bulanan: @json($eksternalPerBulan),  tahunan: @json($eksternalPerTahun),  color: '#f59e0b' },
    mesin:      { bulanan: @json($mesinPerBulan),      tahunan: @json($mesinPerTahun),      color: '#f43f5e' },
};

const charts = {};

function buildChart(id, mode) {
    if (charts[id]) charts[id].destroy();
    const data = fiturData[id];
    const labels = mode === 'bulanan' ? bulanLabels : tahunLabels;
    const values = mode === 'bulanan' ? data.bulanan : data.tahunan;

    const ctx = document.getElementById('chart-' + id).getContext('2d');
    charts[id] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Data',
                data: values,
                borderColor: data.color,
                backgroundColor: data.color + '20',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: data.color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 10,
                    callbacks: {
                        label: (ctx) => 'Jumlah: ' + ctx.parsed.y + ' data'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 10 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
}

function switchTab(id, mode, btn) {
    document.querySelectorAll('.tab-btn-' + id).forEach(b => {
        b.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
        b.classList.add('text-slate-500');
    });
    btn.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
    btn.classList.remove('text-slate-500');

    buildChart(id, mode);
}

document.addEventListener('DOMContentLoaded', function() {
    Object.keys(fiturData).forEach(id => buildChart(id, 'bulanan'));
});
</script>
@endsection