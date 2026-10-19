@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-2 text-xl font-bold text-slate-800">Import List Up Internal Verification</h1>
    <p class="mb-5 text-sm text-slate-500">Impor informasi produk dan jadwal periode langsung ke List Up.</p>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('verifikasi.periode.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label for="tahun" class="mb-1 block text-sm font-semibold text-slate-700">Tahun Data</label>
            <select id="tahun" name="tahun" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @for($year = max((int) date('Y') + 2, $tahun); $year >= date('Y') - 12; $year--)
                    <option value="{{ $year }}" @selected((int) old('tahun', $tahun) === $year)>YEAR {{ $year }}</option>
                @endfor
            </select>
            <p class="mt-1 text-xs text-slate-500">Semua baris dari file akan disimpan pada tahun yang dipilih.</p>
        </div>

        <div class="mb-4">
            <label for="file" class="mb-1 block text-sm font-semibold text-slate-700">File Excel/CSV</label>
            <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div class="mb-5 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
            <p>Gunakan nama kolom berikut pada baris pertama file: <strong>tool_name, tool_type, specification, serial_number, registration_number, shop, period1_verification_date, period1_next_verification_date, period1_jgm, period2_verification_date, period2_next_verification_date, period2_jgm</strong>.</p>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-slate-800 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700">Import</button>
            <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun]) }}" class="rounded-lg bg-gray-200 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
        </div>
    </form>
</div>
@endsection
