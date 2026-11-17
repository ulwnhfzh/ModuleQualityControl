@extends('layouts.app')
@section('content')
@php($editing = $item->exists)
<div class="mx-auto max-w-5xl rounded-lg border border-slate-200 bg-white shadow-sm">
    <header class="border-b border-slate-200 px-5 py-4"><h1 class="text-lg font-semibold text-slate-800">{{ $editing ? 'Edit' : 'Tambah' }} List Up External Calibration</h1><p class="mt-1 text-sm text-slate-500">Isi informasi sesuai format List Up External Calibration.</p></header>
    <form method="POST" action="{{ $editing ? route('external-list-up.update', $item) : route('external-list-up.store') }}" class="space-y-5 p-5">
        @csrf @if($editing) @method('PUT') @endif
        @if($errors->any())<div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="text-sm font-medium text-slate-700">Tahun *<input id="external-calibration-year" type="number" name="tahun" min="2000" max="2100" required value="{{ old('tahun', $year) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">Tool Name *
                <select name="tool_name" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                    <option value="">Pilih Tool Name</option>
                    @foreach($toolNameList as $toolOption)
                        <option value="{{ $toolOption }}" @selected(old('tool_name', $item->tool_name) === $toolOption)>{{ $toolOption }}</option>
                    @endforeach
                </select>
            </label>
            @foreach(['specification'=>'Specification','serial_number'=>'Number Seri','registration_number'=>'Number Registration','shop'=>'Shop'] as $field=>$label)
                <label class="text-sm font-medium text-slate-700">{{ $label }}<input type="text" name="{{ $field }}" value="{{ old($field, $item->$field) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            @endforeach
            <label class="text-sm font-medium text-slate-700">Calibration Date<input type="date" name="calibration_date" value="{{ old('calibration_date', $item->calibration_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">Next Calibration Date<input type="date" name="next_calibration_date" value="{{ old('next_calibration_date', $item->next_calibration_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">JGM<input type="text" name="jgm" value="{{ old('jgm', $item->jgm) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
            <label class="text-sm font-medium text-slate-700">Number Kalibrasi<input type="number" min="0" name="calibration_count" value="{{ old('calibration_count', $item->calibration_count) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></label>
        </div>
        <p id="external-tool-options-error" class="hidden text-sm text-red-600" role="alert"></p>
        <label class="block text-sm font-medium text-slate-700">Note<textarea name="note" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">{{ old('note', $item->note) }}</textarea></label>
        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4"><a href="{{ route('external-list-up.index', ['tahun'=>$year]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm">Batal</a><button class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Simpan</button></div>
    </form>
</div>
<script>
document.getElementById('external-calibration-year').addEventListener('change', async function () {
    const toolSelect = document.querySelector('select[name="tool_name"]');
    const errorMessage = document.getElementById('external-tool-options-error');
    const currentToolName = toolSelect.value;
    const url = new URL(@json(route('data-all-produksi.get-tools')), window.location.origin);
    url.searchParams.set('division', 'ALL');
    url.searchParams.set('year', this.value);

    try {
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Daftar Tool Name gagal dimuat.');

        const toolNames = await response.json();
        if (currentToolName && !toolNames.includes(currentToolName)) {
            toolNames.unshift(currentToolName);
        }

        toolSelect.replaceChildren(new Option('Pilih Tool Name', ''));
        toolNames.forEach(function (toolName) {
            const option = new Option(toolName, toolName);
            option.selected = toolName === currentToolName;
            toolSelect.add(option);
        });
        errorMessage.textContent = '';
        errorMessage.classList.add('hidden');
    } catch (error) {
        errorMessage.textContent = 'Daftar Tool Name dari Data All Produksi gagal dimuat. Coba ubah tahun lagi.';
        errorMessage.classList.remove('hidden');
    }
});
</script>
@endsection
