@extends('layouts.app')

@section('content')

<!-- NOTIFIKASI -->
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 rounded mb-3 md:mb-4
            px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
    {{ session('success') }}
</div>
@endif

<!-- HEADER + TOGGLE + FILTER -->
<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-3 py-2.5 sm:px-4 sm:py-3">
    <div class="flex flex-col gap-2.5">

        <!-- Baris 1: Judul + Toggle Mode -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-sm sm:text-base font-bold text-gray-800 whitespace-nowrap">
                Jadwal Verifikasi Internal
            </h1>

            <div class="flex bg-gray-100 rounded-lg p-0.5 w-full sm:w-auto sm:inline-flex">
                <a href="{{ route('verifikasi.index', ['tahun' => $tahun, 'mode' => 'bulanan']) }}"
                class="flex-1 sm:flex-initial text-center rounded transition
                        px-3 py-1 text-[11px] sm:text-xs font-semibold
                        {{ $mode == 'bulanan' ? 'bg-black text-white shadow' : 'text-gray-600 hover:text-black' }}">
                    📅 Tahunan
                </a>
                <a href="{{ route('verifikasi.index', ['tahun' => $tahun, 'mode' => 'harian']) }}"
                class="flex-1 sm:flex-initial text-center rounded transition
                        px-3 py-1 text-[11px] sm:text-xs font-semibold
                        {{ $mode == 'harian' ? 'bg-black text-white shadow' : 'text-gray-600 hover:text-black' }}">
                    📆 Bulanan
                </a>
            </div>
        </div>

        <!-- Baris 2: Filter + Total + Tombol -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 md:gap-3">
            <form method="GET" action="{{ route('verifikasi.index') }}" class="flex items-center gap-1.5">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <label class="text-[10px] sm:text-xs font-semibold text-gray-500">TAHUN</label>
                <select name="tahun" onchange="this.form.submit()"
                        class="border border-gray-300 rounded text-xs sm:text-sm bg-white
                               px-2 py-1 sm:px-2.5 sm:py-1.5
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>YEAR {{ $y }}</option>
                    @endfor
                </select>
            </form>

            <div class="flex items-center justify-between md:justify-end gap-2 sm:gap-3">
                <span class="text-[10px] sm:text-xs text-gray-500 whitespace-nowrap">
                    Total <strong class="text-gray-700">{{ $schedules->count() }}</strong>
                </span>
                <a href="{{ route('verifikasi.create', ['mode' => $mode]) }}"
                   class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition inline-flex items-center gap-1 whitespace-nowrap
                          px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">
                    <span>+</span> Tambah
                </a>
            </div>
        </div>
    </div>
</div>

<!-- HINT SCROLL -->
<p class="text-[10px] text-gray-400 mb-1 md:hidden flex items-center gap-1">
    <span>←</span> Geser tabel ke kanan untuk lihat semua kolom <span>→</span>
</p>

<!-- TABEL -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto" style="-webkit-overflow-scrolling: touch;">

        @if($mode == 'bulanan')
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1100px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs min-w-[180px]">Tool Name</th>
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
                @forelse($schedules as $index => $item)
                <tr class="border-b {{ $index % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-blue-50">
                    <td class="px-2 py-2 font-semibold text-[11px] md:text-xs">{{ $index + 1 }}</td>
                    <td class="px-2 py-2 font-medium text-[11px] md:text-xs">{{ strtoupper($item->tool_name) }}</td>
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
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50 text-[11px] md:text-xs">{{ $item->total ?? 0 }}</td>
                    <td class="px-2 py-2 text-center bg-white sticky right-0 z-10 border-l border-gray-200">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('verifikasi.edit', $item->id) }}"
                               class="bg-yellow-400 text-white rounded hover:bg-yellow-500 transition px-2 py-1 text-[10px]">Edit</a>
                            <form action="{{ route('verifikasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white rounded hover:bg-red-600 transition px-2 py-1 text-[10px]">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm px-4">
                        Belum ada jadwal verifikasi bulanan untuk tahun <strong>{{ $tahun }}</strong>.<br class="sm:hidden">
                        Silakan klik <strong>+ Tambah</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($schedules->count() > 0)
            <tfoot>
                <tr class="bg-emerald-100 font-bold border-t-2 border-emerald-300 text-emerald-900">
                    <td class="px-2 py-2 md:py-3 text-[11px] md:text-xs" colspan="2">TOTAL</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('jan') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('feb') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('mar') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('apr') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('may') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('jun') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('jul') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('aug') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('sep') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('oct') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('nov') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('dec') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center bg-emerald-200 text-[11px] md:text-xs">{{ $schedules->sum('total') }}</td>
                    <td class="px-2 py-2 md:py-3 bg-emerald-100 sticky right-0 z-10 border-l border-emerald-300"></td>
                </tr>
            </tfoot>
            @endif
        </table>

        @else
        <table class="w-full text-xs text-left whitespace-nowrap min-w-[1400px]">
            <thead class="table-header uppercase">
                <tr>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">No</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs min-w-[150px]">Tool Name</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs">Tanggal</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">QA 1</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">QA 1.2</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">QA INC</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">QA INSP</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">PPIC 1</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">PPIC 2</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">LINE SCC</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P1</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P2</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P3</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P5</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P6</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P7</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">P8</th>
                    <th class="px-2 py-2 md:py-3 border-r border-gray-700 text-[10px] md:text-xs text-center">ENG</th>
                    <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs text-center bg-emerald-600 text-white">Total</th>
                    <th class="px-2 py-2 md:py-3 text-[10px] md:text-xs text-center bg-gray-800 text-white sticky right-0 z-10">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $item)
                <tr class="border-b {{ $index % 2 == 0 ? 'table-row-even' : 'table-row-odd' }} hover:bg-blue-50">
                    <td class="px-2 py-2 font-semibold text-[11px] md:text-xs">{{ $index + 1 }}</td>
                    <td class="px-2 py-2 font-medium text-[11px] md:text-xs">{{ strtoupper($item->tool_name) }}</td>
                    <td class="px-2 py-2 text-gray-600 text-[11px] md:text-xs">{{ $item->tanggal ?? '-' }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->qa_1 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->qa_1_2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->qa_inc ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->qa_insp ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->ppic_1 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->ppic_2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->line_scc ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p1 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p2 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p3 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p5 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p6 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p7 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->p8 ?? 0 }}</td>
                    <td class="px-2 py-2 text-center text-[11px] md:text-xs">{{ $item->eng ?? 0 }}</td>
                    <td class="px-2 py-2 text-center font-bold text-emerald-700 bg-emerald-50 text-[11px] md:text-xs">{{ $item->total ?? 0 }}</td>
                    <td class="px-2 py-2 text-center bg-white sticky right-0 z-10 border-l border-gray-200">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('verifikasi.edit', $item->id) }}"
                               class="bg-yellow-400 text-white rounded hover:bg-yellow-500 transition px-2 py-1 text-[10px]">Edit</a>
                            <form action="{{ route('verifikasi.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white rounded hover:bg-red-600 transition px-2 py-1 text-[10px]">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm px-4">
                        Belum ada jadwal verifikasi harian untuk tahun <strong>{{ $tahun }}</strong>.<br class="sm:hidden">
                        Silakan klik <strong>+ Tambah</strong>.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($schedules->count() > 0)
            <tfoot>
                <tr class="bg-emerald-100 font-bold border-t-2 border-emerald-300 text-emerald-900">
                    <td class="px-2 py-2 md:py-3 text-[11px] md:text-xs" colspan="3">TOTAL</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('qa_1') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('qa_1_2') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('qa_inc') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('qa_insp') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('ppic_1') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('ppic_2') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('line_scc') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p1') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p2') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p3') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p5') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p6') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p7') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('p8') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center text-[11px] md:text-xs">{{ $schedules->sum('eng') }}</td>
                    <td class="px-2 py-2 md:py-3 text-center bg-emerald-200 text-[11px] md:text-xs">{{ $schedules->sum('total') }}</td>
                    <td class="px-2 py-2 md:py-3 bg-emerald-100 sticky right-0 z-10 border-l border-emerald-300"></td>
                </tr>
            </tfoot>
            @endif
        </table>
        @endif
    </div>
</div>

@endsection