@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h1 class="text-lg font-bold text-gray-800 mb-4">Import Data List Up NG</h1>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 rounded mb-4 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('list-up-ng.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">File Excel/CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                @error('file') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="tahun" class="block text-sm font-semibold text-gray-700 mb-1">YEAR yang diganti</label>
                <input id="tahun" type="number" name="tahun" min="2000" max="2100" value="{{ old('tahun', $tahun) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                @error('tahun') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-3 mb-4">
                Import ini mengganti seluruh data List Up NG untuk tahun yang dipilih. Pastikan workbook berisi kolom Tool Name, NG Date, Dept, dan Category NG.
            </p>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-blue-700">
                    Import Data
                </button>
                <a href="{{ route('list-up-ng.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection