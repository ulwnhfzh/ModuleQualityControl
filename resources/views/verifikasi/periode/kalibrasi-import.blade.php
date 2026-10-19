@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-2 text-xl font-bold text-slate-800">Import List Up Kalibrasi Internal</h1>
    <p class="mb-5 text-sm text-slate-500">File Excel/CSV dapat memakai kolom berikut: tool_name, tool_type, specification, serial_number, registration_number, shop, calibration_date, next_calibration_date, jgm.</p>
    @if($errors->any())<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif
    <form action="{{ route('verifikasi.periode.kalibrasi.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <label class="block text-sm font-semibold text-slate-700">Tahun Data
            <select name="tahun" required class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                @for($year = max((int) date('Y') + 2, $tahun); $year >= date('Y') - 12; $year--)
                    <option value="{{ $year }}" @selected((int) old('tahun', $tahun) === $year)>YEAR {{ $year }}</option>
                @endfor
            </select>
        </label>
        <label class="block text-sm font-semibold text-slate-700">File Excel/CSV<input type="file" name="file" accept=".xlsx,.xls,.csv" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></label>
        <div class="flex justify-end gap-2">
            <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi']) }}" class="rounded-lg bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700">Batal</a>
            <button class="rounded-lg bg-slate-800 px-5 py-2 text-sm font-semibold text-white">Import</button>
        </div>
    </form>
</div>
@endsection
