@extends('layouts.app')

@section('content')

<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-6 p-3 sm:p-4 md:p-6 max-w-4xl mx-auto">
    <h2 class="font-bold text-gray-800 mb-4 text-base md:text-xl">Tambah Alat Ukur Baru</h2>
    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 rounded mb-4 px-3 py-2 text-xs sm:px-4 sm:py-3 sm:text-sm">
        <p class="font-bold mb-1">Ada kesalahan:</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('tools.store') }}" method="POST">
        @csrf

        <!-- Nama Alat & Kategori -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-6">
            <div>
                <label class="block text-xs md:text-sm font-semibold mb-1">Nama Alat Ukur *</label>
                <input type="text" name="tool_name" required
                       placeholder="Contoh: BALL ANVIL"
                       oninput="this.value = this.value.toUpperCase()"
                       value="{{ old('tool_name') }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm uppercase focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div x-data="{ newCategory: '{{ old('category') === '__new' ? 'true' : 'false' }}' === 'true' }">
                <label class="block text-xs md:text-sm font-semibold mb-1">Type / Kategori *</label>
                <select name="category" required @change="newCategory = $event.target.value === '__new'"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">Pilih type</option>
                    @foreach($categories as $category)
                <option value="{{ $category }}" @selected(old('category', request('kategori', 'Small Tool')) === $category)>{{ $category }}</option>
                    @endforeach
                    <option value="__new" @selected(old('category') === '__new')>＋ Tambah type baru…</option>
                </select>
                <input x-show="newCategory" x-cloak type="text" name="category_new" value="{{ old('category_new') }}"
                       :required="newCategory" maxlength="100" placeholder="Ketik type baru, contoh: Master Tool"
                       class="mt-2 w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2 text-sm md:text-base">Informasi Stok</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-6">
            <div>
                <label class="block text-xs md:text-sm font-semibold mb-1">Nomor Order</label>
                <input type="text" name="order_number" value="{{ old('order_number') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs md:text-sm font-semibold mb-1">Spesifikasi</label>
                <input type="text" name="specification" value="{{ old('specification') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-xs md:text-sm font-semibold mb-1">Stok Awal *</label>
                <input type="number" name="opening_stock" min="0" required value="{{ old('opening_stock', 0) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        <!-- Input Angka -->
        <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2 text-sm md:text-base">Jumlah Penggunaan / Kalibrasi</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-3 mb-6">
            @php
                $fields = [
                    'qa_1' => 'QA 1', 'qa_1_2' => 'QA 1.2', 'qa_2' => 'QA 2', 'qa_inc' => 'QA INC',
                    'qa_insp' => 'QA INSP', 'ppic_1' => 'PPIC 1', 'ppic_2' => 'PPIC 2', 'line_scc' => 'LINE SCC',
                    'p1' => 'P1', 'p2' => 'P2', 'p3' => 'P3', 'p5' => 'P5',
                    'p6' => 'P6', 'p7' => 'P7', 'p8' => 'P8', 'eng' => 'ENG'
                ];
            @endphp

            @foreach($fields as $name => $label)
            <div>
                <label class="block text-[10px] md:text-xs font-semibold mb-1 text-gray-600">{{ $label }}</label>
                <input type="number" name="{{ $name }}" value="{{ old($name, 0) }}" min="0"
                       class="w-full border border-gray-300 rounded px-2 py-2 text-sm text-center focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>

        <!-- Tombol Aksi -->
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition px-4 py-2 text-sm">
                Simpan Data
            </button>
            <button type="submit" name="save_mode" value="another" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Simpan & Tambah Lagi
            </button>
            <a href="{{ route('tools.index') }}" class="bg-gray-200 text-gray-700 rounded font-semibold hover:bg-gray-300 transition px-4 py-2 text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection
