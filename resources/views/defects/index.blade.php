@extends('layouts.app')

@section('content')
<div class="feature-toolbar mb-3 overflow-hidden bg-white px-3 py-2.5 shadow-sm sm:mb-4 sm:px-4 sm:py-3">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex min-w-0 flex-wrap items-center gap-3">
            <h1 class="text-sm font-bold uppercase text-gray-800 sm:text-base">Data Record Tool</h1>
            <form method="GET" action="{{ route('defects.index') }}" class="flex flex-wrap items-center gap-2 sm:gap-3">
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500 sm:text-xs">
                    TAHUN
                    <select name="tahun" onchange="this.form.submit()" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs sm:px-2.5 sm:py-1.5 sm:text-sm">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </label>
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500 sm:text-xs">
                    BULAN
                    <select name="bulan" onchange="this.form.submit()" class="rounded border border-gray-300 bg-white px-2 py-1 text-xs sm:px-2.5 sm:py-1.5 sm:text-sm">
                        <option value="JAN" {{ $bulanFilter == 'JAN' ? 'selected' : '' }}>JANUARI</option>
                        <option value="FEB" {{ $bulanFilter == 'FEB' ? 'selected' : '' }}>FEBRUARI</option>
                        <option value="MAR" {{ $bulanFilter == 'MAR' ? 'selected' : '' }}>MARET</option>
                        <option value="APR" {{ $bulanFilter == 'APR' ? 'selected' : '' }}>APRIL</option>
                        <option value="MAY" {{ $bulanFilter == 'MAY' ? 'selected' : '' }}>MEI</option>
                        <option value="JUN" {{ $bulanFilter == 'JUN' ? 'selected' : '' }}>JUNI</option>
                        <option value="JUL" {{ $bulanFilter == 'JUL' ? 'selected' : '' }}>JULI</option>
                        <option value="AUG" {{ $bulanFilter == 'AUG' ? 'selected' : '' }}>AGUSTUS</option>
                        <option value="SEP" {{ $bulanFilter == 'SEP' ? 'selected' : '' }}>SEPTEMBER</option>
                        <option value="OCT" {{ $bulanFilter == 'OCT' ? 'selected' : '' }}>OKTOBER</option>
                        <option value="NOV" {{ $bulanFilter == 'NOV' ? 'selected' : '' }}>NOVEMBER</option>
                        <option value="DEC" {{ $bulanFilter == 'DEC' ? 'selected' : '' }}>DESEMBER</option>
                    </select>
                </label>
            </form>
        </div>

        <div class="feature-toolbar-actions flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('defects.import.form') }}" class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded border border-gray-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-gray-700 transition hover:bg-gray-50 sm:px-3 sm:py-2 sm:text-sm">
                <span aria-hidden="true">📥</span> Import
            </a>
            <div class="relative inline-block" x-data="{ exportOpen: false }" @click.outside="exportOpen = false">
                <button type="button" @click="exportOpen = !exportOpen" :aria-expanded="exportOpen.toString()" aria-haspopup="true"
                        class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded bg-slate-800 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-slate-700 sm:px-3 sm:py-2 sm:text-sm">
                    <span aria-hidden="true">📤</span> Export
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="exportMenuNg" x-show="exportOpen" x-cloak x-transition
                     class="absolute right-0 z-50 mt-1 w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                    <a href="{{ route('defects.export.excel', ['tahun' => $tahun, 'bulan' => $bulanFilter]) }}"
                       class="flex items-center gap-2 px-3 py-2.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50">
                        <span class="font-bold text-green-700" aria-hidden="true">X</span> Export Excel
                    </a>
                    <a href="{{ route('defects.export.pdf', ['tahun' => $tahun, 'bulan' => $bulanFilter]) }}"
                       class="flex items-center gap-2 border-t border-gray-100 px-3 py-2.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50">
                        <span class="font-bold text-red-700" aria-hidden="true">PDF</span> Export PDF
                    </a>
                </div>
            </div>
            <a href="{{ route('defects.create') }}" class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded bg-black px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-gray-800 sm:px-4 sm:py-2 sm:text-sm">
                <span>+</span> Tambah
            </a>
        </div>
    </div>
</div>

