@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm mb-6">
    <h2 class="text-xl font-bold mb-4">Edit Alat Ukur: {{ $tool->tool_name }}</h2>
    
    <form action="{{ route('tools.update', $tool->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- Method Spoofing untuk Update -->
        
        <!-- Nama Alat & Kategori -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama Alat Ukur</label>
                <input type="text" name="tool_name" value="{{ $tool->tool_name }}" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Kategori</label>
                <select name="category" class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="Small Tool" {{ $tool->category == 'Small Tool' ? 'selected' : '' }}>Small Tool</option>
                    <option value="Machine" {{ $tool->category == 'Machine' ? 'selected' : '' }}>Machine</option>
                </select>
            </div>
        </div>

        <!-- Input Angka -->
        <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Jumlah Penggunaan / Kalibrasi</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
            @php
                $fields = [
                    'qa_1' => 'QA 1', 'qa_1_2' => 'QA 1.2', 'qa_inc' => 'QA INC', 'qa_insp' => 'QA INSP',
                    'ppic_1' => 'PPIC 1', 'ppic_2' => 'PPIC 2', 'line_scc' => 'LINE SCC',
                    'p1' => 'P1', 'p2' => 'P2', 'p3' => 'P3', 'p5' => 'P5', 
                    'p6' => 'P6', 'p7' => 'P7', 'p8' => 'P8', 'eng' => 'ENG'
                ];
            @endphp

            @foreach($fields as $name => $label)
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-600">{{ $label }}</label>
                <input type="number" name="{{ $name }}" value="{{ $tool->$name }}" min="0" class="w-full border border-gray-300 p-2 rounded text-center focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>

        <!-- Tombol Aksi -->
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 transition">Update Data</button>
            <a href="{{ route('tools.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection