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

<div class="feature-toolbar bg-white rounded-lg shadow-sm mb-3 md:mb-4 p-3 sm:p-4">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">

        <form method="GET" action="{{ route('master-calibrator.index') }}" class="flex items-center gap-2 flex-wrap">
            @if(request('sidebar_context') === 'external-list-up')
                <input type="hidden" name="sidebar_context" value="external-list-up">
            @endif
            <label for="kategori" class="text-[10px] sm:text-xs font-semibold text-gray-500 whitespace-nowrap">KATEGORI</label>
            <select id="kategori" aria-label="Kategori kalibrasi eksternal"
                    onchange="this.form.action = this.value; this.form.submit()"
                    class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1.5 sm:px-3 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="{{ route('eksternal.small', ['tahun' => $tahun] + (request('sidebar_context') === 'external-list-up' ? ['sidebar_context' => 'external-list-up'] : [])) }}">Small Tool</option>
                <option value="{{ route('mesin.index', ['tahun' => $tahun] + (request('sidebar_context') === 'external-list-up' ? ['sidebar_context' => 'external-list-up'] : [])) }}">Machine Alat Ukur</option>
                <option value="{{ route('master-calibrator.index') }}" selected>Master Kalibrator</option>
            </select>

            <label for="tahun" class="text-[10px] sm:text-xs font-semibold text-gray-500 whitespace-nowrap ml-2">TAHUN</label>
            <select id="tahun" name="tahun" onchange="this.form.submit()" class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1.5 sm:px-3 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>

        <div class="feature-toolbar-actions flex items-center justify-between sm:justify-end gap-2 sm:gap-3 flex-wrap">
            <p class="text-gray-600 text-[10px] sm:text-xs whitespace-nowrap">
                Total {{ $schedules->count() }} alat
            </p>

            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('master-calibrator.import.form', ['tahun' => $tahun] + (request('sidebar_context') === 'external-list-up' ? ['sidebar_context' => 'external-list-up'] : [])) }}"
                   class="bg-white border border-gray-300 text-gray-700 rounded font-semibold hover:bg-gray-50 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">
                    @include('components.icons.import') Import
                </a>

                <div class="relative inline-block" id="exportDropdownMasterCalibrator">
                    <button type="button" onclick="toggleExportMasterCalibrator()"
                            class="bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">
                        @include('components.icons.export') Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="exportMenuMasterCalibrator" class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                        <a href="{{ route('master-calibrator.export', ['tahun' => $tahun]) }}"
                           class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-blue-50 transition border-b border-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0">
                                <path fill="#1D6F42" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/>
                                <path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/>
                                <text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="9">X</text>
                            </svg>
                            <span class="font-medium">Export Excel</span>
                        </a>
                        <a href="{{ route('master-calibrator.export.pdf', ['tahun' => $tahun]) }}"
                           class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-red-50 transition">
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-red-600 text-[8px] font-bold text-white">PDF</span>
                            <span class="font-medium">Export PDF</span>
                        </a>
                    </div>
                </div>
            @endif

            <a href="{{ route('master-calibrator.create', ['tahun' => $tahun]) }}"
               class="inline-flex items-center justify-center gap-1 whitespace-nowrap rounded bg-black px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-gray-800 sm:px-3 sm:py-2 sm:text-sm">
                <span>+</span> Tambah Kalibrator
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="p-4 border-b border-slate-200">
        <h2 class="text-lg font-bold text-slate-800">Master Kalibrator — {{ $tahun }}</h2>
        <p class="text-xs text-slate-500 mt-1">Struktur kolom mengikuti workbook master: identitas alat, hasil per bulan, dan catatan.</p>
    </div>

    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1500px]">
            <thead class="table-header uppercase bg-blue-600 text-white">
                <tr>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">No</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Tool Name</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Type</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Size</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Number Seri</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Number Reg</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Part Name</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs">Part Number</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Jan</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Feb</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Mar</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Apr</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">May</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Jun</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Jul</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Aug</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Sep</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Oct</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Nov</th>
                    <th class="px-2 py-2 md:py-3 border-r border-blue-700 text-[10px] md:text-xs text-center">Dec</th>
                    <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs">Note</th>
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs text-center sticky right-0 bg-blue-700">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $item)
                <tr class="border-b {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50">
                    <td class="px-2 py-2 font-semibold text-[11px] md:text-xs">{{ $index + 1 }}</td>
                    <td class="px-2 py-2 font-medium text-[11px] md:text-xs">{{ $item->tool_name }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $item->type ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $item->size ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $item->number_seri ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $item->number_reg ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $item->part_name ?? '-' }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs">{{ $item->part_number ?? '-' }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->jan ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->feb ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->mar ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->apr ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->may ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->jun ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->jul ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->aug ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->sep ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->oct ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->nov ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->dec ?? 0 }}</td>
                    <td class="px-2 py-2 text-[11px] md:text-xs max-w-[200px] truncate" title="{{ $item->note }}">{{ $item->note ?? '-' }}</td>

                    @if(Auth::check() && Auth::user()->role === 'admin')
                    <td class="px-2 py-2 text-center bg-white sticky right-0 border-l border-gray-200">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('master-calibrator.edit', $item->id) }}" class="rounded bg-blue-600 px-2 py-1 text-[10px] text-white hover:bg-blue-700">Edit</a>
                            <form action="{{ route('master-calibrator.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
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
                    <td colspan="21" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm px-4">
                        Belum ada data Master Kalibrator untuk tahun <strong>{{ $tahun }}</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleExportMasterCalibrator() {
    document.getElementById('exportMenuMasterCalibrator').classList.toggle('hidden');
}
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('exportDropdownMasterCalibrator');
    const menu = document.getElementById('exportMenuMasterCalibrator');
    if (dropdown && !dropdown.contains(event.target)) {
        menu.classList.add('hidden');
    }
});
</script>
@endsection