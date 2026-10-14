@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard User</h1>
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

    {{-- ===== BANNER MODE USER ===== --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800">Mode User (View Only)</p>
            <p class="text-xs text-blue-600 mt-0.5">Anda dapat melihat data, namun tidak dapat menambah, mengubah, atau menghapus data.</p>
        </div>
    </div>

    {{-- ===== SECTION 1: KPI ===== --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-slate-800 text-lg">📊 KPI {{ $kpi['bulanLabel'] }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">Key Performance Indicator bulan ini</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- KPI 1: Progress Kalibrasi --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Progress Kalibrasi</p>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-4xl font-bold text-blue-700">{{ $kpi['progressKalibrasi'] }}%</p>
                </div>
                <p class="text-xs text-blue-600 mt-1">
                    {{ $kpi['totalKalibrasiBulanIni'] }} dari {{ $kpi['totalAlatKalibrasi'] }} alat
                </p>
                <div class="w-full bg-blue-200 rounded-full h-2 mt-3">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                         style="width: {{ min($kpi['progressKalibrasi'], 100) }}%"></div>
                </div>
            </div>

            {{-- KPI 2: Alat Terlambat --}}
            <div class="bg-gradient-to-br from-rose-50 to-rose-100 rounded-lg p-4 border border-rose-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-rose-700 uppercase tracking-wide">Alat Terlambat</p>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-4xl font-bold text-rose-700">{{ $kpi['jumlahTerlambat'] }}</p>
                <p class="text-xs text-rose-600 mt-1">
                    @if($kpi['jumlahTerlambat'] > 0)
                        Butuh perhatian segera!
                    @else
                        Semua alat terkalibrasi tepat waktu ✅
                    @endif
                </p>
            </div>

            {{-- KPI 3: Aktivitas Minggu Ini --}}
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg p-4 border border-emerald-200">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Aktivitas Minggu Ini</p>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-4xl font-bold text-emerald-700">{{ $kpi['aktivitasMingguIni'] }}</p>
                    @php $perubahan = $kpi['perubahanAktivitas']; @endphp
                    @if($perubahan > 0)
                        <span class="text-sm font-semibold text-emerald-600">↑ {{ $perubahan }}%</span>
                    @elseif($perubahan < 0)
                        <span class="text-sm font-semibold text-rose-600">↓ {{ abs($perubahan) }}%</span>
                    @else
                        <span class="text-sm font-semibold text-gray-500">—</span>
                    @endif
                </div>
                <p class="text-xs text-emerald-600 mt-1">
                    Minggu lalu: {{ $kpi['aktivitasMingguLalu'] }} aktivitas
                </p>
            </div>
        </div>
    </div>

    {{-- ===== SECTION 2: NOTIFIKASI ===== --}}
    @if($kpi['jumlahTerlambat'] > 0 || $kpi['jumlahJatuhTempo7'] > 0 || $kpi['jumlahJatuhTempo30'] > 0)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-amber-50 px-4 py-3 border-b border-amber-200 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <h3 class="font-bold text-amber-800 text-sm">⚠️ Perhatian! Ada Alat yang Butuh Tindakan</h3>
        </div>

        <div class="p-4 space-y-3">

            {{-- Notifikasi Merah: Terlambat --}}
            @if($kpi['jumlahTerlambat'] > 0)
            <div class="bg-rose-50 border-l-4 border-rose-500 rounded-r-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔴</span>
                        <span class="font-semibold text-rose-800 text-sm">
                            {{ $kpi['jumlahTerlambat'] }} alat TERLAMBAT kalibrasi
                        </span>
                    </div>
                    <span class="text-xs text-rose-600">Butuh tindakan segera</span>
                </div>
                <div class="pl-7 space-y-1">
                    @foreach($kpi['alatTerlambat']->take(3) as $alat)
                    <div class="text-xs text-rose-700">
                        • <strong>{{ $alat->tool_name }}</strong>
                        ({{ $alat->machine_model ?? '-' }}) —
                        @php $sisa = (int) $alat->sisa_hari; @endphp
                        @if($sisa < 0)
                            TERLAMBAT {{ abs($sisa) }} hari
                        @else
                            {{ $sisa }} hari lagi
                        @endif
                    </div>
                    @endforeach
                    @if($kpi['alatTerlambat']->count() > 3)
                    <div class="text-xs text-rose-600 font-semibold">... dan {{ $kpi['alatTerlambat']->count() - 3 }} alat lainnya</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Notifikasi Oranye: ≤ 7 hari --}}
            @if($kpi['jumlahJatuhTempo7'] > 0)
            <div class="bg-orange-50 border-l-4 border-orange-500 rounded-r-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🟠</span>
                        <span class="font-semibold text-orange-800 text-sm">
                            {{ $kpi['jumlahJatuhTempo7'] }} alat jatuh tempo ≤ 7 hari
                        </span>
                    </div>
                    <span class="text-xs text-orange-600">Segera siapkan</span>
                </div>
                <div class="pl-7 space-y-1">
                    @foreach($kpi['alatJatuhTempo7Hari']->take(3) as $alat)
                    <div class="text-xs text-orange-700">
                        • <strong>{{ $alat->tool_name }}</strong>
                        ({{ $alat->machine_model ?? '-' }}) —
                        @php $sisa = (int) $alat->sisa_hari; @endphp
                        @if($sisa == 0)
                            HARI INI
                        @elseif($sisa == 1)
                            Besok
                        @else
                            {{ $sisa }} hari lagi
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Notifikasi Biru: ≤ 30 hari --}}
            @if($kpi['jumlahJatuhTempo30'] > 0)
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔵</span>
                        <span class="font-semibold text-blue-800 text-sm">
                            {{ $kpi['jumlahJatuhTempo30'] }} alat jatuh tempo dalam 30 hari
                        </span>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ===== SECTION 3: SHORTCUT (tanpa tombol tambah, hanya lihat) ===== --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <h3 class="font-semibold text-slate-800 text-sm mb-3">🚀 Shortcut (Lihat Data)</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <a href="{{ route('tools.index') }}"
               class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-3 text-center transition">
                <div class="text-blue-600 font-semibold text-xs">📋 Daftar Induk</div>
            </a>
            <a href="{{ route('verifikasi.index') }}"
               class="bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg p-3 text-center transition">
                <div class="text-emerald-600 font-semibold text-xs">✅ Verifikasi</div>
            </a>
            <a href="{{ route('internal.index') }}"
               class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-3 text-center transition">
                <div class="text-purple-600 font-semibold text-xs">🔧 Internal</div>
            </a>
            <a href="{{ route('mesin.index') }}"
               class="bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg p-3 text-center transition">
                <div class="text-rose-600 font-semibold text-xs">⚙️ Mesin</div>
            </a>
        </div>
    </div>

    {{-- ===== SECTION 4: BREAKDOWN KATEGORI ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-800 text-sm mb-3">🗂️ Breakdown Alat per Kategori</h3>
            <div class="space-y-2">
                @forelse($kpi['breakdownKategori'] as $kat)
                <div class="flex items-center justify-between border-b border-gray-100 py-2">
                    <span class="text-sm text-gray-700 font-medium">{{ $kat->category }}</span>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">{{ $kat->jumlah }}</span>
                </div>
                @empty
                <div class="text-center text-gray-400 text-sm py-4">Belum ada data</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h3 class="font-semibold text-slate-800 text-sm mb-3">📌 Ringkasan Data {{ $tahun }}</h3>
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-blue-50 rounded-lg p-2 text-center">
                    <p class="text-2xl font-bold text-blue-700">{{ $totalTools }}</p>
                    <p class="text-[10px] text-blue-600 uppercase">Tools</p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-2 text-center">
                    <p class="text-2xl font-bold text-emerald-700">{{ $totalVerifikasi }}</p>
                    <p class="text-[10px] text-emerald-600 uppercase">Verifikasi</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-2 text-center">
                    <p class="text-2xl font-bold text-purple-700">{{ $totalInternal }}</p>
                    <p class="text-[10px] text-purple-600 uppercase">Internal</p>
                </div>
                <div class="bg-amber-50 rounded-lg p-2 text-center">
                    <p class="text-2xl font-bold text-amber-700">{{ $totalEksternal }}</p>
                    <p class="text-[10px] text-amber-600 uppercase">Eksternal</p>
                </div>
                <div class="bg-rose-50 rounded-lg p-2 text-center col-span-2">
                    <p class="text-2xl font-bold text-rose-700">{{ $totalMesin }}</p>
                    <p class="text-[10px] text-rose-600 uppercase">Mesin</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== SECTION 5: INSIGHT OTOMATIS ===== --}}
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
        <div class="flex items-start gap-3">
            <div class="bg-amber-100 text-amber-600 rounded-full p-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-800">💡 Insight Otomatis</h4>
                <ul class="text-xs text-slate-600 mt-1 space-y-1 list-disc list-inside">
                    <li>Progress kalibrasi bulan ini: <strong>{{ $kpi['progressKalibrasi'] }}%</strong> ({{ $kpi['totalKalibrasiBulanIni'] }} dari {{ $kpi['totalAlatKalibrasi'] }} alat).</li>
                    <li>Aktivitas minggu ini: <strong>{{ $kpi['aktivitasMingguIni'] }}</strong>
                        @if($kpi['perubahanAktivitas'] > 0)
                            (naik {{ $kpi['perubahanAktivitas'] }}% dari minggu lalu)
                        @elseif($kpi['perubahanAktivitas'] < 0)
                            (turun {{ abs($kpi['perubahanAktivitas']) }}% dari minggu lalu)
                        @endif
                    </li>
                    @if($kpi['jumlahTerlambat'] > 0)
                        <li class="text-rose-600 font-medium">⚠️ Perhatian: {{ $kpi['jumlahTerlambat'] }} alat <strong>terlambat kalibrasi</strong>.</li>
                    @endif
                    @if($kpi['jumlahJatuhTempo7'] > 0)
                        <li class="text-orange-600 font-medium">🟠 {{ $kpi['jumlahJatuhTempo7'] }} alat jatuh tempo dalam 7 hari ke depan.</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection