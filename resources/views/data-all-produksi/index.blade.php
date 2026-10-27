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
                        <select id="division-select" name="division" class="w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-sm focus:ring-emerald-500 focus:border-emerald-500">
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
                        <select id="tool-select" name="tool_name" class="w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-sm focus:ring-emerald-500 focus:border-emerald-500">
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
            <p class="text-xs text-slate-500 mr-1">Total <span class="font-bold text-slate-700">{{ $data->count() }}</span></p>
            <a href="{{ route('data-all-produksi.import.form') }}" class="bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">📥 Import</a>
            <a href="{{ route('data-all-produksi.export', ['division' => $selectedDivision]) }}" class="bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">📤 Export</a>
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
                        <th rowspan="2" class="border border-slate-600 px-2 py-3 text-center align-middle bg-emerald-800">Division</th>
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
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-emerald-50/50 text-center">
                        <td class="border border-slate-200 px-2 py-3">{{ $index + 1 }}</td>
                        
                        @if($selectedDivision === 'ALL')
                            <td class="border border-slate-200 px-2 py-3 font-semibold text-emerald-700">{{ $item->division }}</td>
                        @endif
                        
                        <td class="border border-slate-200 px-2 py-3 text-left font-semibold text-slate-800">{{ $item->tool_name }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->type }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->size }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->number_seri ?: '-' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->number_reg ?: '-' }}</td>
                        <td class="border border-slate-200 px-2 py-3 text-left">{{ $item->part_name ?: '-' }}</td>
                        <td class="border border-slate-200 px-2 py-3 text-left">{{ $item->part_number ?: '-' }}</td>
                        
                        <td class="border border-slate-200 px-2 py-3">{{ $item->jan ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->feb ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->mar ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->apr ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->may ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->jun ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->jul ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->aug ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->sep ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->oct ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->nov ?: '' }}</td>
                        <td class="border border-slate-200 px-2 py-3">{{ $item->dec ?: '' }}</td>
                        
                        <td class="border border-slate-200 px-2 py-3 text-left">{{ $item->note ?: '-' }}</td>
                        
                        <td class="border border-slate-200 px-2 py-3 text-center">
                            <div class="flex justify-center gap-1">
                                <a href="{{ route('data-all-produksi.edit', $item->id) }}" class="bg-amber-400 text-white px-2 py-1 rounded text-[10px] font-bold hover:bg-amber-500">EDIT</a>
                                <form action="{{ route('data-all-produksi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-500 text-white px-2 py-1 rounded text-[10px] font-bold hover:bg-rose-600">HAPUS</button>
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
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    $(document).ready(function() {
        // ===== FITUR AUTO-LIVE UNTUK DROPDOWN TOOL =====
        // Saat Division diganti, ambil daftar Tool baru TANPA refresh
        $('#division-select').on('change', function() {
            let division = $(this).val();
            let toolSelect = $('#tool-select');
            let currentTool = toolSelect.val();
            
            toolSelect.empty().append('<option value="">Memuat data...</option>');

            $.ajax({
                url: "{{ route('data-all-produksi.get-tools') }}",
                type: "GET",
                data: { division: division },
                success: function(data) {
                    toolSelect.empty().append('<option value="">Semua Tool</option>');
                    $.each(data, function(key, value) {
                        let isSelected = (value === currentTool) ? 'selected' : '';
                        toolSelect.append('<option value="' + value + '" ' + isSelected + '>' + value + '</option>');
                    });
                },
                error: function() {
                    toolSelect.empty().append('<option value="">Gagal memuat data</option>');
                }
            });
        });
    });
</script>
@endpush