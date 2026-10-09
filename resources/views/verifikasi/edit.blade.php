@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm mb-6 max-w-4xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit Jadwal Verifikasi</h2>

    <form action="{{ route('verifikasi.update', $verifikasi->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama Alat Ukur</label>
                <input type="text" name="tool_name" value="{{ $verifikasi->tool_name }}" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tahun</label>
                <input type="number" name="tahun" value="{{ $verifikasi->tahun }}" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

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
                <input type="number" name="{{ $name }}" value="{{ $verifikasi->$name }}" min="0" class="w-full border border-gray-300 p-2 rounded text-center focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 transition">Update</button>
            <a href="{{ route('verifikasi.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection