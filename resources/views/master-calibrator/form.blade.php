@extends('layouts.app')
@section('content')
@php($editing = $item->exists)
<div class="mx-auto max-w-6xl rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-5 py-4"><h1 class="text-lg font-semibold text-slate-800">{{ $editing ? 'Edit' : 'Tambah' }} Master Kalibrator</h1><p class="mt-1 text-sm text-slate-500">Susunan identitas alat dan hasil bulanan mengikuti lembar Excel.</p></div>
    <form method="POST" action="{{ $editing ? route('master-calibrator.update', $item) : route('master-calibrator.store') }}" class="space-y-6 p-5">
        @csrf @if($editing) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-sm font-medium text-slate-700">Tahun<input type="number" name="tahun" min="2000" max="2100" required value="{{ old('tahun', $item->tahun ?? $tahun) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            @foreach(['tool_name'=>'Tool Name','type'=>'Type','size'=>'Size','number_seri'=>'Number Seri','number_reg'=>'Number Reg','part_name'=>'Part Name','part_number'=>'Part Number'] as $field=>$label)
                <label class="text-sm font-medium text-slate-700">{{ $label }}@if($field==='tool_name') * @endif<input type="text" name="{{ $field }}" @required($field==='tool_name') value="{{ old($field, $item->$field) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            @endforeach
        </div>
        <div><h2 class="mb-2 text-sm font-semibold text-slate-700">Result per bulan</h2><div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach(['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','may'=>'Mei','jun'=>'Jun','jul'=>'Jul','aug'=>'Ags','sep'=>'Sep','oct'=>'Okt','nov'=>'Nov','dec'=>'Des'] as $field=>$label)
                <label class="text-xs font-semibold text-slate-600">{{ $label }}<input type="text" name="{{ $field }}" value="{{ old($field, $item->$field) }}" placeholder="OK / NG" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm font-normal"></label>
            @endforeach
        </div><p class="mt-2 text-xs text-slate-500">Hasil dapat berupa teks seperti OK atau NG, mengikuti workbook.</p></div>
        <label class="block text-sm font-medium text-slate-700">Note<textarea name="note" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">{{ old('note', $item->note) }}</textarea></label>
        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4"><a href="{{ route('master-calibrator.index', ['tahun'=>$tahun]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm">Batal</a><button class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Simpan</button></div>
    </form>
</div>
@endsection
