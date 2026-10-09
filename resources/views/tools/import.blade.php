@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm mb-6 max-w-2xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Import Data Alat Ukur dari Excel</h2>

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('tools.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Pilih Tahun -->
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">Tahun Data</label>
            <select name="tahun" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                @for($y = date('Y') + 1; $y >= date('Y') - 10; $y--)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                        YEAR {{ $y }} {{ $y == date('Y') ? '(Sekarang)' : '' }}
                    </option>
                @endfor
            </select>
            <p class="text-xs text-gray-500 mt-1">Data yang di-import akan masuk ke tahun ini.</p>
        </div>

        <!-- Upload File -->
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">File Excel (.xlsx, .xls, .csv)</label>
            <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
        </div>

        <!-- Contoh Format -->
        <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-6 text-sm">
            <p class="font-semibold text-blue-800 mb-2">📋 Format Kolom Excel yang Benar:</p>
            <p class="text-gray-700">Baris pertama harus berisi header kolom:</p>
            <code class="block bg-white p-2 rounded mt-2 text-xs text-blue-700 overflow-x-auto">
                tool_name | category | qa_1 | qa_1_2 | qa_inc | qa_insp | ppic_1 | ppic_2 | line_scc | p1 | p2 | p3 | p5 | p6 | p7 | p8 | eng
            </code>
            <p class="text-gray-600 mt-2 text-xs">Nama kolom harus huruf kecil dan pakai underscore (_), misal: <strong>tool_name</strong>, <strong>qa_1</strong>.</p>
        </div>

        <!-- Tombol -->
        <div class="flex gap-2">
            <button type="submit" class="bg-black text-white px-6 py-2 rounded font-semibold hover:bg-gray-800 transition">Import Sekarang</button>
            <a href="{{ route('tools.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection