@extends('layouts.guest_app')

@section('content')

<!-- ============================================ -->
<!-- BANNER MODE GUEST -->
<!-- ============================================ -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div>
        <p class="text-sm font-semibold text-blue-800">Mode Guest (View Only)</p>
        <p class="text-xs text-blue-600 mt-0.5">Anda dapat melihat data, namun tidak dapat menambah, mengubah, atau menghapus data.</p>
    </div>
</div>

<!-- ============================================ -->
<!-- HEADER HALAMAN + SEARCH + FILTER TAHUN -->
<!-- ============================================ -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-4 p-3 sm:p-4">
    <div class="flex flex-col gap-3">

        <!-- Baris 1: Judul -->
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-slate-800">
                    👁 Daftar Alat Ukur & Jadwal
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Mode Guest — Hanya bisa melihat data
                </p>
            </div>
        </div>

        <!-- Baris 2: Search + Filter Tahun -->
        <form method="GET" action="{{ route('guest.index') }}" class="flex flex-col sm:flex-row gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <!-- Search: Pakai Flexbox -->
            <div class="flex items-center flex-1 bg-slate-50 border border-slate-200 rounded-lg
                        focus-within:bg-white focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200
                        transition overflow-hidden h-11">
                <div class="flex items-center justify-center w-10 h-10 text-slate-400 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari alat, mesin, atau jadwal..."
                       class="flex-1 h-full bg-transparent border-0 text-sm text-slate-700 placeholder:text-slate-400
                              focus:outline-none focus:ring-0 pr-3">
            </div>

            <!-- Filter Tahun: Pakai Flexbox -->
            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg
                        focus-within:bg-white focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-200
                        transition overflow-hidden h-11 min-w-[150px]">
                <select name="tahun" onchange="this.form.submit()"
                        class="flex-1 h-full bg-transparent border-0 text-sm text-slate-700 font-medium
                               pl-4 pr-2 focus:outline-none focus:ring-0 cursor-pointer appearance-none">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
                <div class="flex items-center justify-center w-8 h-10 text-slate-500 pointer-events-none shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            <button type="submit"
                    class="h-11 bg-slate-900 hover:bg-slate-800 text-white rounded-lg px-5 text-sm font-semibold
                           transition inline-flex items-center justify-center gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Cari</span>
            </button>
        </form>

        <!-- Baris 3: Tab -->
        <div class="flex gap-1 overflow-x-auto pb-1 -mb-1">
            @php
                $tabs = [
                    'semua' => '📋 Semua',
                    'induk' => '📄 Daftar Induk',
                    'verifikasi' => '✅ Verifikasi',
                    'internal' => '🔧 Internal',
                    'eksternal' => '⚙️ Eksternal',
                    'mesin' => '🏭 Mesin',
                ];
            @endphp

            @foreach($tabs as $key => $label)
                <a href="{{ route('guest.index', ['tab' => $key, 'search' => $search, 'tahun' => $tahun]) }}"
                   class="tab-btn {{ $tab == $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

    </div>
</div>

<!-- ============================================ -->
<!-- HASIL PENCARIAN -->
<!-- ============================================ -->
@if($search !== '')
<div class="mb-4 text-sm text-slate-600">
    Hasil pencarian untuk: <strong class="text-slate-900">"{{ $search }}"</strong>
    @if($tahun)
        &middot; Tahun <strong>{{ $tahun }}</strong>
    @endif
</div>
@endif

<!-- ============================================ -->
<!-- KONTEN PER TAB -->
<!-- ============================================ -->

{{-- ============ TAB: INDUK ============ --}}
@if(isset($data['induk']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 text-sm">📄 Daftar Induk Alat Ukur</h2>
        <span class="text-xs text-slate-500">{{ $data['induk']->count() }} alat</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1200px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2.5 text-[10px]">No</th>
                    <th class="px-2 py-2.5 text-[10px] min-w-[180px]">Tool Name</th>
                    <th class="px-2 py-2.5 text-[10px]">Kategori</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">QA 1</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">QA 1.2</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">QA 2</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">QA INC</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">QA INSP</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">PPIC 1</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">PPIC 2</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">LINE SCC</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P1</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P2</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P3</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P5</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P6</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P7</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">P8</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">ENG</th>
                    <th class="px-2 py-2.5 text-[10px] text-center bg-emerald-600 text-white">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['induk'] as $i => $row)
                <tr class="border-b {{ $i % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-semibold">{{ $i + 1 }}</td>
                    <td class="px-2 py-2 font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->category }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->qa_1 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->qa_1_2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->qa_2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->qa_inc ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->qa_insp ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->ppic_1 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->ppic_2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->line_scc ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p1 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p3 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p5 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p6 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p7 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->p8 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->eng ?? 0 }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50">{{ $row->total ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="text-center py-6 text-slate-400 text-sm">
                        Tidak ada data induk untuk filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: VERIFIKASI ============ --}}
@if(isset($data['verifikasi']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 text-sm">✅ Jadwal Verifikasi</h2>
        <span class="text-xs text-slate-500">{{ $data['verifikasi']->count() }} jadwal</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[900px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2.5 text-[10px]">No</th>
                    <th class="px-2 py-2.5 text-[10px] min-w-[180px]">Tool Name</th>
                    <th class="px-2 py-2.5 text-[10px]">Type</th>
                    <th class="px-2 py-2.5 text-[10px]">Tanggal</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['verifikasi'] as $i => $row)
                <tr class="border-b {{ $i % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-semibold">{{ $i + 1 }}</td>
                    <td class="px-2 py-2 font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ ucfirst($row->tipe ?? '-') }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->tanggal ?? '-' }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50">{{ $row->total ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-slate-400 text-sm">
                        Tidak ada data verifikasi untuk filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: INTERNAL ============ --}}
@if(isset($data['internal']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 text-sm">🔧 Kalibrasi Internal</h2>
        <span class="text-xs text-slate-500">{{ $data['internal']->count() }} jadwal</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1000px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2.5 text-[10px]">No</th>
                    <th class="px-2 py-2.5 text-[10px] min-w-[180px]">Tool Name</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jan</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Feb</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Mar</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Apr</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">May</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jun</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jul</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Aug</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Sep</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Oct</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Nov</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Dec</th>
                    <th class="px-2 py-2.5 text-[10px] text-center bg-emerald-600 text-white">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['internal'] as $i => $row)
                <tr class="border-b {{ $i % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-semibold">{{ $i + 1 }}</td>
                    <td class="px-2 py-2 font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jan ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->feb ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->mar ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->apr ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->may ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jun ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jul ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->aug ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->sep ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->oct ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->nov ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->dec ?? 0 }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50">{{ $row->qty ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center py-6 text-slate-400 text-sm">
                        Tidak ada data internal untuk filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: EKSTERNAL SMALL ============ --}}
@if(isset($data['eksternal']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 text-sm">⚙️ Kalibrasi Eksternal (Small Tool)</h2>
        <span class="text-xs text-slate-500">{{ $data['eksternal']->count() }} jadwal</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1000px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2.5 text-[10px]">No</th>
                    <th class="px-2 py-2.5 text-[10px] min-w-[180px]">Tool Name</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jan</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Feb</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Mar</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Apr</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">May</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jun</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jul</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Aug</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Sep</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Oct</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Nov</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Dec</th>
                    <th class="px-2 py-2.5 text-[10px] text-center bg-emerald-600 text-white">Qty Set</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['eksternal'] as $i => $row)
                <tr class="border-b {{ $i % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-semibold">{{ $i + 1 }}</td>
                    <td class="px-2 py-2 font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jan ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->feb ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->mar ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->apr ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->may ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jun ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jul ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->aug ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->sep ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->oct ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->nov ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->dec ?? 0 }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50">{{ $row->qty_set ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center py-6 text-slate-400 text-sm">
                        Tidak ada data eksternal untuk filter ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: MESIN ============ --}}
@if(isset($data['mesin']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-bold text-slate-800 text-sm">🏭 Kalibrasi Eksternal (Mesin)</h2>
        <span class="text-xs text-slate-500">{{ $data['mesin']->count() }} mesin</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1200px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2.5 text-[10px]">No</th>
                    <th class="px-2 py-2.5 text-[10px] min-w-[150px]">Tool / Machine Name</th>
                    <th class="px-2 py-2.5 text-[10px]">Model</th>
                    <th class="px-2 py-2.5 text-[10px]">Code</th>
                    <th class="px-2 py-2.5 text-[10px]">Serial</th>
                    <th class="px-2 py-2.5 text-[10px]">Maker</th>
                    <th class="px-2 py-2.5 text-[10px]">No. Machine</th>
                    <th class="px-2 py-2.5 text-[10px]">Retension</th>
                    <th class="px-2 py-2.5 text-[10px]">Start</th>
                    <th class="px-2 py-2.5 text-[10px]">Next</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jan</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Feb</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Mar</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Apr</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">May</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jun</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Jul</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Aug</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Sep</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Oct</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Nov</th>
                    <th class="px-2 py-2.5 text-[10px] text-center">Dec</th>
                    <th class="px-2 py-2.5 text-[10px] text-center bg-emerald-600 text-white">QTY (SET)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['mesin'] as $i => $row)
                <tr class="border-b {{ $i % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-semibold">{{ $i + 1 }}</td>
                    <td class="px-2 py-2 font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->machine_model ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->code_number ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->serial_number ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->maker ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->no_machine ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->retension ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->start_date ?? '-' }}</td>
                    <td class="px-2 py-2 text-slate-600">{{ $row->next_date ?? '-' }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jan ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->feb ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->mar ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->apr ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->may ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jun ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->jul ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->aug ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->sep ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->oct ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->nov ?? 0 }}</td>
                    <td class="px-2 py-2 text-center">{{ $row->dec ?? 0 }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50">{{ $row->qty_set ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="23" class="text-center py-6 text-slate-400 text-sm">
                        Tidak ada data mesin untuk filter ini.
                    </td>
                </tr>
                @endforelse

                {{-- BARIS TOTAL PER BULAN DI BAWAH --}}
                @if($data['mesin']->count() > 0)
                <tr class="bg-emerald-600 text-white font-bold">
                    {{-- Teks "TOTAL" di kolom NO, rata tengah --}}
                    <td class="px-2 py-3 text-center text-[10px] uppercase tracking-wide" colspan="10">
                        Total
                    </td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('jan') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('feb') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('mar') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('apr') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('may') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('jun') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('jul') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('aug') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('sep') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('oct') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('nov') }}</td>
                    <td class="px-2 py-3 text-center text-sm">{{ $data['mesin']->sum('dec') }}</td>
                    <td class="px-2 py-3 text-center text-sm bg-emerald-700">{{ $data['mesin']->sum('qty_set') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection