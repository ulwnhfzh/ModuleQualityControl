@extends('layouts.app')
@section('content')
@php($editing = $item->exists)
<div class="mx-auto max-w-5xl rounded-lg border border-slate-200 bg-white shadow-sm">
    <header class="border-b border-slate-200 px-5 py-4"><h1 class="text-lg font-semibold text-slate-800">{{ $editing ? 'Edit' : 'Tambah' }} List Up External Calibration</h1><p class="mt-1 text-sm text-slate-500">Isi informasi sesuai format List Up External Calibration.</p></header>
    <form method="POST" action="{{ $editing ? route('external-list-up.update', $item) : route('external-list-up.store') }}" class="space-y-5 p-5">
        @csrf @if($editing) @method('PUT') @endif
        @if($errors->any())<div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="text-sm font-medium text-slate-700">Tahun *<input type="number" name="tahun" min="2000" max="2100" required value="{{ old('tahun', $item->tahun ?? $year) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            @foreach(['tool_name'=>'Tool Name','specification'=>'Specification','serial_number'=>'Number Seri','registration_number'=>'Number Registration','shop'=>'Shop'] as $field=>$label)
                <label class="text-sm font-medium text-slate-700">{{ $label }}@if($field==='tool_name') * @endif<input type="text" name="{{ $field }}" @required($field==='tool_name') value="{{ old($field, $item->$field) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            @endforeach
            <label class="text-sm font-medium text-slate-700">Calibration Date<input type="date" name="calibration_date" value="{{ old('calibration_date', $item->calibration_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">Next Calibration Date<input type="date" name="next_calibration_date" value="{{ old('next_calibration_date', $item->next_calibration_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">JGM<input type="text" name="jgm" value="{{ old('jgm', $item->jgm) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">Number Kalibrasi<input type="number" min="0" name="calibration_count" value="{{ old('calibration_count', $item->calibration_count) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
        </div>
        <label class="block text-sm font-medium text-slate-700">Note<textarea name="note" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">{{ old('note', $item->note) }}</textarea></label>
        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4"><a href="{{ route('external-list-up.index', ['tahun'=>$year]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm">Batal</a><button class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Simpan</button></div>
    </form>
</div>
@endsection
