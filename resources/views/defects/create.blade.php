@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-4xl rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
    <h2 class="mb-1 text-xl font-bold text-slate-800">Tambah Data NG</h2>
    <p class="mb-5 text-sm text-slate-500">Isi jumlah NG per jenis kerusakan untuk satu divisi pada satu bulan.</p>

    @if ($errors->any())
        <div class="mb-5 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('defects.store') }}" method="POST">
        @csrf

        <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="tahun" class="mb-1 block text-sm font-semibold">Tahun *</label>
                <input id="tahun" type="number" name="tahun" min="2000" max="2100" required
                       value="{{ old('tahun', date('Y')) }}"
                       class="w-full rounded border border-slate-300 p-2">
            </div>
            <div>
                <label for="bulan" class="mb-1 block text-sm font-semibold">Bulan *</label>
                <select id="bulan" name="bulan" required class="w-full rounded border border-slate-300 p-2">
                    @foreach(\App\Models\ToolDefect::MONTHS as $m)
                        <option value="{{ $m }}" {{ old('bulan', strtoupper(date('M'))) === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="division" class="mb-1 block text-sm font-semibold">Division *</label>
                <select id="division" name="division" required class="w-full rounded border border-slate-300 p-2">
                    <option value="">-- Pilih Division --</option>
                    @foreach(\App\Models\ToolDefect::DIVISIONS as $d)
                        <option value="{{ $d }}" {{ old('division') === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <p class="mb-2 text-sm font-semibold text-slate-700">Jumlah NG per jenis kerusakan</p>
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach(\App\Models\ToolDefect::COUNT_FIELDS as $field)
                <div>
                    <label for="{{ $field }}" class="mb-1 block text-xs font-semibold uppercase text-slate-600">
                        {{ strtoupper(str_replace('_', ' ', $field)) }}
                    </label>
                    <input id="{{ $field }}" type="number" name="{{ $field }}" min="0"
                           value="{{ old($field, 0) }}"
                           class="w-full rounded border border-slate-300 p-2">
                </div>
            @endforeach
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row">
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Simpan</button>
            <a href="{{ route('defects.index') }}" class="rounded bg-gray-200 px-4 py-2 text-center font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
        </div>
    </form>
</div>
@endsection