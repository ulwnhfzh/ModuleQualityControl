@extends('layouts.app')

@section('content')
<!-- TOOLBAR (Kotak Putih) -->
<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        
        <!-- KIRI: Judul dan Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
            <h1 class="text-lg font-bold text-slate-800 whitespace-nowrap">Data All Produksi</h1>
            
            <!-- Form Filter dengan Tombol Terapkan -->
            <form id="filter-form" method="GET" action="{{ route('data-all-produksi.index') }}" class="flex flex-wrap items-center gap-x-4 gap-y-3">
                
                <!-- Filter Division -->
                <div class="flex items-center gap-2">
                    <label for="division-select" class="text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap mb-0 leading-none">DIVISION</label>
                    <div class="w-full sm:w-auto min-w-[130px]">
                        <select id="division-select" name="division" class="w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="ALL" {{ $selectedDivision === 'ALL' ? 'selected' : '' }}>Semua Division</option>
                            @foreach($divisionList as $division)
                                <option value="{{ $division }}" {{ $selectedDivision === $division ? 'selected' : '' }}>{{ $division }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter Tool Name -->
                <div class="flex items-center gap-2">
                    <label for="tool-select" class="text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap mb-0 leading-none">TOOL NAME</label>
                    <div class="w-full sm:w-auto min-w-[180px]">
                        <select id="tool-select" name="tool_name" class="w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Tool</option>
                            @foreach($toolNameList as $toolName)
                                <option value="{{ $toolName }}" {{ $searchTool === $toolName ? 'selected' : '' }}>{{ $toolName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- TOMBOL TERAPKAN -->
                <button type="submit" class="bg-slate-800 text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-slate-900 transition shadow-sm inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    Terapkan
                </button>
            </form>
        </div>

        <!-- KANAN: Total dan Tombol Aksi -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-end">
            <p class="text-xs text-slate-500 mr-1">Halaman ini <span class="font-bold text-slate-700">{{ $data->count() }}</span> baris</p>
            <a href="{{ route('data-all-produksi.import.form') }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-50 sm:px-3 sm:py-2 sm:text-sm">@include('components.icons.import') Import</a>
            <a href="{{ route('data-all-produksi.export', ['division' => $selectedDivision]) }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 sm:px-3 sm:py-2 sm:text-sm">@include('components.icons.export') Export</a>
            <a href="{{ route('data-all-produksi.create') }}" class="bg-black text-white rounded-lg font-semibold hover:bg-gray-800 transition inline-flex items-center gap-1 whitespace-nowrap px-3 py-1.5 text-xs sm:text-sm"><span>+</span> Tambah</a>
        </div>
    </div>
</div>

<!-- TABEL DATA -->
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1500px] table-auto border-collapse text-xs text-slate-700">
            <thead class="bg-slate-800 text-white text-center">
                <tr class="uppercase tracking-wide text-[11px]">
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle">No</th>
                    @if($selectedDivision === 'ALL')
                        <th rowspan="2" class="border border-slate-600 px-2 py-3 text-center align-middle bg-blue-800">Division</th>
                    @endif
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 text-left align-middle">Tool Name</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle">Type</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle">Size</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle">Number Seri</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle">Number Reg</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 text-left align-middle">Part Name</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 text-left align-middle">Part Number</th>
                    <th colspan="12" class="border border-slate-600 px-2 py-2 bg-slate-700">Result</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle">Note</th>
                    <th rowspan="2" class="border border-slate-600 px-2 py-3 align-middle text-center">Aksi</th>
                </tr>
                <tr class="uppercase text-[10px]">
                    <th class="border border-slate-600 px-1 py-2">JAN</th>
                    <th class="border border-slate-600 px-1 py-2">FEB</th>
                    <th class="border border-slate-600 px-1 py-2">MAR</th>
                    <th class="border border-slate-600 px-1 py-2">APR</th>
                    <th class="border border-slate-600 px-1 py-2">MEI</th>
                    <th class="border border-slate-600 px-1 py-2">JUN</th>
                    <th class="border border-slate-600 px-1 py-2">JUL</th>
                    <th class="border border-slate-600 px-1 py-2">AUG</th>
                    <th class="border border-slate-600 px-1 py-2">SEP</th>
                    <th class="border border-slate-600 px-1 py-2">OCT</th>
                    <th class="border border-slate-600 px-1 py-2">NOV</th>
                    <th class="border border-slate-600 px-1 py-2">DEC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-blue-50/50 text-center">
                        <td class="border border-slate-200 px-2 py-3">{{ ($data->firstItem() ?? 1) + $index }}</td>
                        
                        @if($selectedDivision === 'ALL')
                            <td class="border border-slate-200 px-2 py-3 font-semibold text-blue-700">{{ $item->division }}</td>
                        @endif
                        
                        <td class="border border-slate-200 px-2 py-3 text-left font-semibold text-slate-800">{{ $item->tool_name }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->type }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->size }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->number_seri ?: '-' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->number_reg ?: '-' }}</td>
                        <td class="border border-slate-200 px-2 py-3 text-left">{{ $item->part_name ?: '-' }}</td>
                        <td class="border border-slate-200 px-2 py-3 text-left">{{ $item->part_number ?: '-' }}</td>
                        
                        @foreach(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month)
                            @php($resultStatus = $item->resultStatus($month))
                            <td class="border border-slate-200 px-2 py-3">
                                @if($resultStatus === 'NG')
                                    <span class="rounded bg-rose-100 px-2 py-1 font-bold text-rose-700">NG</span>
                                @elseif($resultStatus === 'OK')
                                    <span class="rounded bg-blue-100 px-2 py-1 font-semibold text-blue-700">OK</span>
                                @else
                                    {{ $item->$month ?: '' }}
                                @endif
                            </td>
                        @endforeach
                        
                        <td class="border border-slate-200 px-2 py-3 text-left">{{ $item->note ?: '-' }}</td>
                        
                        <td class="border border-slate-200 px-2 py-3 text-center">
                            <div class="flex justify-center gap-1">
                                <a href="{{ route('data-all-produksi.edit', $item->id) }}" class="rounded bg-blue-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-blue-700">EDIT</a>
                                <form action="{{ route('data-all-produksi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-rose-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-rose-700">HAPUS</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $selectedDivision === 'ALL' ? '23' : '22' }}" class="px-4 py-12 text-center text-slate-500">
                            Belum ada data untuk pencarian ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
    {{ $data->links() }}
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const divisionSelect = document.getElementById('division-select');
        const toolSelect = document.getElementById('tool-select');
        if (!divisionSelect || !toolSelect) return;

        divisionSelect.addEventListener('change', async function () {
            const division = divisionSelect.value;
            const currentTool = toolSelect.value;
            toolSelect.replaceChildren(new Option('Memuat data...', ''));

            try {
                const url = new URL(@json(route('data-all-produksi.get-tools')), window.location.origin);
                url.searchParams.set('division', division);
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('Request gagal');

                const tools = await response.json();
                toolSelect.replaceChildren(new Option('Semua Tool', ''));
                tools.forEach(function (tool) {
                    const option = new Option(tool, tool);
                    option.selected = tool === currentTool;
                    toolSelect.add(option);
                });
            } catch (error) {
                toolSelect.replaceChildren(new Option('Gagal memuat data', ''));
            }
        });
    });
</script>
@endpush
