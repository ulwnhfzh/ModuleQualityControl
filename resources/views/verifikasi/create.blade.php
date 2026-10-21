@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm mb-6 max-w-4xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Tambah Jadwal Verifikasi {{ ucfirst($mode) }}</h2>
    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p class="font-bold mb-1">Ada kesalahan:</p>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('verifikasi.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tipe" value="{{ $mode }}">

        <!-- Pilih Alat & Tahun -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama Alat Ukur</label>
                <select name="tool_name" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Alat --</option>
                    @foreach($tools as $tool)
                        <option value="{{ $tool->tool_name }}" @selected(old('tool_name') === $tool->tool_name)>{{ $tool->tool_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tahun</label>
                <select name="tahun" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
                    @for($y = max((int) date('Y') + 1, (int) $tahun); $y >= min((int) date('Y') - 5, (int) $tahun); $y--)
                        <option value="{{ $y }}" @selected((int) old('tahun', $tahun) === $y)>YEAR {{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>

        {{-- FORM BULANAN --}}
        @if($mode == 'bulanan')
        <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Jumlah Verifikasi per Bulan</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
            @php
                $bulan = [
                    'jan' => 'Januari', 'feb' => 'Februari', 'mar' => 'Maret', 'apr' => 'April',
                    'may' => 'Mei', 'jun' => 'Juni', 'jul' => 'Juli', 'aug' => 'Agustus',
                    'sep' => 'September', 'oct' => 'Oktober', 'nov' => 'November', 'dec' => 'Desember'
                ];
            @endphp
            @foreach($bulan as $name => $label)
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-600">{{ $label }}</label>
                <input type="number" name="{{ $name }}" value="{{ old($name, 0) }}" min="0" max="2147483647" step="1" inputmode="numeric" class="w-full border border-gray-300 p-2 rounded text-center focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>
        @endif

        {{-- FORM HARIAN --}}
        @if($mode == 'harian')
        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">Tanggal Verifikasi</label>
            <x-date-entry name="tanggal" :value="old('tanggal', date('Y-m-d'))" :required="true" input-class="w-full md:w-64 border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none" />
        </div>

        <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Jumlah Verifikasi per Departemen</h3>
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
                <input type="number" name="{{ $name }}" value="{{ old($name, 0) }}" min="0" max="2147483647" step="1" inputmode="numeric" class="w-full border border-gray-300 p-2 rounded text-center focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="bg-black text-white px-6 py-2 rounded font-semibold hover:bg-gray-800 transition">Simpan</button>
            <button type="submit" name="save_mode" value="another" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan & Tambah Lagi</button>
            <a href="{{ route('verifikasi.index', ['mode' => $mode]) }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
