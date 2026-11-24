@extends('layouts.app')

@section('content')
<!-- TOOLBAR (Kotak Putih) -->
<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    <div class="feature-toolbar-layout flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <!-- KIRI: Judul dan Filter -->
        <div class="flex min-w-0 flex-col gap-3 xl:flex-row xl:items-end xl:gap-6">
            <h1 class="text-base font-bold text-slate-800 whitespace-nowrap sm:text-lg">Data All Produksi</h1>

            <form id="filter-form" method="GET" action="{{ route('data-all-produksi.index') }}" class="flex w-full min-w-0 flex-col gap-3 xl:w-auto xl:flex-row xl:items-end">
                <div class="grid w-full grid-cols-2 gap-2 sm:grid-cols-4 xl:flex xl:w-auto xl:flex-wrap xl:items-end xl:gap-3">
                    <div class="flex min-w-0 flex-col gap-1 xl:flex-row xl:items-center xl:gap-1.5">
                        <label for="year-select" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 whitespace-nowrap">YEAR</label>
                        <select id="year-select" name="year" onchange="this.form.submit()" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 xl:w-[88px]">
                            @foreach($yearOptions as $year)
                                <option value="{{ $year }}" {{ $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex min-w-0 flex-col gap-1 xl:flex-row xl:items-center xl:gap-1.5">
                        <label for="division-select" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 whitespace-nowrap">DIVISION</label>
                        <select id="division-select" name="division" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 xl:w-[120px]">
                            <option value="ALL" {{ $selectedDivision === 'ALL' ? 'selected' : '' }}>ALL DIVISION</option>
                            @foreach($divisionList as $division)
                                <option value="{{ $division }}" {{ $selectedDivision === $division ? 'selected' : '' }}>{{ $division }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex min-w-0 flex-col gap-1 xl:flex-row xl:items-center xl:gap-1.5">
                        <label for="category-select" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 whitespace-nowrap">CATEGORY</label>
                        <select id="category-select" name="category" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 xl:w-[140px]">
                            <option value="ALL" {{ $selectedCategory === 'ALL' ? 'selected' : '' }}>ALL CATEGORY</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-2 flex min-w-0 flex-col gap-1 sm:col-span-1 xl:flex-row xl:items-center xl:gap-1.5">
                        <label for="tool-select" class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 whitespace-nowrap">TOOL NAME</label>
                        <select id="tool-select" name="tool_name" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 xl:w-[170px]">
                            <option value="">ALL TOOL</option>
                            @foreach($toolNameList as $toolName)
                                <option value="{{ $toolName }}" {{ $searchTool === $toolName ? 'selected' : '' }}>{{ $toolName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-900 xl:w-auto xl:py-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    Terapkan
                </button>
            </form>
        </div>

        <!-- KANAN: Total dan Tombol Aksi -->
        <div class="flex flex-wrap items-center justify-start gap-2 lg:justify-end">
            <p class="mr-1 text-xs text-slate-500">Halaman ini <span class="font-bold text-slate-700">{{ $data->count() }}</span> baris</p>
            <a href="{{ route('data-all-produksi.import.form', ['year' => $selectedYear]) }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-50 sm:text-sm">@include('components.icons.import') Import</a>
            <a href="{{ route('data-all-produksi.export', ['division' => $selectedDivision, 'category' => $selectedCategory, 'year' => $selectedYear]) }}" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 sm:text-sm">@include('components.icons.export') Export</a>
            <a href="{{ route('data-all-produksi.create', ['year' => $selectedYear]) }}" class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded bg-black px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-gray-800 sm:text-sm"><span>+</span> Tambah</a>
        </div>
    </div>
</div>

<!-- TABEL DATA: tabel mengisi layar, header diam, hanya baris data yang bergerak -->
<div class="w-full min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="table-sticky-scroll table-fill-screen max-w-full w-full min-w-0">
        <table class="data-grid w-full min-w-[1800px] table-fixed border-collapse text-xs text-slate-700">
            <colgroup>
                <col style="width:40px">
                @if($selectedDivision === 'ALL')<col style="width:100px">@endif
                <col style="width:150px"><col style="width:85px"><col style="width:95px">
                <col style="width:90px"><col style="width:90px"><col style="width:130px"><col style="width:125px">
                @for($monthColumn = 0; $monthColumn < 12; $monthColumn++)<col style="width:52px">@endfor
                <col style="width:150px"><col style="width:121px">
            </colgroup>
            <thead class="bg-slate-800 text-white text-center">
                <tr class="uppercase tracking-wide text-[11px]">
                    <th rowspan="2" class="sticky-col border px-2 py-3 align-middle" style="left:0">No</th>
                    @if($selectedDivision === 'ALL')
                        <th rowspan="2" class="sticky-col border px-2 py-3 text-center align-middle" style="left:40px">Division</th>
                    @endif
                    <th rowspan="2" class="border px-2 py-3 text-center align-middle">Tool Name</th>
                    <th rowspan="2" class="border px-2 py-3 align-middle">Type</th>
                    <th rowspan="2" class="border px-2 py-3 align-middle">Size</th>
                    <th rowspan="2" class="border px-2 py-3 align-middle">Number Seri</th>
                    <th rowspan="2" class="border px-2 py-3 align-middle">Number Reg</th>
                    <th rowspan="2" class="border px-2 py-3 text-center align-middle">Part Name</th>
                    <th rowspan="2" class="border px-2 py-3 text-center align-middle">Part Number</th>
                    <th colspan="12" class="border px-2 py-2">Result</th>
                    <th rowspan="2" class="border px-2 py-3 align-middle">Note</th>
                    <th rowspan="2" class="border px-2 py-3 align-middle text-center">Aksi</th>
                </tr>
                <tr class="uppercase text-[10px]">
                    <th class="border px-1 py-2">JAN</th>
                    <th class="border px-1 py-2">FEB</th>
                    <th class="border px-1 py-2">MAR</th>
                    <th class="border px-1 py-2">APR</th>
                    <th class="border px-1 py-2">MEI</th>
                    <th class="border px-1 py-2">JUN</th>
                    <th class="border px-1 py-2">JUL</th>
                    <th class="border px-1 py-2">AUG</th>
                    <th class="border px-1 py-2">SEP</th>
                    <th class="border px-1 py-2">OCT</th>
                    <th class="border px-1 py-2">NOV</th>
                    <th class="border px-1 py-2">DEC</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    @php($hasNg = collect(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'])->contains(fn ($month) => $item->resultStatus($month) === 'NG'))
                    <tr class="{{ $hasNg ? 'bg-rose-100 text-rose-900 hover:bg-rose-200' : ($index % 2 ? 'bg-slate-50' : 'bg-white') . ' hover:bg-blue-50/50' }} text-center">
                        <td class="sticky-col border px-2 py-3 {{ $hasNg ? 'bg-rose-100' : ($index % 2 ? 'bg-slate-50' : 'bg-white') }}" style="left:0">{{ ($data->firstItem() ?? 1) + $index }}</td>

                        @if($selectedDivision === 'ALL')
                            <td class="sticky-col border px-2 py-3 {{ $hasNg ? 'bg-rose-100' : ($index % 2 ? 'bg-slate-50' : 'bg-white') }} font-semibold text-blue-700" style="left:40px">{{ $item->division }}</td>
                        @endif

                        <td class="border px-2 py-3 font-semibold text-slate-800"><span class="block truncate" title="{{ $item->tool_name }}">{{ $item->tool_name }}</span></td>
                        <td class="border px-2 py-3">{{ $item->type }}</td>
                        <td class="border px-2 py-3">{{ $item->size }}</td>
                        <td class="border px-2 py-3">{{ $item->number_seri ?: '-' }}</td>
                        <td class="border px-2 py-3">{{ $item->number_reg ?: '-' }}</td>
                        <td class="border px-2 py-3"><span class="block truncate" title="{{ $item->part_name ?: '-' }}">{{ $item->part_name ?: '-' }}</span></td>
                        <td class="border px-2 py-3"><span class="block truncate" title="{{ $item->part_number ?: '-' }}">{{ $item->part_number ?: '-' }}</span></td>

                        @foreach(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'] as $month)
                            @php($resultStatus = $item->resultStatus($month))
                            <td class="border px-2 py-3">
                                @if($resultStatus === 'NG')
                                    <span class="rounded bg-rose-100 px-2 py-1 font-bold text-rose-700">NG</span>
                                @elseif($resultStatus === 'OK')
                                    <span class="rounded bg-blue-100 px-2 py-1 font-semibold text-blue-700">OK</span>
                                @else
                                    {{ $item->$month ?: '' }}
                                @endif
                            </td>
                        @endforeach

                        <td class="border px-2 py-3 text-left"><span class="block truncate" title="{{ $item->note ?: '-' }}">{{ $item->note ?: '-' }}</span></td>

                        <td class="border px-2 py-3 text-center">
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
        const categorySelect = document.getElementById('category-select');
        const toolSelect = document.getElementById('tool-select');
        const yearSelect = document.getElementById('year-select');
        if (!divisionSelect || !toolSelect) return;

        divisionSelect.addEventListener('change', async function () {
            const division = divisionSelect.value;
            const currentTool = toolSelect.value;
            toolSelect.replaceChildren(new Option('Memuat data...', ''));

            try {
                const url = new URL(@json(route('data-all-produksi.get-tools')), window.location.origin);
                url.searchParams.set('division', division);
                if (categorySelect) url.searchParams.set('category', categorySelect.value);
                if (yearSelect) url.searchParams.set('year', yearSelect.value);
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('Request gagal');

                const tools = await response.json();
                toolSelect.replaceChildren(new Option('All Tool', ''));
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