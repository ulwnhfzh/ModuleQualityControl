@extends('layouts.guest_app')

@section('content')

{{-- ============================================ --}}
{{-- BANNER MODE GUEST --}}
{{-- ============================================ --}}
<div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-3 mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div>
        <p class="text-sm font-semibold text-blue-800">Mode Guest (View Only)</p>
        <p class="text-xs text-blue-600 mt-0.5">Anda dapat melihat data, namun tidak dapat menambah, mengubah, atau menghapus data.</p>
    </div>
</div>

{{-- ============================================ --}}
{{-- CARD 1: JUDUL --}}
{{-- ============================================ --}}
<div class="feature-toolbar bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-3">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0 shadow-md" style="background-color:#059669;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-slate-800 leading-tight">
                Daftar Alat Ukur & Jadwal
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Mode Guest — Hanya bisa melihat data
            </p>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- CARD 2: FILTER --}}
{{-- ============================================ --}}
<div class="feature-toolbar bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-3">
    <form method="GET" action="{{ route('guest.index') }}" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="tab" value="{{ $tab }}">

        {{-- Search --}}
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari alat, mesin, atau jadwal..."
                   class="w-full h-11 pl-11 pr-4 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700
                          placeholder:text-slate-400
                          focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
        </div>

        {{-- Filter Tahun --}}
        <div class="sm:w-44">
            <select name="tahun"
                    class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium text-slate-700
                           focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer transition">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endfor
            </select>
        </div>

        {{-- Tombol Cari --}}
        <button type="submit"
                class="h-11 px-6 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-sm font-semibold
                       transition inline-flex items-center justify-center gap-2 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Cari</span>
        </button>
    </form>
</div>

{{-- ============================================ --}}
{{-- CARD 3: TAB --}}
{{-- ============================================ --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4">
    <div class="flex gap-1.5 overflow-x-auto">
        @php
            $tabs = [
                'semua' => ['label' => 'Semua', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                'induk' => ['label' => 'Daftar Induk', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                'verifikasi' => ['label' => 'Verifikasi', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'internal' => ['label' => 'Internal', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                'eksternal' => ['label' => 'Eksternal', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
                'mesin' => ['label' => 'Mesin', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ];
        @endphp

        @foreach($tabs as $key => $item)
            @php $isActive = $tab == $key; @endphp
            <a href="{{ route('guest.index', ['tab' => $key, 'search' => $search, 'tahun' => $tahun]) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition
                      {{ $isActive
                          ? 'text-white shadow-sm'
                          : 'bg-slate-50 text-slate-600 border border-slate-200 hover:bg-slate-100' }}"
               @if($isActive) style="background-color:#059669;" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>

{{-- ============================================ --}}
{{-- HASIL PENCARIAN --}}
{{-- ============================================ --}}
@if($search !== '')
<div class="mb-4 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
    Hasil pencarian untuk: <strong>"{{ $search }}"</strong>
    @if($tahun)
        &middot; Tahun <strong>{{ $tahun }}</strong>
    @endif
</div>
@endif

{{-- ============================================ --}}
{{-- STYLE HEADER TABEL --}}
{{-- ============================================ --}}
@php
    $thStyle = 'background-color:#a0a8b4; color:#111827; font-weight:700; padding:10px 8px; font-size:10px; text-transform:uppercase; border:1px solid #000000;';
    $thStyleTotal = 'background-color:#a0a8b4; color:#111827; font-weight:700; padding:10px 8px; font-size:10px; text-transform:uppercase; border:1px solid #000000;';
    $tdStyle = 'padding:8px 8px; border:1px solid #000000;';
    $tfootStyle = 'background-color:#a0a8b4; color:#111827; font-weight:700; padding:12px 8px; font-size:11px; border:1px solid #000000;';
@endphp

{{-- ============ TAB: INDUK ============ --}}
@if(isset($data['induk']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-blue-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color:#2563eb;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <h2 class="font-bold text-slate-800 text-sm">Daftar Induk Alat Ukur</h2>
        </div>
        <span class="text-xs font-semibold text-blue-700 bg-blue-100 px-3 py-1 rounded-full">{{ $data['induk']->count() }} alat</span>
    </div>
    <div class="overflow-x-auto table-scroll">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1200px]">
            <thead>
                <tr>
                    <th style="{{ $thStyle }}">No</th>
                    <th style="{{ $thStyle }} min-width:180px;">Tool Name</th>
                    <th style="{{ $thStyle }}">Kategori</th>
                    <th style="{{ $thStyle }} text-align:center;">QA 1</th>
                    <th style="{{ $thStyle }} text-align:center;">QA 1.2</th>
                    <th style="{{ $thStyle }} text-align:center;">QA 2</th>
                    <th style="{{ $thStyle }} text-align:center;">QA INC</th>
                    <th style="{{ $thStyle }} text-align:center;">QA INSP</th>
                    <th style="{{ $thStyle }} text-align:center;">PPIC 1</th>
                    <th style="{{ $thStyle }} text-align:center;">PPIC 2</th>
                    <th style="{{ $thStyle }} text-align:center;">LINE SCC</th>
                    <th style="{{ $thStyle }} text-align:center;">P1</th>
                    <th style="{{ $thStyle }} text-align:center;">P2</th>
                    <th style="{{ $thStyle }} text-align:center;">P3</th>
                    <th style="{{ $thStyle }} text-align:center;">P5</th>
                    <th style="{{ $thStyle }} text-align:center;">P6</th>
                    <th style="{{ $thStyle }} text-align:center;">P7</th>
                    <th style="{{ $thStyle }} text-align:center;">P8</th>
                    <th style="{{ $thStyle }} text-align:center;">ENG</th>
                    <th style="{{ $thStyleTotal }} text-align:center;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['induk'] as $i => $row)
                <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-emerald-50 transition">
                    <td style="{{ $tdStyle }}" class="font-semibold">{{ $i + 1 }}</td>
                    <td style="{{ $tdStyle }}" class="font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->category }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->qa_1 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->qa_1_2 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->qa_2 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->qa_inc ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->qa_insp ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->ppic_1 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->ppic_2 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->line_scc ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p1 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p2 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p3 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p5 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p6 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p7 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->p8 ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->eng ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center font-bold text-gray-900 bg-gray-100">{{ $row->total ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="text-center py-8 text-slate-400 text-sm">Tidak ada data induk untuk filter ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($data['induk']->count() > 0)
            <tfoot>
                <tr style="background-color:#047857; color:#ffffff;">
                    <td colspan="3" style="{{ $tfootStyle }} text-align:center;">TOTAL</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['qa_1'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['qa_1_2'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['qa_2'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['qa_inc'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['qa_insp'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['ppic_1'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['ppic_2'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['line_scc'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p1'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p2'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p3'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p5'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p6'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p7'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['p8'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['eng'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['induk_total']['total'] }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: VERIFIKASI ============ --}}
@if(isset($data['verifikasi']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-emerald-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color:#059669;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="font-bold text-slate-800 text-sm">Jadwal Verifikasi</h2>
        </div>
        <span class="text-xs font-semibold text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">{{ $data['verifikasi']->count() }} jadwal</span>
    </div>
    <div class="overflow-x-auto table-scroll">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[900px]">
            <thead>
                <tr>
                    <th style="{{ $thStyle }} width:50px;">No</th>
                    <th style="{{ $thStyle }} min-width:180px;">Tool Name</th>
                    <th style="{{ $thStyle }} width:120px;">Type</th>
                    <th style="{{ $thStyle }} width:140px;">Tanggal</th>
                    <th style="{{ $thStyleTotal }} text-align:center; width:80px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['verifikasi'] as $i => $row)
                <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-emerald-50 transition">
                    <td style="{{ $tdStyle }}" class="font-semibold">{{ $i + 1 }}</td>
                    <td style="{{ $tdStyle }}" class="font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ ucfirst($row->tipe ?? '-') }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->tanggal ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center font-bold text-gray-900 bg-gray-100">{{ $row->total ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-400 text-sm">Tidak ada data verifikasi untuk filter ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($data['verifikasi']->count() > 0)
            <tfoot>
                <tr style="background-color:#d1fae5; color:#065f46;">
                    <td colspan="4" style="{{ $tfootStyle }} text-align:center;">TOTAL</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['verifikasi_total']['total'] }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: INTERNAL ============ --}}
@if(isset($data['internal']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-purple-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color:#9333ea;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="font-bold text-slate-800 text-sm">Kalibrasi Internal</h2>
        </div>
        <span class="text-xs font-semibold text-purple-700 bg-purple-100 px-3 py-1 rounded-full">{{ $data['internal']->count() }} jadwal</span>
    </div>
    <div class="overflow-x-auto table-scroll">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1000px]">
            <thead>
                <tr>
                    <th style="{{ $thStyle }} width:50px;">No</th>
                    <th style="{{ $thStyle }} min-width:180px;">Tool Name</th>
                    <th style="{{ $thStyle }} text-align:center;">Jan</th>
                    <th style="{{ $thStyle }} text-align:center;">Feb</th>
                    <th style="{{ $thStyle }} text-align:center;">Mar</th>
                    <th style="{{ $thStyle }} text-align:center;">Apr</th>
                    <th style="{{ $thStyle }} text-align:center;">May</th>
                    <th style="{{ $thStyle }} text-align:center;">Jun</th>
                    <th style="{{ $thStyle }} text-align:center;">Jul</th>
                    <th style="{{ $thStyle }} text-align:center;">Aug</th>
                    <th style="{{ $thStyle }} text-align:center;">Sep</th>
                    <th style="{{ $thStyle }} text-align:center;">Oct</th>
                    <th style="{{ $thStyle }} text-align:center;">Nov</th>
                    <th style="{{ $thStyle }} text-align:center;">Dec</th>
                    <th style="{{ $thStyleTotal }} text-align:center;">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['internal'] as $i => $row)
                <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-purple-50 transition">
                    <td style="{{ $tdStyle }}" class="font-semibold">{{ $i + 1 }}</td>
                    <td style="{{ $tdStyle }}" class="font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jan ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->feb ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->mar ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->apr ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->may ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jun ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jul ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->aug ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->sep ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->oct ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->nov ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->dec ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center font-bold text-gray-900 bg-gray-100">{{ $row->qty ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center py-8 text-slate-400 text-sm">Tidak ada data internal untuk filter ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($data['internal']->count() > 0)
            <tfoot>
                <tr style="background-color:#047857; color:#ffffff;">
                    <td colspan="2" style="{{ $tfootStyle }} text-align:center;">TOTAL</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['jan'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['feb'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['mar'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['apr'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['may'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['jun'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['jul'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['aug'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['sep'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['oct'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['nov'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['dec'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['internal_total']['qty'] }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: EKSTERNAL SMALL ============ --}}
@if(isset($data['eksternal']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-amber-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color:#d97706;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
            </div>
            <h2 class="font-bold text-slate-800 text-sm">Kalibrasi Eksternal (Small Tool)</h2>
        </div>
        <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-3 py-1 rounded-full">{{ $data['eksternal']->count() }} jadwal</span>
    </div>
    <div class="overflow-x-auto table-scroll">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1000px]">
            <thead>
                <tr>
                    <th style="{{ $thStyle }} width:50px;">No</th>
                    <th style="{{ $thStyle }} min-width:180px;">Tool Name</th>
                    <th style="{{ $thStyle }} text-align:center;">Jan</th>
                    <th style="{{ $thStyle }} text-align:center;">Feb</th>
                    <th style="{{ $thStyle }} text-align:center;">Mar</th>
                    <th style="{{ $thStyle }} text-align:center;">Apr</th>
                    <th style="{{ $thStyle }} text-align:center;">May</th>
                    <th style="{{ $thStyle }} text-align:center;">Jun</th>
                    <th style="{{ $thStyle }} text-align:center;">Jul</th>
                    <th style="{{ $thStyle }} text-align:center;">Aug</th>
                    <th style="{{ $thStyle }} text-align:center;">Sep</th>
                    <th style="{{ $thStyle }} text-align:center;">Oct</th>
                    <th style="{{ $thStyle }} text-align:center;">Nov</th>
                    <th style="{{ $thStyle }} text-align:center;">Dec</th>
                    <th style="{{ $thStyleTotal }} text-align:center;">Qty Set</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['eksternal'] as $i => $row)
                <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-amber-50 transition">
                    <td style="{{ $tdStyle }}" class="font-semibold">{{ $i + 1 }}</td>
                    <td style="{{ $tdStyle }}" class="font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jan ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->feb ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->mar ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->apr ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->may ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jun ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jul ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->aug ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->sep ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->oct ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->nov ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->dec ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center font-bold text-gray-900 bg-gray-100">{{ $row->qty_set ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="text-center py-8 text-slate-400 text-sm">Tidak ada data eksternal untuk filter ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($data['eksternal']->count() > 0)
            <tfoot>
                <tr style="background-color:#047857; color:#ffffff;">
                    <td colspan="2" style="{{ $tfootStyle }} text-align:center;">TOTAL</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['jan'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['feb'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['mar'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['apr'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['may'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['jun'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['jul'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['aug'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['sep'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['oct'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['nov'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['dec'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['eksternal_total']['qty_set'] }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

{{-- ============ TAB: MESIN ============ --}}
@if(isset($data['mesin']))
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-rose-50">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color:#e11d48;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="font-bold text-slate-800 text-sm">Kalibrasi Eksternal (Mesin)</h2>
        </div>
        <span class="text-xs font-semibold text-rose-700 bg-rose-100 px-3 py-1 rounded-full">{{ $data['mesin']->count() }} mesin</span>
    </div>
    <div class="overflow-x-auto table-scroll">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1400px]">
            <thead>
                <tr>
                    <th style="{{ $thStyle }} width:50px;">No</th>
                    <th style="{{ $thStyle }} min-width:150px;">Tool / Machine Name</th>
                    <th style="{{ $thStyle }}">Model</th>
                    <th style="{{ $thStyle }}">Code</th>
                    <th style="{{ $thStyle }}">Serial</th>
                    <th style="{{ $thStyle }}">Maker</th>
                    <th style="{{ $thStyle }}">No. Machine</th>
                    <th style="{{ $thStyle }}">Retension</th>
                    <th style="{{ $thStyle }}">Start Calibration</th>
                    <th style="{{ $thStyle }}">Next Calibration</th>
                    <th style="{{ $thStyle }} text-align:center;">Jan</th>
                    <th style="{{ $thStyle }} text-align:center;">Feb</th>
                    <th style="{{ $thStyle }} text-align:center;">Mar</th>
                    <th style="{{ $thStyle }} text-align:center;">Apr</th>
                    <th style="{{ $thStyle }} text-align:center;">May</th>
                    <th style="{{ $thStyle }} text-align:center;">Jun</th>
                    <th style="{{ $thStyle }} text-align:center;">Jul</th>
                    <th style="{{ $thStyle }} text-align:center;">Aug</th>
                    <th style="{{ $thStyle }} text-align:center;">Sep</th>
                    <th style="{{ $thStyle }} text-align:center;">Oct</th>
                    <th style="{{ $thStyle }} text-align:center;">Nov</th>
                    <th style="{{ $thStyle }} text-align:center;">Dec</th>
                    <th style="{{ $thStyleTotal }} text-align:center;">QTY (SET)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['mesin'] as $i => $row)
                <tr class="{{ $i % 2 == 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-rose-50 transition">
                    <td style="{{ $tdStyle }}" class="font-semibold">{{ $i + 1 }}</td>
                    <td style="{{ $tdStyle }}" class="font-medium">{{ strtoupper($row->tool_name) }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->machine_model ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->code_number ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->serial_number ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->maker ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->no_machine ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->retension ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->start_date ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-slate-600">{{ $row->next_date ?? '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jan ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->feb ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->mar ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->apr ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->may ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jun ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->jul ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->aug ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->sep ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->oct ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->nov ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center">{{ $row->dec ?: '-' }}</td>
                    <td style="{{ $tdStyle }}" class="text-center font-bold text-gray-900 bg-gray-100">{{ $row->qty_set ?? 0 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="23" class="text-center py-8 text-slate-400 text-sm">Tidak ada data mesin untuk filter ini.</td>
                </tr>
                @endforelse
            </tbody>
            @if($data['mesin']->count() > 0)
            <tfoot>
                <tr style="background-color:#047857; color:#ffffff;">
                    <td colspan="10" style="{{ $tfootStyle }} text-align:center;">TOTAL</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['jan'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['feb'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['mar'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['apr'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['may'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['jun'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['jul'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['aug'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['sep'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['oct'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['nov'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['dec'] }}</td>
                    <td style="{{ $tfootStyle }} text-align:center;">{{ $data['mesin_total']['qty_set'] }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endif

@endsection
