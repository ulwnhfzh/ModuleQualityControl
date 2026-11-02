@extends('layouts.app')

@section('content')
<div class="feature-toolbar relative z-10 mb-3 overflow-visible bg-white px-3 py-2.5 shadow-sm sm:mb-4 sm:px-4 sm:py-3">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex min-w-0 flex-wrap items-center gap-3">
            <h1 class="text-sm font-bold uppercase text-slate-800 sm:text-base">Data NG</h1>
            <form method="GET" action="{{ route('defects.index') }}" class="flex flex-wrap items-center gap-2 sm:gap-3">
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-500 sm:text-xs">
                    TAHUN
                    <select name="tahun" onchange="this.form.submit()" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs sm:px-2.5 sm:py-1.5 sm:text-sm">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </label>
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-500 sm:text-xs">
                    BULAN
                    <select name="bulan" onchange="this.form.submit()" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs sm:px-2.5 sm:py-1.5 sm:text-sm">
                        @foreach(['JAN'=>'JANUARI','FEB'=>'FEBRUARI','MAR'=>'MARET','APR'=>'APRIL','MAY'=>'MEI','JUN'=>'JUNI','JUL'=>'JULI','AUG'=>'AGUSTUS','SEP'=>'SEPTEMBER','OCT'=>'OKTOBER','NOV'=>'NOVEMBER','DEC'=>'DESEMBER'] as $k => $v)
                            <option value="{{ $k }}" {{ $bulanFilter == $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </label>
            </form>
        </div>

        <div class="feature-toolbar-actions flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('defects.import.form') }}" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-50 sm:px-3 sm:py-2 sm:text-sm">
                @include('components.icons.import') Import
            </a>
            <div class="relative inline-block" id="exportDropdownNg">
                <button type="button" onclick="toggleExportNg()" aria-controls="exportMenuNg" aria-expanded="false"
                        class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 sm:px-3 sm:py-2 sm:text-sm">
                    @include('components.icons.export') Export
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="exportMenuNg"
                     class="absolute right-0 z-50 mt-1 hidden w-44 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                    <a href="{{ route('defects.export.excel', ['tahun' => $tahun, 'bulan' => $bulanFilter]) }}"
                       class="flex items-center gap-2 px-3 py-2.5 text-xs font-medium text-slate-700 transition hover:bg-blue-50">
                        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-emerald-700 text-[10px] font-bold text-white" aria-hidden="true">X</span> Export Excel
                    </a>
                    <a href="{{ route('defects.export.pdf', ['tahun' => $tahun, 'bulan' => $bulanFilter]) }}"
                       class="flex items-center gap-2 border-t border-gray-100 px-3 py-2.5 text-xs font-medium text-slate-700 transition hover:bg-blue-50">
                        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-rose-600 text-[8px] font-bold text-white" aria-hidden="true">PDF</span> Export PDF
                    </a>
                </div>
            </div>
            <a href="{{ route('defects.create') }}" class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded bg-blue-600 px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 sm:px-4 sm:py-2 sm:text-sm">
                <span>+</span> Tambah
            </a>
        </div>
    </div>
</div>

<div class="overflow-hidden rounded-lg bg-white p-3 shadow-sm sm:p-6">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <!-- TABEL REKAPITULASI -->
    <div class="overflow-x-auto rounded-lg border border-slate-200">
        <table class="w-full min-w-[1100px] border-collapse text-center text-xs text-slate-700">
            <thead>
                <tr class="font-bold uppercase">
                    <th class="border border-slate-600 p-2" rowspan="2">NO</th>
                    <th class="border border-slate-600 p-2" rowspan="2">DIVISION</th>
                    <th class="border border-slate-600 p-2" colspan="12">{{ $bulanFilter }}</th>
                    <th class="border border-slate-600 p-2" rowspan="2">TOTAL</th>
                    <th class="border border-slate-600 p-2" rowspan="2">GRAND TOTAL</th>
                    <th class="border border-slate-600 p-2" rowspan="2">AKSI</th>
                </tr>
                <tr class="font-semibold uppercase">
                    @foreach($jenisKerusakan as $jenis)
                        <th class="border border-slate-300 px-2 py-2">{{ strtoupper(str_replace('_', ' ', $jenis)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($sortedData as $division => $data)
                    @php
                        // Ambil record DB sekali saja per divisi (hindari N+1 di dalam loop sel)
                        $dbData = $existingDefects[$division] ?? null;
                    @endphp
                    <tr class="{{ $no % 2 ? 'bg-white' : 'bg-slate-50' }} hover:bg-blue-50">
                        <td class="border border-slate-200 p-2 font-semibold">{{ $no++ }}</td>
                        <td class="border border-slate-200 p-2 text-left font-semibold whitespace-nowrap">{{ $division }}</td>

                        @foreach($jenisKerusakan as $jenis)
                            <td class="border border-slate-200 p-2">{{ $data[$jenis] ?: '' }}</td>
                        @endforeach

                        <td class="border border-slate-200 p-2 font-bold">{{ $data['total'] ?: '' }}</td>
                        <td class="border border-slate-200 p-2 font-bold">{{ $data['grand_total'] ?: '' }}</td>

                        <td class="border border-slate-200 p-2 text-center whitespace-nowrap">
                            @if($dbData)
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('defects.edit', $dbData->id) }}?tahun={{ $tahun }}&bulan={{ $bulanFilter }}"
                                       class="rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold text-white hover:bg-blue-700">EDIT</a>
                                    <form action="{{ route('defects.destroy', $dbData->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus data untuk divisi {{ $division }} bulan {{ $bulanFilter }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white hover:bg-rose-700">HAPUS</button>
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
                <tr class="font-bold">
                    <td colspan="2" class="border border-slate-300 p-2 text-right uppercase">TOTAL</td>
                    @foreach($jenisKerusakan as $jenis)
                        <td class="border border-slate-300 p-2">{{ $footerTotals[$jenis] ?: '' }}</td>
                    @endforeach
                    <td class="border border-slate-300 p-2">{{ $footerTotals['total'] ?: '' }}</td>
                    <td class="border border-slate-300 p-2">{{ $footerTotals['grand_total'] ?: '' }}</td>
                    <td class="border border-slate-300 p-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<script>
    function toggleExportNg() {
        const dropdown = document.getElementById('exportDropdownNg');
        const menu = document.getElementById('exportMenuNg');
        const button = dropdown?.querySelector('button[aria-controls="exportMenuNg"]');

        if (!menu || !button) {
            return;
        }

        const isOpen = menu.classList.toggle('hidden') === false;
        button.setAttribute('aria-expanded', String(isOpen));
    }

    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('exportDropdownNg');
        const menu = document.getElementById('exportMenuNg');
        const button = dropdown?.querySelector('button[aria-controls="exportMenuNg"]');

        if (dropdown && menu && !dropdown.contains(event.target)) {
            menu.classList.add('hidden');
            button?.setAttribute('aria-expanded', 'false');
        }
    });
</script>
@endsection