<div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm sm:p-6">
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 text-sm">{{ session('success') }}</div>
    @endif

    <!-- TABEL REKAPITULASI -->
    <div class="overflow-x-auto border border-gray-400">
        <table class="w-full text-xs text-center border-collapse">
            <thead>
                <tr class="bg-gray-400 text-gray-900 font-bold uppercase">
                    <th class="border border-gray-500 p-1" rowspan="2">NO</th>
                    <th class="border border-gray-500 p-1" rowspan="2">DIVISION</th>
                    <th class="border border-gray-500 p-1" colspan="12">{{ $bulanFilter }}</th>
                    <th class="border border-gray-500 p-1" rowspan="2">TOTAL</th>
                    <th class="border border-gray-500 p-1 bg-gray-300" rowspan="2">GRAND TOTAL</th>
                    <!-- KOLOM BARU: AKSI -->
                    <th class="border border-gray-500 p-1 bg-gray-300" rowspan="2">AKSI</th>
                </tr>
                <tr class="bg-gray-300 text-gray-800 font-semibold uppercase">
                    <th class="border border-gray-500 p-1">AUS</th>
                    <th class="border border-gray-500 p-1">ERORR</th>
                    <th class="border border-gray-500 p-1">GOMPAL</th>
                    <th class="border border-gray-500 p-1">HILANG</th>
                    <th class="border border-gray-500 p-1">KARAT</th>
                    <th class="border border-gray-500 p-1">MACET</th>
                    <th class="border border-gray-500 p-1">MIN STD</th>
                    <th class="border border-gray-500 p-1">OVER STD</th>
                    <th class="border border-gray-500 p-1">PATAH</th>
                    <th class="border border-gray-500 p-1">RUSAK</th>
                    <th class="border border-gray-500 p-1">SERET</th>
                    <th class="border border-gray-500 p-1">LAIN-LAIN</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($sortedData as $division => $data)
                <tr class="hover:bg-gray-100">
                    <td class="border border-gray-400 p-1 font-semibold bg-gray-50">{{ $no++ }}</td>
                    <td class="border border-gray-400 p-1 text-left font-semibold bg-gray-50 whitespace-nowrap">{{ $division }}</td>
                    
                    @foreach($jenisKerusakan as $jenis)
                        <td class="border border-gray-400 p-1">{{ $data[$jenis] ?: '' }}</td>
                    @endforeach
                    
                    <td class="border border-gray-400 p-1 font-bold">{{ $data['total'] ?: '' }}</td>
                    <td class="border border-gray-400 p-1 font-bold bg-gray-100">{{ $data['grand_total'] ?: '' }}</td>
                    
                    <!-- KOLOM BARU: TOMBOL EDIT & HAPUS -->
                    <td class="border border-gray-400 p-1 text-center whitespace-nowrap">
                        @php
                            // Cari ID asli dari database berdasarkan filter yang sedang aktif
                            $dbData = \App\Models\ToolDefect::where('tahun', $tahun)
                                        ->where('bulan', $bulanFilter)
                                        ->where('division', $division)
                                        ->first();
                        @endphp

                        @if($dbData)
                            <div class="flex justify-center gap-1">
                                <a href="{{ route('defects.edit', $dbData->id) }}?tahun={{ $tahun }}&bulan={{ $bulanFilter }}" class="bg-amber-400 text-white px-1.5 py-0.5 rounded text-[10px] font-bold hover:bg-amber-500">EDIT</a>
                                <form action="{{ route('defects.destroy', $dbData->id) }}" method="POST" onsubmit="return confirm('Hapus data untuk divisi {{ $division }} bulan {{ $bulanFilter }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-500 text-white px-1.5 py-0.5 rounded text-[10px] font-bold hover:bg-rose-600">HAPUS</button>
                                </form>
                            </div>
                        @else
                            <span class="text-[10px] text-gray-400 italic">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-400 font-bold text-gray-900">
                    <td colspan="2" class="border border-gray-500 p-1 text-right uppercase">TOTAL</td>
                    @foreach($jenisKerusakan as $jenis)
                        <td class="border border-gray-500 p-1">{{ $footerTotals[$jenis] ?: '' }}</td>
                    @endforeach
                    <td class="border border-gray-500 p-1">{{ $footerTotals['total'] ?: '' }}</td>
                    <td class="border border-gray-500 p-1 bg-gray-300">{{ $footerTotals['grand_total'] ?: '' }}</td>
                    <!-- Kolom Aksi di Footer (Kosong) -->
                    <td class="border border-gray-500 p-1 bg-gray-300"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection