@extends('layouts.app')

@section('content')
<div class="list-up-ng-form-card w-full max-w-4xl rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
    <h2 class="mb-2 text-xl font-bold text-slate-800">Edit Data List Up NG</h2>
    <p class="mb-5 text-sm text-slate-500">Mengedit data untuk: <strong class="text-slate-700">{{ $listUpNg->tool_name }}</strong></p>

    @if ($errors->any())
    <div class="mb-5 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('list-up-ng.update', $listUpNg->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="list-up-ng-form-grid mb-5">
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Tool Name *</label>
                <select id="tool_name" name="tool_name" required class="w-full border p-2 rounded">
                    <option value="">-- Pilih atau Cari Alat --</option>
                    @foreach($toolOptions as $toolName => $types)
                        <option value="{{ $toolName }}" @selected($selectedToolName === $toolName)>{{ $toolName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Type</label>
                <select id="type" name="type" class="w-full border p-2 rounded">
                    <option value="">-- Pilih Type --</option>
                    @foreach($toolOptions[$selectedToolName] ?? [] as $typeOption)
                        <option value="{{ $typeOption }}" @selected($selectedType === $typeOption)>{{ $typeOption }}</option>
                    @endforeach
                    <option value="__add_new_type__" @selected(old('type') === '__add_new_type__')>+ Tambah Type Baru</option>
                </select>
                <div id="newTypeContainer" class="mt-2" @if(old('type') !== '__add_new_type__') hidden @endif>
                    <label for="new_type" class="block text-xs font-semibold mb-1">Type Baru</label>
                    <input id="new_type" name="new_type" type="text" maxlength="255" class="w-full border p-2 rounded" value="{{ old('new_type') }}" placeholder="Masukkan Type baru" @if(old('type') === '__add_new_type__') required @endif>
                    <p class="mt-1 text-xs text-gray-500">Type baru akan ditambahkan ke Data All Produksi untuk alat ini.</p>
                </div>
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Size</label>
                <input type="text" name="size" class="w-full min-w-0 border p-2 rounded" value="{{ old('size', $listUpNg->size) }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">No Reg</label>
                <input type="text" name="number_reg" class="w-full min-w-0 border p-2 rounded" value="{{ old('number_reg', $listUpNg->number_reg) }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">No Seri</label>
                <input type="text" name="number_seri" class="w-full min-w-0 border p-2 rounded" value="{{ old('number_seri', $listUpNg->number_seri) }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Part Name</label>
                <input type="text" name="part_name" class="w-full min-w-0 border p-2 rounded" value="{{ old('part_name', $listUpNg->part_name) }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Part Number</label>
                <input type="text" name="part_number" class="w-full min-w-0 border p-2 rounded" value="{{ old('part_number', $listUpNg->part_number) }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">NG Date *</label>
                <input type="date" name="ng_date" required value="{{ old('ng_date', $listUpNg->ng_date->format('Y-m-d')) }}" class="w-full min-w-0 border p-2 rounded">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Dept *</label>
                <select name="dept" required class="w-full border p-2 rounded">
                    @foreach($departments as $department)
                        <option value="{{ $department }}" @selected(old('dept', $listUpNg->dept) === $department)>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Category NG *</label>
                <select name="category_ng" required class="w-full border p-2 rounded">
                    @foreach(['VERIFIKASI', 'KALIBRASI', 'KALIBRASI EKSTENAL'] as $c)
                        <option value="{{ $c }}" @selected(old('category_ng', $listUpNg->category_ng) == $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="list-up-ng-form-note">
                <label class="block text-sm font-semibold mb-1">Note</label>
                <textarea name="note" rows="3" class="w-full min-w-0 border p-2 rounded">{{ old('note', $listUpNg->note) }}</textarea>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row">
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Update Data</button>
            <a href="{{ route('list-up-ng.index') }}" class="rounded bg-gray-200 px-4 py-2 text-center font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
        </div>
    </form>
</div>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const listUpNgToolTypes = @json($toolOptions);
    const selectedListUpNgType = @json($selectedType);
    const addNewTypeValue = '__add_new_type__';
    const shouldShowNewType = @json(old('type') === '__add_new_type__');

    $(function () {
        $('#tool_name').select2({
            placeholder: '-- Pilih atau Cari Alat --',
            allowClear: true,
            width: '100%',
        });

        $('#tool_name').on('change', function () {
            document.getElementById('type').value = '';
            document.getElementById('new_type').value = '';
            updateListUpNgTypes($(this).val());
        });

        $('#type').on('change', function () {
            toggleNewTypeInput($(this).val() === addNewTypeValue);
        });

        if ($('#tool_name').val()) {
            updateListUpNgTypes($('#tool_name').val(), selectedListUpNgType);
        }
        if (shouldShowNewType) {
            $('#type').val(addNewTypeValue);
            toggleNewTypeInput(true);
        }
    });

    function updateListUpNgTypes(toolName, selectedType = '') {
        const typeSelect = document.getElementById('type');
        typeSelect.replaceChildren(new Option('-- Pilih Type --', ''));

        const types = listUpNgToolTypes[toolName] || [];
        types.forEach((type) => {
            typeSelect.append(new Option(type, type));
        });
        typeSelect.append(new Option('+ Tambah Type Baru', addNewTypeValue));

        if (selectedType) {
            typeSelect.value = selectedType;
        }
        toggleNewTypeInput(false);
    }

    function toggleNewTypeInput(show) {
        const container = document.getElementById('newTypeContainer');
        const input = document.getElementById('new_type');
        container.hidden = !show;
        input.required = show;
    }
</script>
@endsection