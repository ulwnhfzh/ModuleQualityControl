@extends('layouts.app')

@section('content')
<div class="mx-auto mt-10 max-w-xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="mb-4 text-lg font-bold text-slate-800">Import Data NG</h2>
    
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ session('error') }}</div>
    @endif

    <form action="{{ route('defects.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-4">
            <label class="mb-1 block text-sm font-semibold text-slate-700">File Excel (.xlsx, .xls, .csv)</label>
            <input type="file" name="file" accept=".xlsx,.xls,.csv" class="w-full rounded border border-slate-300 p-2 text-sm" required>
            <p class="mt-1 text-xs text-slate-500">Workbook Excel dapat berisi beberapa sheet: sheet tahunan dengan header bulan dan jenis kerusakan, atau sheet per bulan. CSV hanya mendukung satu sheet.</p>
        </div>

        <div class="mb-6">
            <label class="mb-1 block text-sm font-semibold text-slate-700">Tahun</label>
            <input type="number" name="tahun" value="{{ date('Y') }}" min="2000" max="2100" step="1" class="w-full rounded border border-slate-300 p-2 text-sm" required>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded bg-blue-600 px-6 py-2 font-semibold text-white hover:bg-blue-700">Proses Import</button>
            <a href="{{ route('defects.index') }}" class="rounded bg-slate-100 px-6 py-2 font-semibold text-slate-700 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection