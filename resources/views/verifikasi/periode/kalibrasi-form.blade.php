@extends('layouts.app')

@section('content')
@php($editing = isset($schedule))
<div class="mx-auto max-w-5xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <div class="mb-6">
        <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi']) }}" class="text-sm font-medium text-emerald-700">← Kembali ke List Up Periode</a>
        <h1 class="mt-3 text-xl font-bold text-slate-800">{{ $editing ? 'Edit' : 'Tambah' }} Jadwal Kalibrasi Internal</h1>
    </div>
    @if(session('success'))<div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ $editing ? route('verifikasi.periode.kalibrasi.update', $schedule) : route('verifikasi.periode.kalibrasi.store') }}" class="space-y-6">
        @csrf @if($editing) @method('PUT') @endif
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['tool_name', 'Tool Name', true], ['tool_type', 'Type', true], ['specification', 'Specification', false],
                ['serial_number', 'Number Seri', false], ['registration_number', 'Number Registration', false], ['shop', 'Shop', false],
            ] as [$field, $label, $required])
                <label class="text-sm font-semibold text-slate-700">{{ $label }}
                    <input name="{{ $field }}" value="{{ old($field, $editing ? ($schedule->{$field} ?? '') : '') }}" {{ $required ? 'required' : '' }} maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="{{ $label }}">
                </label>
            @endforeach
        </div>

        <!-- FIELD PERIODE 1 -->
        <fieldset class="rounded-xl border border-slate-200 p-4">
            <legend class="px-2 text-sm font-bold text-slate-800">Periode 1 (Tahun {{ $tahun }})</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="text-sm font-medium text-slate-700">Calibration Date
                    <x-date-entry name="calibration_date" :value="old('calibration_date', isset($schedule) ? $schedule->calibration_date?->format('Y-m-d') : '')" input-class="mt-1 w-full rounded-lg border-slate-300 text-sm" />
                </label>
                <label class="text-sm font-medium text-slate-700">Next Calibration Date
                    <x-date-entry name="next_calibration_date" :value="old('next_calibration_date', isset($schedule) ? $schedule->next_calibration_date?->format('Y-m-d') : '')" input-class="mt-1 w-full rounded-lg border-slate-300 text-sm" />
                </label>
                <label class="text-sm font-medium text-slate-700">JGM
                    <input name="jgm" value="{{ old('jgm', $schedule->jgm ?? '') }}" maxlength="30" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Contoh: OK">
                </label>
            </div>
        </fieldset>

        <!-- FIELD PERIODE 2 -->
        <fieldset class="rounded-xl border border-slate-200 p-4">
            <legend class="px-2 text-sm font-bold text-slate-800">Periode 2 (Tahun {{ $tahun }})</legend>
            <div class="grid gap-4 sm:grid-cols-3">
                <label class="text-sm font-medium text-slate-700">Calibration Date
                    <x-date-entry name="period2_calibration_date" :value="old('period2_calibration_date', isset($schedule) ? $schedule->period2_calibration_date?->format('Y-m-d') : '')" input-class="mt-1 w-full rounded-lg border-slate-300 text-sm" />
                </label>
                <label class="text-sm font-medium text-slate-700">Next Calibration Date
                    <x-date-entry name="period2_next_calibration_date" :value="old('period2_next_calibration_date', isset($schedule) ? $schedule->period2_next_calibration_date?->format('Y-m-d') : '')" input-class="mt-1 w-full rounded-lg border-slate-300 text-sm" />
                </label>
                <label class="text-sm font-medium text-slate-700">JGM
                    <input name="period2_jgm" value="{{ old('period2_jgm', $schedule->period2_jgm ?? '') }}" maxlength="30" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Contoh: OK">
                </label>
            </div>
        </fieldset>

        <div class="flex justify-end gap-2">
            <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi']) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
            @unless($editing)
                <button type="submit" name="save_mode" value="another" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Simpan & Tambah Lagi</button>
            @endunless
        </div>
    </form>
</div>
@endsection