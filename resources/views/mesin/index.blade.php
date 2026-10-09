@extends('layouts.app')

@section('content')

<!-- NOTIFIKASI SUKSES -->
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 rounded mb-3 md:mb-4
            px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
    {{ session('success') }}
</div>
@endif

<!-- FILTER & ACTION BAR -->
<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-4 p-3 sm:p-4">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <form method="GET" action="{{ route('mesin.index') }}" class="flex items-center gap-2">
            <label class="text-[10px] sm:text-xs font-semibold text-gray-500 whitespace-nowrap">TAHUN</label>
            <select name="tahun" onchange="this.form.submit()"
                    class="border border-gray-300 rounded text-xs sm:text-sm bg-white
                           px-2 py-1.5 sm:px-3 sm:py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>

        <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-3">
            <p class="text-gray-600 text-[10px] sm:text-xs whitespace-nowrap">
                Total {{ $machines->count() }} mesin alat ukur
            </p>
            <a href="{{ route('mesin.create') }}"
               class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition inline-flex items-center gap-1 whitespace-nowrap
                      px-3 py-1.5 text-[11px] sm:px-4 sm:py-2 sm:text-sm">
                <span>+</span> Tambah Mesin Alat Ukur
            </a>
        </div>
    </div>
</div>

<!-- HINT SCROLL DI HP -->
<p class="text-[10px] text-gray-400 mb-1 md:hidden flex items-center gap-1">
    <span>←</span> Geser tabel ke kanan untuk lihat semua kolom <span>→</span>
</p>

<!-- TABEL MESIN -->
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
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Start</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Next</th>
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
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('mesin.edit', $machine->id) }}"
                               class="bg-yellow-400 text-white rounded hover:bg-yellow-500 transition px-2 py-1 text-[10px]">Edit</a>
                            <form action="{{ route('mesin.destroy', $machine->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white rounded hover:bg-red-600 transition px-2 py-1 text-[10px]">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="24" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm px-4">
                        Belum ada data mesin untuk tahun <strong>{{ $tahun }}</strong>.<br class="sm:hidden">
                        Silakan klik <strong>+ Tambah Mesin</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection