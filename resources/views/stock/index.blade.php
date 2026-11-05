@extends('layouts.app')

@section('content')
<div class="stock-page space-y-3 font-sans">
    @php
        $selectedMonthName = $months[$selectedMonth] ?? 'Jan';
        $selectedMonthStart = sprintf('%04d-%02d-01', $tahun, $selectedMonth);
        $selectedMonthEnd = \Illuminate\Support\Carbon::parse($selectedMonthStart)->endOfMonth()->toDateString();
    @endphp
    @if(session('success'))
        <div class="border border-blue-300 bg-blue-50 px-4 py-3 text-sm text-blue-900">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="mb-1 font-semibold">Input belum dapat disimpan:</p>
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <section class="feature-toolbar border border-slate-300 bg-white p-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-lg font-semibold text-slate-900">Data Stock Alat Ukur QA Plant 2</h1>
                <p class="mt-1 text-sm text-slate-600">Persediaan dan pergerakan alat ukur per bulan.</p>
            </div>
            <form method="GET" action="{{ route('stock.index') }}" class="flex flex-wrap items-end gap-2.5">
                <label class="text-xs font-semibold text-gray-600">Tahun
                    <select name="tahun" class="mt-1 block min-h-9 border-slate-400 text-sm">
                        @for($year = date('Y'); $year >= date('Y') - 12; $year--)
                            <option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>
                        @endfor
                    </select>
                </label>
                <label class="text-xs font-semibold text-gray-600">Kategori
                    <select name="kategori" class="mt-1 block min-h-9 border-slate-400 text-sm">
                        @foreach(['Small Tool', 'Machine', 'Jig'] as $option)
                            <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                {{-- DROPDOWN BULAN --}}
                <label class="text-xs font-semibold text-gray-600">Bulan
                    <select name="bulan" class="mt-1 block min-h-9 border-slate-400 text-sm">
                        @foreach($months as $m => $name)
                            <option value="{{ $m }}" @selected($selectedMonth === $m)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="min-h-9 border border-blue-700 bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Tampilkan</button>
            </form>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-3">
            <a href="{{ route('stock.export', ['tahun' => $tahun, 'kategori' => $kategori]) }}" class="inline-flex items-center gap-1.5 border border-blue-700 bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">@include('components.icons.export') Export Excel</a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stock.import.form', ['tahun' => $tahun, 'kategori' => $kategori]) }}" class="inline-flex items-center gap-1.5 border border-blue-600 bg-white px-4 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">@include('components.icons.import') Import</a>
                <button type="button" onclick="document.getElementById('stock-entry-dialog').showModal()" class="border border-blue-700 bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">Tambah Data Stock</button>
            </div>
        </div>
        @endif
    </section>

    @if(Auth::user()->role === 'admin')
    <dialog id="stock-entry-dialog" class="m-auto max-h-[92vh] w-[min(96vw,1100px)] border border-slate-400 p-0 font-sans shadow-xl backdrop:bg-slate-900/50">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-bold text-gray-900">Tambah Data Stock</h2>
                <p class="mt-1 text-xs text-gray-500">Nama alat diinput langsung di sini, tidak mengambil data dari Daftar Induk.</p>
            </div>
            <button type="button" aria-label="Tutup form" onclick="document.getElementById('stock-entry-dialog').close()" class="px-3 py-1 text-xl text-gray-500 hover:bg-gray-100">&times;</button>
        </div>
        <form method="POST" action="{{ route('stock.grid.store') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <input type="hidden" name="bulan" value="{{ $selectedMonth }}">
            <div class="grid grid-cols-1 gap-3 border-y border-gray-100 bg-slate-50 p-4 sm:grid-cols-2 lg:grid-cols-5">
                <label class="text-xs font-semibold text-gray-600">Nama Alat Ukur *
                    <input required name="entries[0][tool_name]" value="{{ old('entries.0.tool_name') }}" placeholder="Contoh: BALL ANVIL" class="mt-1 block w-full rounded border-gray-300 text-sm uppercase">
                </label>
                <label class="text-xs font-semibold text-gray-600">Type *
                    <input required name="entries[0][type]" value="{{ old('entries.0.type') }}" placeholder="Contoh: CARBIDE" class="mt-1 block w-full rounded border-gray-300 text-sm uppercase">
                </label>
                <label class="text-xs font-semibold text-gray-600">Number Order
                    <input name="entries[0][order_number]" value="{{ old('entries.0.order_number') }}" placeholder="Nomor order" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </label>
                <label class="text-xs font-semibold text-gray-600">Spesifikasi
                    <input name="entries[0][specification]" value="{{ old('entries.0.specification') }}" placeholder="Contoh: Ø 2.5" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </label>
                <label class="text-xs font-semibold text-gray-600">Stock Awal
                    <input type="number" min="0" max="2147483647" step="1" name="entries[0][opening_stock]" value="{{ old('entries.0.opening_stock', 0) }}" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </label>
            </div>
            <div class="px-4 pt-3">
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-700">Pergerakan Stock {{ $selectedMonthName }} {{ $tahun }}</h3>
                <p class="mt-1 text-xs text-gray-500">Masukkan jumlah dan tanggal transaksi untuk bulan yang dipilih.</p>
            </div>
            <fieldset class="mx-4 my-3 overflow-hidden border border-slate-300">
                <legend class="ml-3 px-2 text-xs font-bold uppercase tracking-wide text-blue-800">{{ $selectedMonthName }} {{ $tahun }}</legend>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[740px] border-collapse text-xs">
                        <thead>
                            <tr class="bg-blue-600 text-center font-semibold text-white">
                                <th class="px-3 py-2.5">Slot</th><th colspan="2" class="px-3 py-2.5">Barang Masuk</th><th colspan="2" class="px-3 py-2.5">Barang Keluar</th>
                            </tr>
                            <tr class="bg-blue-50 text-center font-semibold text-slate-700">
                                <th class="px-2 py-2">#</th><th class="px-2 py-2">Qty</th><th class="px-2 py-2">Tanggal</th><th class="px-2 py-2">Qty</th><th class="px-2 py-2">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(range(1, 4) as $slot)
                                <tr class="border-t border-slate-200">
                                    <td class="px-2 py-2 text-center font-medium text-slate-500">{{ $slot }}</td>
                                    @foreach(['in', 'out'] as $direction)
                                        <td class="px-2 py-2 text-center">
                                            <input type="number" min="0" max="2147483647" step="1" inputmode="numeric" name="entries[0][months][{{ $selectedMonth }}][{{ $direction }}][{{ $slot }}][quantity]" value="{{ old("entries.0.months.$selectedMonth.$direction.$slot.quantity") }}" aria-label="{{ strtoupper($direction) }} qty {{ $selectedMonthName }} slot {{ $slot }}" class="w-24 rounded-lg border-slate-300 px-2 py-2 text-center">
                                        </td>
                                        <td class="min-w-52 px-2 py-2">
                                            <input type="date" name="entries[0][months][{{ $selectedMonth }}][{{ $direction }}][{{ $slot }}][date]" value="{{ old("entries.0.months.$selectedMonth.$direction.$slot.date") }}" min="{{ $selectedMonthStart }}" max="{{ $selectedMonthEnd }}" aria-label="Tanggal {{ strtoupper($direction) }} {{ $selectedMonthName }} slot {{ $slot }}" class="block min-h-10 w-full rounded-lg border-slate-300 px-2 py-2 font-sans text-xs focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </fieldset>
            <div class="flex flex-col gap-2 border-t border-gray-300 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-500">Tanggal dibatasi sesuai bulan {{ $selectedMonthName }} {{ $tahun }}.</p>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="border border-blue-700 bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Simpan Data Stock</button>
                    <button type="submit" name="save_mode" value="another" class="border border-slate-500 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Simpan & Tambah Lagi</button>
                </div>
            </div>
        </form>
    </dialog>
    @if($errors->any() || request()->boolean('tambah'))
        <script>document.addEventListener('DOMContentLoaded', function () { document.getElementById('stock-entry-dialog')?.showModal(); });</script>
    @endif
    @endif

    <section class="overflow-hidden border border-slate-300 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-300 bg-slate-50 px-4 py-3 sm:px-5">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ $kategori }} — {{ $selectedMonthName }} {{ $tahun }}</h2>
                <p class="mt-1 text-sm text-slate-600">Jumlah masuk, keluar, dan sisa stok pada bulan terpilih.</p>
            </div>
            <span class="text-sm text-slate-600">{{ $rows->count() }} alat</span>
        </div>
        <div class="overflow-x-auto">
            {{-- STRUKTUR TABEL ASLI, TAPI HANYA MERENDER 1 BULAN --}}
            <table class="w-full min-w-[1840px] border-collapse text-xs text-slate-800">
                <thead class="text-center font-semibold uppercase text-white">
                    <tr>
                        <th rowspan="3" class="px-3 py-3">No</th>
                        <th rowspan="3" class="min-w-48 px-3 py-3 text-left">Nama Alat Ukur</th>
                        <th rowspan="3" class="min-w-28 px-3 py-3">Type</th>
                        <th rowspan="3" class="min-w-24 px-3 py-3">Number Order</th>
                        <th rowspan="3" class="min-w-28 px-3 py-3">Spesifikasi</th>
                        <th rowspan="2" class="min-w-28 px-3 py-3">Stock Awal Tahun</th>
                        
                        {{-- HANYA RENDER 1 BULAN SESUAI DROPDOWN --}}
                        <th colspan="16" class="px-3 py-3">{{ strtoupper($selectedMonthName) }} — PERGERAKAN</th>
                        
                        <th rowspan="2" class="min-w-28 px-3 py-3">Stock Akhir Tahun</th>
                    </tr>
                    <tr class="bg-blue-50 text-slate-700">
                        <th colspan="4" class="min-w-[240px] px-3 py-3">MASUK · QTY</th>
                        <th colspan="4" class="min-w-[240px] px-3 py-3">KELUAR · QTY</th>
                        <th colspan="4" class="min-w-[560px] px-3 py-3">TANGGAL MASUK</th>
                        <th colspan="4" class="min-w-[560px] px-3 py-3">TANGGAL KELUAR</th>
                    </tr>
                    <tr>
                            <th class="min-w-28 px-3 py-2 font-bold">QTY</th>
                        @foreach(range(1, 16) as $slot)
                            @if($slot <= 8)
                                <th class="min-w-[60px] px-3 py-2">{{ (($slot - 1) % 4) + 1 }}</th>
                            @else
                                <th class="min-w-[140px] px-3 py-2">{{ (($slot - 1) % 4) + 1 }}</th>
                            @endif
                        @endforeach
                        <th class="min-w-28 px-3 py-2 font-bold">QTY</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        @php
                            $stock = $row['stock'];
                            $monthlyData = $row['monthly'][$selectedMonth] ?? null;
                            $monthSlots = $row['movement_slots'];
                        @endphp
                        <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-2 font-semibold">{{ $stock->tool_name }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $stock->type }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $stock->order_number ?: '—' }}</td>
                            <td class="border border-gray-300 px-2 py-2">{{ $stock->specification ?: '—' }}</td>
                            
                            {{-- Stock Awal Tahun --}}
                            <td class="border border-gray-300 bg-blue-50 px-3 py-3 text-center font-bold text-blue-900">{{ $row['stock_awal_tahun'] }}</td>

                            {{-- IN slots --}}
                            @foreach(range(0, 3) as $slotIndex)
                                @php $movementSlot = $monthSlots['in'][$slotIndex] ?? null; @endphp
                                <td class="min-w-[60px] border border-gray-300 px-3 py-3 text-center">{{ $movementSlot['quantity'] ?? '' }}</td>
                            @endforeach
                            
                            {{-- OUT slots --}}
                            @foreach(range(0, 3) as $slotIndex)
                                @php $movementSlot = $monthSlots['out'][$slotIndex] ?? null; @endphp
                                <td class="min-w-[60px] border border-gray-300 px-3 py-3 text-center">{{ $movementSlot['quantity'] ?? '' }}</td>
                            @endforeach

                            {{-- Tanggal IN --}}
                            @foreach(range(0, 3) as $slotIndex)
                                @php $movementSlot = $monthSlots['in'][$slotIndex] ?? null; @endphp
                                <td class="min-w-[140px] border border-gray-300 px-3 py-3 text-center tabular-nums">{{ $movementSlot['date'] ?? '' }}</td>
                            @endforeach

                            {{-- Tanggal OUT --}}
                            @foreach(range(0, 3) as $slotIndex)
                                @php $movementSlot = $monthSlots['out'][$slotIndex] ?? null; @endphp
                                <td class="min-w-[140px] border border-gray-300 px-3 py-3 text-center tabular-nums">{{ $movementSlot['date'] ?? '' }}</td>
                            @endforeach

                            {{-- Stock Akhir Tahun --}}
                            <td class="border border-gray-300 bg-blue-50 px-3 py-3 text-center font-bold text-blue-900">{{ $row['stock_akhir_tahun'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="23" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada data stock {{ $kategori }} untuk {{ $selectedMonthName }} {{ $tahun }}. Gunakan tombol “Tambah Data Stock” untuk mengisi data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
<style>
    .stock-page,
    .stock-page :where(input, select, button, table, th, td) {
        font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
    }
    .stock-page table thead tr:first-child th {
        background: #0070f2;
        border: 1px solid #0054b5;
        color: #fff;
    }
    .stock-page table thead tr:not(:first-child) th {
        background: #eaf3fc;
        border: 1px solid #c9d8e8;
        color: #1d2d3e;
    }
    .stock-page table tbody td { border: 1px solid #d9d9d9; }
    .stock-page table tbody tr:hover td { background-color: #f0f7fd; }
</style>
@endsection
