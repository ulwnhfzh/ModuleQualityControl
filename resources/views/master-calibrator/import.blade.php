@extends('layouts.app')

@section('content')
<div class="mx-auto mt-10 max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-1 text-lg font-bold text-slate-800">Import Master Kalibrator</h1>
    <p class="mb-6 text-sm text-slate-500">Pilih workbook Excel (.xlsx / .xls) untuk diunggah.</p>

    @if($errors->any())
        <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('master-calibrator.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        @if(request('sidebar_context') === 'external-list-up')
            <input type="hidden" name="sidebar_context" value="external-list-up">
        @endif

        <div>
            <label for="import-year" class="mb-1 block text-sm font-semibold text-slate-700">Tahun</label>
            <input id="import-year" type="text" value="{{ $tahun }}" readonly class="w-full cursor-not-allowed rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-600">
        </div>

        <div>
            <label for="import-file" class="mb-1 block text-sm font-semibold text-slate-700">Pilih File Excel</label>
            <input id="import-file" type="file" name="file" accept=".xls,.xlsx" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('master-calibrator.index', ['tahun' => $tahun] + (request('sidebar_context') === 'external-list-up' ? ['sidebar_context' => 'external-list-up'] : [])) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Batal</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Upload &amp; Import</button>
        </div>
    </form>
</div>
@endsection
