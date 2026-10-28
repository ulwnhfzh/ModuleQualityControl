@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-6 max-w-xl mx-auto mt-10">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Import Data Kerusakan (NG)</h2>
    
    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">{{ session('error') }}</div>
    @endif

    <form action="{{ route('defects.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">File Excel (.xlsx, .csv)</label>
            <input type="file" name="file" class="w-full border border-gray-300 rounded p-2 text-sm" required>
            <p class="text-xs text-gray-500 mt-1">Pastikan format Excel sesuai standar tahunan (Header Bulan di baris ke-2, Header Defect di baris ke-3).</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1">Tahun</label>
            <input type="number" name="tahun" value="{{ date('Y') }}" min="2000" max="2100" step="1" class="w-full border rounded p-2 text-sm" required>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded font-semibold hover:bg-amber-600">Proses Import</button>
            <a href="{{ route('defects.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300">Batal</a>
        </div>
    </form>
</div>
@endsection