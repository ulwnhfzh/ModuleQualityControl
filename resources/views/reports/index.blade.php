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
            <p class="text-xs text-gray-500 mt-0.5">Ringkasan, log aktivitas & grafik QC Measurement Tools</p>
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
        <a href="{{ route('reports.index', ['tab' => 'ringkasan', 'tahun' => $tahun]) }}"
           class="px-4 py-2 text-sm font-semibold transition whitespace-nowrap
                  {{ $tab == 'ringkasan' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-800' }}">
            📊 Ringkasan
        </a>
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

{{-- ===== TAB: RINGKASAN ===== --}}
@if($tab == 'ringkasan')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Daftar Induk</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalTools }}</p>
            </div>
            <div class="bg-blue-100 text-blue-600 rounded-lg p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Verifikasi</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalVerifikasi }}</p>
            </div>
            <div class="bg-emerald-100 text-emerald-600 rounded-lg p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Internal</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalInternal }}</p>
            </div>
            <div class="bg-purple-100 text-purple-600 rounded-lg p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Eksternal</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalEksternal }}</p>
            </div>
            <div class="bg-amber-100 text-amber-600 rounded-lg p-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Mesin</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalMesin }}</p>
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

<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="font-semibold text-gray-800 text-sm">Detail Kategori Daftar Induk</h3>
    </div>
    <table class="w-full text-xs text-left">
        <thead class="bg-gray-50 text-gray-700 uppercase">
            <tr>
                <th class="px-4 py-3 font-semibold">Kategori</th>
                <th class="px-4 py-3 font-semibold text-center">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2.5">Small Tool</td>
                <td class="px-4 py-2.5 text-center font-semibold">{{ $toolsSmallTool }}</td>
            </tr>
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-2.5">Machine</td>
                <td class="px-4 py-2.5 text-center font-semibold">{{ $toolsMachine }}</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5">Jig</td>
                <td class="px-4 py-2.5 text-center font-semibold">{{ $toolsJig }}</td>
            </tr>
        </tbody>
    </table>
</div>
@endif

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
           class="flex items-center gap-2.5 px-3 py-2 text-xs text-white bg-green-600 rounded-lg hover:bg-green-700 transition font-semibold">
            Excel
        </a>
        <a href="{{ route('reports.export.log.pdf', request()->query()) }}"
           class="flex items-center gap-2.5 px-3 py-2 text-xs text-white bg-red-600 rounded-lg hover:bg-red-700 transition font-semibold">
            PDF
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

{{-- ===== TAB: GRAFIK (PARETO) - MANDIRI ===== --}}
@if($tab == 'grafik')

