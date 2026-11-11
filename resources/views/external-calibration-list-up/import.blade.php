@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl mt-10 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-bold text-slate-800 mb-1">Import List Up External Calibration</h2>
    <p class="text-sm text-slate-500 mb-6">Pilih file Excel (.xlsx / .xls) untuk diunggah.</p>

    @if($errors->any())
        <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('external-list-up.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tahun</label>
            <input type="text" value="{{ $tahun }}" class="w-full bg-slate-100 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-600 cursor-not-allowed" readonly>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih File Excel</label>
            <input type="file" name="file" accept=".xls,.xlsx" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('external-list-up.index', ['tahun' => $tahun]) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Batal</a>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Upload & Import</button>
        </div>
    </form>
</div>
@endsection