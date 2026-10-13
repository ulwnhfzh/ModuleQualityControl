@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h1 class="text-lg font-bold text-gray-800 mb-4">Import Data Kalibrasi Internal</h1>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 rounded mb-4 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('internal.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">File Excel/CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-xs text-blue-700">
                💡 Download template dulu supaya format sesuai:
                <a href="{{ route('internal.template') }}"
                   class="underline font-semibold">Download Template</a>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-blue-700">
                    Import
                </button>
                <a href="{{ route('internal.index') }}"
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection