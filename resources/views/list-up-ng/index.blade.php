@extends('layouts.app')

@section('content')

@if(session('success'))
<div class="bg-blue-100 border border-blue-400 text-blue-700 rounded mb-3 md:mb-4 px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 rounded mb-3 md:mb-4 px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
    {{ session('error') }}
</div>
@endif

@php
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
@endphp

<div class="feature-toolbar bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-3 py-2.5 sm:px-4 sm:py-3">
    <div class="feature-toolbar-layout flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-sm sm:text-base font-bold text-gray-800 whitespace-nowrap">
                List Up Total Tool Measurement NG - YEAR {{ $tahun }}
            </h1>
            <form method="GET" action="{{ route('list-up-ng.index') }}" class="flex flex-wrap items-center gap-2 sm:gap-3">
                <div class="flex items-center gap-1.5">
                    <label class="text-[10px] sm:text-xs font-semibold text-gray-500">YEAR</label>
                    <select name="tahun" onchange="this.form.submit()" class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1 sm:px-2.5 sm:py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($y = date('Y'); $y >= 2014; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}> {{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <label class="text-[10px] sm:text-xs font-semibold text-gray-500">MONTH</label>
                    <select name="bulan" onchange="this.form.submit()" class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1 sm:px-2.5 sm:py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Bulan</option>
                        @foreach($namaBulan as $num => $label)
                            <option value="{{ $num }}" {{ (string) $bulan === (string) $num ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <label class="text-[10px] sm:text-xs font-semibold text-gray-500">DIVISION</label>
                    <select name="division" onchange="this.form.submit()" class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1 sm:px-2.5 sm:py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Division</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ $division == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <label class="text-[10px] sm:text-xs font-semibold text-gray-500">CATEGORY</label>
                    <select name="category_ng" onchange="this.form.submit()" class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1 sm:px-2.5 sm:py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $categoryNg == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
        
        <div class="flex items-center justify-between md:justify-end gap-2 sm:gap-3">
            <span class="text-[10px] sm:text-xs text-gray-500 whitespace-nowrap">
                Total <strong class="text-gray-700">{{ $ngs->count() }}</strong> Data NG
            </span>
            
            @if(Auth::check() && Auth::user()->role === 'admin')
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <!-- Tombol Import -->
                    <a href="{{ route('list-up-ng.import.form') }}" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-50 sm:px-3 sm:py-2 sm:text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import
                    </a>

                    <!-- Dropdown Export -->
                    <div class="relative inline-block" id="exportDropdownNg">
                        <button type="button" onclick="toggleExportNg()" 
                                class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 sm:px-3 sm:py-2 sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <!-- Menu Dropdown -->
                        <div id="exportMenuNg" class="hidden absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                            <a href="{{ route('list-up-ng.export.excel', ['tahun' => $tahun, 'bulan' => $bulan, 'division' => $division, 'category_ng' => $categoryNg]) }}"
                               class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-blue-50 transition border-b border-gray-100">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-blue-700 text-[10px] font-bold text-white">X</span>
                                <span class="font-medium">Export Excel</span>
                            </a>
                            <a href="{{ route('list-up-ng.export.pdf', ['tahun' => $tahun, 'bulan' => $bulan, 'division' => $division, 'category_ng' => $categoryNg]) }}"
                               class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-red-50 transition">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-rose-600 text-[8px] font-bold text-white">PDF</span>
                                <span class="font-medium">Export PDF</span>
                            </a>
                        </div>
                    </div>

                    <!-- Tombol Tambah -->
                    <a href="{{ route('list-up-ng.create') }}" class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded bg-black px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-gray-800 sm:px-3 sm:py-2 sm:text-sm">
                        <span>+</span> Tambah
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<p class="text-[10px] text-gray-400 mb-1 md:hidden flex items-center gap-1">
    <span>←</span> Geser tabel ke kanan untuk lihat semua kolom <span>→</span>
</p>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1500px]">
            <thead class="table-header uppercase bg-gray-800 text-white">
                <tr>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Tool Name</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Type</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Size</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No Seri</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No Reg</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Part Name</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Part Number</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">NG Date</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Division</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Category NG</th>
                    <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs">Note</th>
                    
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($ngs as $index => $ng)
                <tr class="border-b {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50">
                    <td class="px-2 py-2 font-semibold text-[11px] md:text-xs">{{ $index + 1 }}</td>
                    <td class="px-2 py-2 font-medium text-[11px] md:text-xs">{{ $ng->tool_name }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $ng->type ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs text-blue-600 font-semibold">{{ $ng->size ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $ng->number_seri ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $ng->number_reg ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $ng->part_name ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $ng->part_number ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $ng->ng_date->format('d/m/Y') }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs font-bold text-gray-700">{{ $ng->division }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs text-rose-600 font-semibold">{{ $ng->category_ng }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs max-w-[200px] truncate" title="{{ $ng->note }}">{{ $ng->note ?? '-' }}</td>
                    
                    @if(Auth::check() && Auth::user()->role === 'admin')
                    <td class="px-2 py-2 text-center">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('list-up-ng.edit', $ng->id) }}" class="rounded bg-blue-600 px-2 py-1 text-[10px] text-white hover:bg-blue-700">Edit</a>
                            <form action="{{ route('list-up-ng.destroy', $ng->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-rose-600 px-2 py-1 text-[10px] text-white hover:bg-rose-700">Hapus</button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::check() && Auth::user()->role === 'admin' ? '13' : '12' }}" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm px-4">
                        Belum ada data List Up NG untuk {{ $bulan ? 'bulan ' . ($namaBulan[(int) $bulan] ?? '') . ' ' : '' }}YEAR <strong>{{ $tahun }}</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Script untuk Toggle Dropdown Export -->
<script>
    function toggleExportNg() {
        document.getElementById('exportMenuNg').classList.toggle('hidden');
    }
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('exportDropdownNg');
        const menu = document.getElementById('exportMenuNg');
        if (dropdown && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
        }
    });
</script>

@endsection