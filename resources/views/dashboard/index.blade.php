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
                <div class="relative">
                    <select name="tahun" onchange="this.form.submit()"
                            class="appearance-none bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-lg pl-3 pr-10 py-2
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer">
                        @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
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
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Tools</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalTools }}</p>
                </div>
                <div class="bg-blue-100 text-blue-600 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Verifikasi</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalVerifikasi }}</p>
                </div>
                <div class="bg-emerald-100 text-emerald-600 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Internal</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalInternal }}</p>
                </div>
                <div class="bg-purple-100 text-purple-600 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Eksternal</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalEksternal }}</p>
                </div>
                <div class="bg-amber-100 text-amber-600 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Mesin</p>
                    <p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalMesin }}</p>
                </div>
                <div class="bg-rose-100 text-rose-600 rounded-lg p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS 1: Grafik Per Bulan + Kategori --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="mb-4">
                <h3 class="font-semibold text-slate-800">Grafik Per Bulan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jumlah data per bulan tahun {{ $tahun }}</p>
            </div>
            <div class="h-80">
                <canvas id="chartPerBulan"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="mb-4">
                <h3 class="font-semibold text-slate-800">Per Kategori</h3>
                <p class="text-xs text-slate-500 mt-0.5">Distribusi tools</p>
            </div>
            <div class="h-80 flex items-center justify-center">
                <canvas id="chartKategori"></canvas>
            </div>
        </div>
    </div>

    {{-- BARIS 2: Distribusi --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="mb-4">
            <h3 class="font-semibold text-slate-800">Distribusi Semua Data</h3>
            <p class="text-xs text-slate-500 mt-0.5">Jumlah data per kategori tahun {{ $tahun }}</p>
        </div>
        <div class="h-80">
            <canvas id="chartDistribusi"></canvas>
        </div>
    </div>

</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.register(ChartDataLabels);
    const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#f43f5e'];

    // ===== 1. GRAFIK PER BULAN =====
    const ctx1 = document.getElementById('chartPerBulan').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: @json($bulanLabels),
            datasets: [
                {
                    label: 'Verifikasi',
                    data: @json($verifikasiPerBulan),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4, fill: true,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6,
                },
                {
                    label: 'Internal',
                    data: @json($internalPerBulan),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4, fill: true,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6,
                },
                {
                    label: 'Eksternal',
                    data: @json($eksternalPerBulan),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4, fill: true,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6,
                },
                {
                    label: 'Mesin',
                    data: @json($mesinPerBulan),
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    tension: 0.4, fill: true,
                    pointBackgroundColor: '#f43f5e',
                    pointBorderColor: '#fff', pointBorderWidth: 2,
                    pointRadius: 4, pointHoverRadius: 6,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top', align: 'end',
                    labels: { boxWidth: 12, boxHeight: 12, padding: 15, font: { size: 12, family: 'Inter', weight: '500' } }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + ' data';
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    title: {
                        display: true,
                        text: 'Jumlah Data',
                        font: { size: 11, weight: 'bold' },
                        color: '#64748b'
                    }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false },
                    title: {
                        display: true,
                        text: 'Bulan ({{ $tahun }})',
                        font: { size: 11, weight: 'bold' },
                        color: '#64748b'
                    }
                }
            }
        }
    });

    // ===== 2. GRAFIK KATEGORI =====
    const ctx2 = document.getElementById('chartKategori').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($kategoriData)),
            datasets: [{
                data: @json(array_values($kategoriData)),
                backgroundColor: ['#10b981', '#3b82f6'],
                borderWidth: 0, hoverOffset: 8
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, boxHeight: 12, padding: 15, font: { size: 12, family: 'Inter', weight: '500' } }
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                },
                datalabels: {
                    color: '#ffffff',
                    font: { size: 13, weight: 'bold' },
                    formatter: function(value, context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        if (value === 0) return '';
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
                        return percentage + '%';
                    }
                }
            }
        }
    });

    // ===== 3. GRAFIK DISTRIBUSI (VERTICAL BAR) =====
    const ctx3 = document.getElementById('chartDistribusi').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: @json(array_keys($distribusi)),
            datasets: [{
                label: 'Jumlah Data',
                data: @json(array_values($distribusi)),
                backgroundColor: colors,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 60,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            return 'Jumlah: ' + context.parsed.y + ' data';
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#1e293b',
                    font: { size: 13, weight: 'bold' },
                    formatter: function(value) {
                        return value;
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    title: {
                        display: true,
                        text: 'Jumlah Data',
                        font: { size: 11, weight: 'bold' },
                        color: '#64748b'
                    }
                },
                x: {
                    ticks: { font: { size: 12, weight: '500' } },
                    grid: { display: false },
                    title: {
                        display: true,
                        text: 'Kategori',
                        font: { size: 11, weight: 'bold' },
                        color: '#64748b'
                    }
                }
            }
        }
    });
});
</script>
@endsection