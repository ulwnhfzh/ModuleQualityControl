@extends('layouts.app')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 rounded mb-3 md:mb-4 px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 rounded mb-3 md:mb-4 px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
    {{ session('error') }}
</div>
@endif

<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-4 p-3 sm:p-4">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <form method="GET" action="{{ route('mesin.index') }}" class="flex items-center gap-2">
            <label class="text-[10px] sm:text-xs font-semibold text-gray-500 whitespace-nowrap">TAHUN</label>
            <select name="tahun" onchange="this.form.submit()"
                    class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1.5 sm:px-3 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>

        <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-3 flex-wrap">
            <p class="text-gray-600 text-[10px] sm:text-xs whitespace-nowrap">
                Total {{ $machines->count() }} mesin alat ukur
            </p>

            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('mesin.import.form') }}"
                   class="bg-white border border-gray-300 text-gray-700 rounded font-semibold hover:bg-gray-50 transition inline-flex items-center gap-1 whitespace-nowrap
                          px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">
                    📥 Import
                </a>

                <div class="relative inline-block" id="exportDropdownMesin">
                    <button type="button" onclick="toggleExportMesin()"
                            class="bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition inline-flex items-center gap-1 whitespace-nowrap
                                   px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">
                        📤 Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="exportMenuMesin"
                         class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">

                        <a href="{{ route('mesin.export.excel', ['tahun' => $tahun]) }}"
                           class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-green-50 transition border-b border-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0">
                                <path fill="#1D6F42" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/>
                                <path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/>
                                <text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="9">X</text>
                            </svg>
                            <span class="font-medium">Export Excel</span>
                        </a>

                        <a href="{{ route('mesin.export.pdf', ['tahun' => $tahun]) }}"
                           class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-red-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0">
                                <path fill="#DC2626" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/>
                                <path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/>
                                <text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="7">PDF</text>
                            </svg>
                            <span class="font-medium">Export PDF</span>
                        </a>
                    </div>
                </div>
            @endif

            <a href="{{ route('mesin.create') }}"
               class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition inline-flex items-center gap-1 whitespace-nowrap
                      px-3 py-1.5 text-[11px] sm:px-4 sm:py-2 sm:text-sm">
                <span>+</span> Tambah Mesin Alat Ukur
            </a>
        </div>
    </div>
</div>

<p class="text-[10px] text-gray-400 mb-1 md:hidden flex items-center gap-1">
    <span>←</span> Geser tabel ke kanan untuk lihat semua kolom <span>→</span>
</p>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1400px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs min-w-[150px]">Tool / Machine Name</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Machine Model</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Code Number</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Serial</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Maker</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No. Machine</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Retension</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Start Calibration</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Next Calibration</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Jan</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Feb</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Mar</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Apr</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">May</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Jun</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Jul</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Aug</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Sep</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Oct</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Nov</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">Dec</th>
                    <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs text-center bg-emerald-600 text-white">Total</th>
                    <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs text-center bg-gray-800 text-white sticky right-0 z-10">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($machines as $index => $machine)
                <tr class="border-b {{ $index % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-blue-50">
                    <td class="px-2 py-2 font-semibold text-[11px] md:text-xs">{{ $index + 1 }}</td>
                    <td class="px-2 py-2 font-medium text-[11px] md:text-xs">{{ strtoupper($machine->tool_name) }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->machine_model ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->code_number ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->serial_number ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->maker ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->no_machine ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->retension ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $machine->start_date ?? '-' }}</td>
                    <td class="px-2 py-2 text-gray-600 font-medium text-blue-600 text-[11px] md:text-xs">{{ $machine->next_date ?? '-' }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->jan ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->feb ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->mar ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->apr ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->may ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->jun ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->jul ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->aug ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->sep ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->oct ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->nov ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $machine->dec ?? 0 }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50 text-[11px] md:text-xs">{{ $machine->total ?? 0 }}</td>
                    <td class="px-2 py-2 text-center bg-white sticky right-0 z-10 border-l border-gray-200">
                        @if(Auth::check() && Auth::user()->role === 'admin')
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('mesin.edit', $machine->id) }}"
                               class="bg-yellow-400 text-white rounded hover:bg-yellow-500 transition px-2 py-1 text-[10px]">Edit</a>
                            <form action="{{ route('mesin.destroy', $machine->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white rounded hover:bg-red-600 transition px-2 py-1 text-[10px]">Hapus</button>
                            </form>
                        </div>
                        @else
                        <span class="text-gray-400 text-[10px]">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="24" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm px-4">
                        Belum ada data mesin alat ukur untuk tahun <strong>{{ $tahun }}</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($machines->count() > 0)
            <tfoot>
                <tr class="bg-emerald-50 font-bold text-emerald-900 border-t-2 border-emerald-300">
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs uppercase tracking-wide">TOTAL</td>
                    <td colspan="9" class="px-2 py-3"></td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('jan') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('feb') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('mar') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('apr') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('may') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('jun') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('jul') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('aug') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('sep') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('oct') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('nov') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs">{{ $machines->sum('dec') }}</td>
                    <td class="px-2 py-3 text-center text-[11px] md:text-xs bg-emerald-100">{{ $machines->sum('total') }}</td>
                    <td class="px-2 py-3 bg-emerald-50 sticky right-0 z-10 border-l border-emerald-300"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<script>
function toggleExportMesin() {
    document.getElementById('exportMenuMesin').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdownMesin');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('exportMenuMesin').classList.add('hidden');
    }
});
</script>

@endsection