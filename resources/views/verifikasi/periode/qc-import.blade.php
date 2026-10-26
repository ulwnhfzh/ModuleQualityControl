@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-4">
        <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'verifikasi_qc2']) }}" class="text-sm font-medium text-emerald-700">← Kembali ke Tabel QC2</a>
        <h1 class="mt-3 text-xl font-bold text-slate-800">Import Data Verifikasi QC2</h1>
        <p class="text-sm text-slate-500 mt-1">Pastikan file Excel memiliki header: TOOL NAME, TYPE, SPECIFICATION, NUMBER SERI, NUMBER REGISTRATION, SHOP, VERIFICATION DATE, NEXT VERIFICATION DATE, JGM.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('verifikasi.qc2.import') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div>
            <label class="text-sm font-semibold text-slate-700">File Excel (.xlsx, .xls, .csv)</label>
            <input type="file" name="file" required class="mt-1 w-full border border-slate-300 rounded-lg p-2 text-sm">
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'verifikasi_qc2']) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Proses Import</button>
        </div>
    </form>
</div>
@endsection