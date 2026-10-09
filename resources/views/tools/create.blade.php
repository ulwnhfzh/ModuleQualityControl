@extends('layouts.app')

@section('content')

<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-6 p-3 sm:p-4 md:p-6 max-w-4xl mx-auto">
    <h2 class="font-bold text-gray-800 mb-4 text-base md:text-xl">Tambah Alat Ukur Baru</h2>

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
            <div>
                <label class="block text-xs md:text-sm font-semibold mb-1">Kategori *</label>
                <select name="category" required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="Small Tool" {{ old('category') == 'Small Tool' ? 'selected' : '' }}>Small Tool</option>
                    <option value="Machine" {{ old('category') == 'Machine' ? 'selected' : '' }}>Machine</option>
                </select>
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
        <div class="flex gap-2">
            <button type="submit" class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition px-4 py-2 text-sm">
                Simpan Data
            </button>
            <a href="{{ route('tools.index') }}" class="bg-gray-200 text-gray-700 rounded font-semibold hover:bg-gray-300 transition px-4 py-2 text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

@endsection