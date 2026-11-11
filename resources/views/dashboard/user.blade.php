@extends('layouts.app')

@section('content')

<div class="dashboard-page flex w-full flex-col gap-5 pb-8">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-700">Quality Control</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Ringkasan alat ukur dan aktivitas operasional.</p>
        </div>
        <div class="flex items-center gap-3">
            <label for="dashboard-year" class="text-sm font-medium text-slate-600">Tahun laporan</label>
            <form method="GET" action="{{ route('dashboard') }}">
                <select name="tahun" onchange="this.form.submit()"
                        id="dashboard-year" aria-label="Pilih tahun laporan" class="bg-white border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg px-3 py-2.5 shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                    @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
    </div>

    {{-- INDIKATOR KINERJA --}}
    <div class="bg-transparent">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-bold text-slate-900 text-lg">Kinerja bulan ini <span class="font-medium text-slate-500">· {{ $kpi['bulanLabel'] }}</span></h2>
                <p class="text-sm text-slate-500 mt-1">Pantau progres kalibrasi, keterlambatan, dan aktivitas tim.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Progres Kalibrasi --}}
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm min-h-[156px]">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Progres kalibrasi</p>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex items-baseline gap-2 mt-3">
                    <p class="text-4xl font-bold tracking-tight text-slate-900">{{ $kpi['progressKalibrasi'] }}<span class="text-2xl text-slate-500">%</span></p>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $kpi['totalKalibrasiBulanIni'] }} dari {{ $kpi['totalAlatKalibrasi'] }} alat
                </p>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-4">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                         style="width: {{ min($kpi['progressKalibrasi'], 100) }}%"></div>
                </div>
            </div>

            {{-- Alat Terlambat --}}
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm min-h-[156px]">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Terlambat kalibrasi</p>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-4xl font-bold tracking-tight {{ $kpi['jumlahTerlambat'] > 0 ? 'text-rose-700' : 'text-slate-900' }} mt-3">{{ $kpi['jumlahTerlambat'] }}</p>
                <p class="text-xs text-slate-500 mt-1">
                    @if($kpi['jumlahTerlambat'] > 0)
                        Memerlukan tindak lanjut segera
                    @else
                        Seluruh alat terkalibrasi sesuai jadwal
                    @endif
                </p>
            </div>

            {{-- Aktivitas Minggu Ini --}}
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm min-h-[156px]">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Aktivitas minggu ini</p>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="flex items-baseline gap-2 mt-3">
                    <p class="text-4xl font-bold tracking-tight text-slate-900">{{ $kpi['aktivitasMingguIni'] }}</p>
                    @php $perubahan = $kpi['perubahanAktivitas']; @endphp
                    @if($perubahan > 0)
                        <span class="text-sm font-semibold text-blue-600">↑ {{ $perubahan }}%</span>
                    @elseif($perubahan < 0)
                        <span class="text-sm font-semibold text-rose-600">↓ {{ abs($perubahan) }}%</span>
                    @else
                        <span class="text-sm font-semibold text-gray-500">—</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Periode sebelumnya: {{ $kpi['aktivitasMingguLalu'] }} aktivitas
                </p>
            </div>
        </div>
    </div>

    {{-- PEMBERITAHUAN --}}
    @if($kpi['jumlahTerlambat'] > 0 || $kpi['jumlahJatuhTempo7'] > 0 || $kpi['jumlahJatuhTempo30'] > 0 || $kpi['jumlahVerificationBelumDikonfirmasi'] > 0)
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-amber-50 px-4 py-3 border-b border-amber-200 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <h3 class="font-bold text-amber-800 text-sm">Pengingat operasional</h3>
        </div>

        <div class="p-4 space-y-3">

            {{-- Terlambat --}}
            @if($kpi['jumlahTerlambat'] > 0)
            <div class="bg-rose-50 border-l-4 border-rose-500 rounded-r-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-rose-800 text-sm">
                            {{ $kpi['jumlahTerlambat'] }} alat terlambat kalibrasi
                        </span>
                    </div>
                    <span class="text-xs text-rose-600">Memerlukan tindakan segera</span>
                </div>
                <div class="pl-0 space-y-1">
                    @foreach($kpi['alatTerlambat']->take(3) as $alat)
                    <div class="text-xs text-rose-700">
                        • <strong>{{ $alat->tool_name }}</strong>
                        ({{ $alat->machine_model ?? '-' }}) —
                        @php $sisa = (int) $alat->sisa_hari; @endphp
                        @if($sisa < 0)
                            Terlambat {{ abs($sisa) }} hari
                        @else
                            {{ $sisa }} hari lagi
                        @endif
                    </div>
                    @endforeach
                    @if($kpi['jumlahTerlambat'] > $kpi['alatTerlambat']->count())
                    <div class="text-xs text-rose-600 font-semibold">... dan {{ $kpi['jumlahTerlambat'] - $kpi['alatTerlambat']->count() }} alat lainnya</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Jatuh Tempo Cepat --}}
            @if($kpi['jumlahJatuhTempo7'] > 0)
            <div class="bg-orange-50 border-l-4 border-orange-500 rounded-r-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-orange-800 text-sm">
                            {{ $kpi['jumlahJatuhTempo7'] }} alat akan jatuh tempo
                        </span>
                    </div>
                    <span class="text-xs text-orange-600">Segera dipersiapkan</span>
                </div>
                <div class="pl-0 space-y-1">
                    @foreach($kpi['alatJatuhTempo7Hari']->take(3) as $alat)
                    <div class="text-xs text-orange-700">
                        • <strong>{{ $alat->tool_name }}</strong>
                        ({{ $alat->machine_model ?? '-' }}) —
                        @php $sisa = (int) $alat->sisa_hari; @endphp
                        @if($sisa == 0)
                            Hari ini
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

            {{-- Jatuh Tempo Standar --}}
            @if($kpi['jumlahJatuhTempo30'] > 0)
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-blue-800 text-sm">
                            {{ $kpi['jumlahJatuhTempo30'] }} alat akan jatuh tempo dalam periode mendatang
                        </span>
                    </div>
                    <a href="{{ route('mesin.index') }}" class="text-xs text-blue-600 underline hover:text-blue-800">
                        Lihat Rincian →
                    </a>
                </div>
            </div>
            @endif

            @if($kpi['jumlahVerificationBelumDikonfirmasi'] > 0)
            <div class="rounded-r-lg border-l-4 border-amber-500 bg-amber-50 p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-sm font-semibold text-amber-900">{{ $kpi['jumlahVerificationBelumDikonfirmasi'] }} verifikasi menunggu konfirmasi</span>
                    <a href="{{ route('verifikasi.index', ['tahun' => $kpi['tahun']]) }}" class="text-xs font-semibold text-blue-700 underline hover:text-blue-900">Buka daftar verifikasi</a>
                </div>
                <ul class="mt-2 space-y-1 pl-4 text-xs text-amber-800">
                    @foreach($kpi['verifikasiBelumDikonfirmasi']->take(3) as $record)
                        <li>{{ $record->tool_name }} · {{ ucfirst($record->tipe) }}</li>
                    @endforeach
                    @if($kpi['jumlahVerificationBelumDikonfirmasi'] > 3)
                        <li class="font-semibold">Dan {{ $kpi['jumlahVerificationBelumDikonfirmasi'] - 3 }} verifikasi lainnya</li>
                    @endif
                </ul>
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- KOMPOSISI DAFTAR INDUK --}}
    <div class="grid grid-cols-1 items-stretch gap-5 xl:grid-cols-2">
    <div class="min-h-[156px] rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h3 class="font-semibold text-slate-900">Komposisi daftar induk</h3>
            <p class="mt-1 text-sm text-slate-500">Jumlah alat terdaftar per kategori pada {{ $tahun }}.</p>
        </div>
        <div class="space-y-3">
            @forelse($kpi['breakdownKategori'] as $kat)
                @php
                    $persentaseKategori = $totalTools > 0 ? round(($kat->jumlah / $totalTools) * 100) : 0;
                @endphp
                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-3 text-xs">
                        <span class="font-medium text-slate-700">{{ $kat->category }}</span>
                        <span class="shrink-0 font-semibold text-slate-600">{{ $kat->jumlah }} <span class="font-normal text-slate-400">({{ $persentaseKategori }}%)</span></span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Persentase kategori {{ $kat->category }}" aria-valuenow="{{ $persentaseKategori }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-blue-600 transition-all" style="width: {{ $persentaseKategori }}%"></div>
                    </div>
                </div>
            @empty
                <div class="py-6 text-center text-sm text-slate-500">Belum ada data daftar induk untuk tahun ini.</div>
            @endforelse
        </div>
    </div>

    {{-- REKAPITULASI DATA TAHUNAN --}}
    <div class="min-h-[156px] rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="font-semibold text-slate-900">Rekap data {{ $tahun }}</h3>
        <p class="mt-1 mb-4 text-sm text-slate-500">Jumlah catatan dalam sistem.</p>
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-5">
                <div class="bg-slate-50 rounded-lg p-2.5">
                    <p class="text-2xl font-bold text-blue-700">{{ $totalTools }}</p>
                    <p class="text-[10px] text-blue-600 uppercase">Daftar Induk</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-2.5">
                    <p class="text-2xl font-bold text-blue-700">{{ $totalVerification }}</p>
                    <p class="text-[10px] text-blue-600 uppercase">Verification</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-2.5">
                    <p class="text-2xl font-bold text-purple-700">{{ $totalInternal }}</p>
                    <p class="text-[10px] text-purple-600 uppercase">Internal</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-2.5">
                    <p class="text-2xl font-bold text-amber-700">{{ $totalExternal }}</p>
                    <p class="text-[10px] text-amber-600 uppercase">External</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-2.5">
                    <p class="text-2xl font-bold text-rose-700">{{ $totalMachine }}</p>
                    <p class="text-[10px] text-rose-600 uppercase">Machine</p>
                </div>
        </div>
    </div>
    </div>

</div>

@endsection
