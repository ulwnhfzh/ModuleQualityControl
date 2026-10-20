@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Input Data Kerusakan Alat (NG)</h2>
    
    <form action="{{ route('defects.store') }}" method="POST">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun</label>
                <input type="number" name="tahun" value="{{ date('Y') }}" min="2000" max="2100" step="1" class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Bulan</label>
                <select name="bulan" class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-blue-500" required>
                    <option value="JAN">Januari</option>
                    <option value="FEB">Februari</option>
                    <option value="MAR">Maret</option>
                    <option value="APR">April</option>
                    <option value="MAY">Mei</option>
                    <option value="JUN">Juni</option>
                    <option value="JUL">Juli</option>
                    <option value="AUG">Agustus</option>
                    <option value="SEP">September</option>
                    <option value="OCT">Oktober</option>
                    <option value="NOV">November</option>
                    <option value="DEC">Desember</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Divisi</label>
                <select name="division" class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-blue-500" required>
                    <option value="">-- Pilih Divisi --</option>
                    @foreach(['ENGINEERING', 'L.SCC', 'PRODUKSI 1', 'PRODUKSI 2', 'PRODUKSI 3', 'PRODUKSI 5', 'PRODUKSI 6', 'PRODUKSI 7', 'PRODUKSI 8', 'PPIC 1', 'PPIC 2', 'QA INCOMING', 'QA INSPECTION', 'QA 1', 'QA 1.2', 'QA 2', 'NISSHO'] as $div)
                        <option value="{{ $div }}">{{ $div }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2">Jenis Kerusakan (Isi angka 0 jika tidak ada)</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @php
                $jenisKerusakan = [
                    'aus' => 'AUS', 'erorr' => 'ERORR', 'gompal' => 'GOMPAL', 'hilang' => 'HILANG',
                    'karat' => 'KARAT', 'macet' => 'MACET', 'min_std' => 'MIN STD', 'over_std' => 'OVER STD',
                    'patah' => 'PATAH', 'rusak' => 'RUSAK', 'seret' => 'SERET', 'lain_lain' => 'LAIN-LAIN'
                ];
            @endphp

            @foreach($jenisKerusakan as $name => $label)
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $label }}</label>
                <input type="number" name="{{ $name }}" value="0" min="0" max="2147483647" step="1" inputmode="numeric" class="w-full border border-gray-300 rounded p-2 text-sm focus:ring-blue-500">
            </div>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 transition">Simpan Data</button>
            <a href="{{ route('defects.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection