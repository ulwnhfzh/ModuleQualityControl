@extends('layouts.app')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="mb-1 font-semibold">Input belum dapat disimpan:</p>
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <section class="feature-toolbar rounded-lg bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Persediaan QA Plant 2</p>
                <h1 class="text-lg font-bold text-gray-900 md:text-xl">Data Stock Alat Ukur QA Plant 2</h1>
                <p class="mt-1 text-xs text-gray-500">Tabel menampilkan data stock tersimpan. Gunakan tombol tambah untuk menginput data baru.</p>
            </div>
            <form method="GET" action="{{ route('stock.index') }}" class="flex flex-wrap items-end gap-2">
                <label class="text-xs font-semibold text-gray-600">Tahun
                    <select name="tahun" class="mt-1 block rounded border-gray-300 text-sm">
                        @for($year = date('Y'); $year >= date('Y') - 12; $year--)
                            <option value="{{ $year }}" @selected($tahun === $year)>{{ $year }}</option>
                        @endfor
                    </select>
                </label>
                <label class="text-xs font-semibold text-gray-600">Kategori
                    <select name="kategori" class="mt-1 block rounded border-gray-300 text-sm">
                        @foreach(['Small Tool', 'Machine', 'Jig'] as $option)
                            <option value="{{ $option }}" @selected($kategori === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Tampilkan</button>
            </form>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3">
            <a href="{{ route('stock.export', ['tahun' => $tahun, 'kategori' => $kategori]) }}" class="rounded border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Export Excel</a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('stock.import.form', ['tahun' => $tahun, 'kategori' => $kategori]) }}" class="rounded border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Import</a>
                <button type="button" onclick="document.getElementById('stock-entry-dialog').showModal()" class="rounded bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700">+ Tambah Data Stock</button>
            </div>
        </div>
        @endif
    </section>

    @if(Auth::user()->role === 'admin')
    <dialog id="stock-entry-dialog" class="m-auto max-h-[92vh] w-[min(96vw,1100px)] rounded-xl p-0 shadow-2xl backdrop:bg-slate-900/50">
        <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-4 py-4 sm:px-6">
            <div>
                <h2 class="text-base font-bold text-gray-900">Tambah Data Stock</h2>
                <p class="mt-1 text-xs text-gray-500">Nama alat diinput langsung di sini, tidak mengambil data dari Daftar Induk.</p>
            </div>
            <button type="button" aria-label="Tutup form" onclick="document.getElementById('stock-entry-dialog').close()" class="rounded-lg px-3 py-1 text-xl text-gray-500 hover:bg-gray-100">&times;</button>
        </div>
        <form method="POST" action="{{ route('stock.grid.store') }}">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
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
                    <input type="number" min="0" step="1" name="entries[0][opening_stock]" value="{{ old('entries.0.opening_stock', 0) }}" class="mt-1 block w-full rounded border-gray-300 text-sm">
                </label>
            </div>
            <div class="px-4 pt-3">
                <h3 class="text-xs font-bold uppercase tracking-wide text-gray-700">Pergerakan Stock {{ $tahun }}</h3>
                <p class="mt-1 text-xs text-gray-500">Isi QTY dan tanggal masuk/keluar. Tanggal harus sesuai dengan bulan kolomnya.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($months as $monthNumber => $monthName)
                    <fieldset class="min-w-0 rounded-lg border border-gray-200">
                        <legend class="ml-3 px-2 text-xs font-bold uppercase tracking-wide text-slate-700">{{ $monthName }}</legend>
                        <div class="overflow-x-auto px-2 pb-2">
                            <table class="w-full min-w-[390px] border-collapse text-[11px]">
                                <thead>
                                    <tr class="bg-slate-700 text-center font-semibold text-white">
                                        <th class="rounded-l px-2 py-2">Slot</th>
                                        <th colspan="2" class="px-2 py-2">Masuk</th>
                                        <th colspan="2" class="rounded-r px-2 py-2">Keluar</th>
                                    </tr>
                                    <tr class="bg-slate-100 text-center text-gray-600">
                                        <th class="px-1 py-1">#</th>
                                        <th class="px-1 py-1">Qty</th>
                                        <th class="px-1 py-1">Tanggal</th>
                                        <th class="px-1 py-1">Qty</th>
                                        <th class="px-1 py-1">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(range(1, 4) as $slot)
                                        <tr>
                                            <td class="border-b border-gray-100 px-1 py-1 text-center text-gray-500">{{ $slot }}</td>
                                            @foreach(['in', 'out'] as $direction)
                                                <td class="border-b border-gray-100 px-1 py-1">
                                                    <input type="number" min="0" step="1" inputmode="numeric" name="entries[0][months][{{ $monthNumber }}][{{ $direction }}][{{ $slot }}][quantity]" value="{{ old("entries.0.months.$monthNumber.$direction.$slot.quantity") }}" aria-label="{{ strtoupper($direction) }} QTY {{ $monthName }} slot {{ $slot }}" class="w-14 rounded border-gray-300 px-1 py-1.5 text-center">
                                                </td>
                                                <td class="border-b border-gray-100 px-1 py-1">
                                                    <x-date-entry name="entries[0][months][{{ $monthNumber }}][{{ $direction }}][{{ $slot }}][date]" :value="old("entries.0.months.$monthNumber.$direction.$slot.date")" aria-label="Tanggal {{ strtoupper($direction) }} {{ $monthName }} slot {{ $slot }}" input-class="w-32 rounded border-gray-300 px-1 py-1.5 text-xs" />
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                @endforeach
            </div>
            <div class="flex flex-col gap-2 border-t border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-gray-500">Kolom bulan dapat digeser ke samping pada layar kecil.</p>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="rounded bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Data Stock</button>
                    <button type="submit" name="save_mode" value="another" class="rounded bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Simpan & Tambah Lagi</button>
                </div>
            </div>
        </form>
    </dialog>
    @if($errors->any() || request()->boolean('tambah'))
        <script>document.addEventListener('DOMContentLoaded', function () { document.getElementById('stock-entry-dialog')?.showModal(); });</script>
    @endif
    @endif

    <section class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
            <div>
                <h2 class="text-sm font-bold text-gray-800">Data Stock Tersimpan — {{ $kategori }} — {{ $tahun }}</h2>
            <p class="mt-1 text-xs text-gray-500">Format tabel tahunan mengikuti lembar stock: QTY dan tanggal masuk/keluar per bulan.</p>
            </div>
            <span class="text-xs text-gray-500">{{ $rows->count() }} alat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[12600px] border-collapse text-[11px] text-gray-800">
                <thead class="text-center font-semibold uppercase text-white">
                    <tr class="bg-slate-700">
                        <th rowspan="3" class="border border-slate-500 px-2 py-2">No</th>
                        <th rowspan="3" class="min-w-48 border border-slate-500 px-2 py-2 text-left">Nama Alat Ukur</th>
                        <th rowspan="3" class="min-w-28 border border-slate-500 px-2 py-2">Type</th>
                        <th rowspan="3" class="min-w-24 border border-slate-500 px-2 py-2">Number Order</th>
                        <th rowspan="3" class="min-w-28 border border-slate-500 px-2 py-2">Spesifikasi</th>
                        <th rowspan="3" class="min-w-20 border border-slate-500 px-2 py-2">Total</th>
                        @foreach($months as $monthNumber => $monthName)
                            <th colspan="16" class="border border-slate-500 px-2 py-2">{{ strtoupper($monthName) }}</th>
                        @endforeach
                        <th rowspan="3" class="min-w-20 border border-slate-500 bg-emerald-700 px-2 py-2">Akhir Tahun</th>
                    </tr>
                    <tr class="bg-slate-600">
                        @foreach($months as $monthNumber => $monthName)
                            <th colspan="4" class="border border-slate-500 px-1 py-2">IN · QTY</th>
                            <th colspan="4" class="border border-slate-500 px-1 py-2">OUT · QTY</th>
                            <th colspan="4" class="border border-slate-500 px-1 py-2">Tanggal Kedatangan</th>
                            <th colspan="4" class="border border-slate-500 px-1 py-2">Tanggal Dikeluarkan</th>
                        @endforeach
                    </tr>
                    <tr class="bg-slate-500">
                        @foreach($months as $monthNumber => $monthName)
                            @foreach(range(1, 16) as $slot)
                                <th class="border border-slate-400 px-1 py-1">{{ (($slot - 1) % 4) + 1 }}</th>
                            @endforeach
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        @php($stock = $row['stock'])
                        <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }}">
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-2 font-semibold">{{ $stock->tool_name }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $stock->type }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $stock->order_number ?: '—' }}</td>
                            <td class="border border-gray-300 px-2 py-2">{{ $stock->specification ?: '—' }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-center">{{ $row['monthly'][1]['opening'] }}</td>
                            @foreach($months as $monthNumber => $monthName)
                                @php($monthSlots = $row['movement_slots']->get($monthNumber, []))
                                @foreach(['in', 'out'] as $direction)
                                    @foreach(range(0, 3) as $slotIndex)
                                        @php($movementSlot = $monthSlots[$direction][$slotIndex] ?? null)
                                        <td class="border border-gray-300 px-1 py-1 text-center">{{ $movementSlot['quantity'] ?? '' }}</td>
                                    @endforeach
                                @endforeach
                                @foreach(['in', 'out'] as $direction)
                                    @php($directionSlots = $monthSlots[$direction] ?? [])
                                    @foreach(range(0, 3) as $slotIndex)
                                        <td class="border border-gray-300 px-1 py-1 text-center">{{ $directionSlots[$slotIndex]['date'] ?? '' }}</td>
                                    @endforeach
                                @endforeach
                            @endforeach
                            <td class="border border-gray-300 bg-emerald-50 px-2 py-2 text-center font-bold text-emerald-900">{{ $row['closing_stock'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="199" class="px-4 py-12 text-center text-sm text-gray-500">Belum ada data stock {{ $kategori }} untuk tahun {{ $tahun }}. Gunakan tombol “Tambah Data Stock” untuk mengisi data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