{{-- PARETO 1: TOTAL DATA PER FITUR --}}
<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm mb-4">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base">📊 Total Data per Fitur</h3>
            <p class="text-xs text-slate-500 mt-0.5">Distribusi jumlah data berdasarkan fitur QC</p>
        </div>
        <select id="filterTahunFitur" class="border border-gray-300 rounded text-xs px-2 py-1.5 focus:ring-2 focus:ring-blue-500">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
            @endfor
        </select>
    </div>
    <div class="h-96">
        <canvas id="chartParetoFitur"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- PARETO 2: DISTRIBUSI KATEGORI ALAT --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">📊 Distribusi Kategori Alat</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kategori dari Daftar Induk</p>
            </div>
            <select id="filterTahunKategori" class="border border-gray-300 rounded text-xs px-2 py-1.5 focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="h-80">
            <canvas id="chartParetoKategori"></canvas>
        </div>
    </div>

    {{-- PARETO 3: AKTIVITAS PER BULAN --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">📊 Aktivitas per Bulan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Total aktivitas kalibrasi & verifikasi per bulan</p>
            </div>
            <select id="filterTahunBulan" class="border border-gray-300 rounded text-xs px-2 py-1.5 focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="h-80">
            <canvas id="chartParetoBulan"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4">

    {{-- PARETO 4: DISTRIBUSI DATA PER DEPARTEMEN --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">📊 Distribusi Data per Departemen</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jumlah data dari tabel Verifikasi per departemen</p>
            </div>
            <select id="filterTahunDepartemen" class="border border-gray-300 rounded text-xs px-2 py-1.5 focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="h-96">
            <canvas id="chartParetoDepartemen"></canvas>
        </div>
    </div>
</div>

{{-- Chart.js + Plugin --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.register(ChartDataLabels);

    // ========================================
    // FUNGSI UTAMA: PARETO CHART
    // ========================================
    function buatParetoChart(canvasId, labels, values, warnaBar = '#3b82f6') {
        const combined = labels.map((label, i) => ({ label, value: values[i] }));
        combined.sort((a, b) => b.value - a.value);
        const sortedLabels = combined.map(c => c.label);
        const sortedValues = combined.map(c => c.value);
        const total = sortedValues.reduce((a, b) => a + b, 0);
        let cumulative = 0;
        const cumulativePercentages = sortedValues.map(v => {
            cumulative += v;
            return total > 0 ? (cumulative / total) * 100 : 0;
        });
        const maxValue = Math.max(...sortedValues, 1);
        const maxY = Math.ceil(maxValue * 1.2);

        const ctx = document.getElementById(canvasId).getContext('2d');
        
        // Hapus chart lama jika ada (Anti Memory Leak saat ganti filter)
        const existingChart = Chart.getChart(canvasId);
        if (existingChart) existingChart.destroy();

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedLabels,
                datasets: [
                    {
                        type: 'bar', label: 'Jumlah', data: sortedValues, backgroundColor: warnaBar,
                        borderColor: warnaBar, borderWidth: 0, borderRadius: 6, borderSkipped: false, yAxisID: 'y', order: 2,
                        datalabels: { anchor: 'end', align: 'end', color: '#1e40af', font: { size: 11, weight: 'bold' },
                            formatter: v => v > 0 ? v : '', offset: 4 }
                    },
                    {
                        type: 'line', label: '% Kumulatif', data: cumulativePercentages, borderColor: '#dc2626',
                        backgroundColor: '#dc2626', borderWidth: 2.5, pointBackgroundColor: '#dc2626', pointBorderColor: '#fff',
                        pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 8, tension: 0.2, yAxisID: 'y1', order: 1,
                        datalabels: { anchor: 'end', align: 'top', color: '#dc2626', font: { size: 10, weight: 'bold' },
                            formatter: v => v.toFixed(0) + '%', offset: 6 }
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                layout: { padding: { top: 30, right: 20, left: 10, bottom: 10 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top', align: 'end',
                        labels: { boxWidth: 14, boxHeight: 14, padding: 12, font: { size: 11, weight: '600' }, usePointStyle: true }
                    },
                    tooltip: { backgroundColor: '#1e293b', padding: 12, titleFont: { size: 12, weight: 'bold' }, bodyFont: { size: 11 },
                        callbacks: { label: function(context) {
                            if (context.dataset.type === 'line') return '  ' + context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            return '  ' + context.dataset.label + ': ' + context.parsed.y;
                        }}
                    }
                },
                scales: {
                    y: { beginAtZero: true, max: maxY, position: 'left',
                        title: { display: true, text: 'Jumlah', font: { size: 11, weight: 'bold' }, color: '#1e40af' },
                        ticks: { stepSize: maxY <= 10 ? 1 : Math.ceil(maxY / 10), font: { size: 10 }, color: '#1e40af' },
                        grid: { color: 'rgba(30, 64, 175, 0.08)', drawBorder: false }
                    },
                    y1: { beginAtZero: true, max: 100, position: 'right',
                        title: { display: true, text: '% Kumulatif', font: { size: 11, weight: 'bold' }, color: '#dc2626' },
                        ticks: { stepSize: 20, font: { size: 10 }, color: '#dc2626', callback: v => v + '%' },
                        grid: { display: false, drawBorder: false }
                    },
                    x: { ticks: { font: { size: 10, weight: '600' }, maxRotation: 0, minRotation: 0, autoSkip: false }, grid: { display: false, drawBorder: false } }
                }
            }
        });
    }

    // ========================================
    // FETCH DATA & RENDER (VIA AJAX)
    // ========================================
    function fetchAndRender(url, canvasId, warna) {
        fetch(url)
            .then(res => res.json())
            .then(data => {
                buatParetoChart(canvasId, data.labels, data.values, warna);
            })
            .catch(err => console.error("Gagal load data grafik:", err));
    }

    // Initial Load
    const currentYear = '{{ $tahun }}';
    fetchAndRender(`{{ route('reports.api.pareto.fitur') }}?tahun=${currentYear}`, 'chartParetoFitur', '#3b82f6');
    fetchAndRender(`{{ route('reports.api.pareto.kategori') }}?tahun=${currentYear}`, 'chartParetoKategori', '#10b981');
    fetchAndRender(`{{ route('reports.api.pareto.bulanan') }}?tahun=${currentYear}`, 'chartParetoBulan', '#8b5cf6');
    fetchAndRender(`{{ route('reports.api.pareto.departemen') }}?tahun=${currentYear}`, 'chartParetoDepartemen', '#f59e0b');

    // Event Listener Filter Mandiri
    document.getElementById('filterTahunFitur').addEventListener('change', function() {
        fetchAndRender(`{{ route('reports.api.pareto.fitur') }}?tahun=${this.value}`, 'chartParetoFitur', '#3b82f6');
    });
    document.getElementById('filterTahunKategori').addEventListener('change', function() {
        fetchAndRender(`{{ route('reports.api.pareto.kategori') }}?tahun=${this.value}`, 'chartParetoKategori', '#10b981');
    });
    document.getElementById('filterTahunBulan').addEventListener('change', function() {
        fetchAndRender(`{{ route('reports.api.pareto.bulanan') }}?tahun=${this.value}`, 'chartParetoBulan', '#8b5cf6');
    });
    document.getElementById('filterTahunDepartemen').addEventListener('change', function() {
        fetchAndRender(`{{ route('reports.api.pareto.departemen') }}?tahun=${this.value}`, 'chartParetoDepartemen', '#f59e0b');
    });

});
</script>
@endif

@endsection