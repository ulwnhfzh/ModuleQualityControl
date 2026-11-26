@extends('layouts.app')

@section('content')
<div class="list-up-ng-form-card w-full max-w-4xl rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
    <h2 class="mb-5 text-xl font-bold text-slate-800">Tambah Data List Up NG</h2>

    @if ($errors->any())
    <div class="mb-5 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('list-up-ng.store') }}" method="POST">
        @csrf
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
                    @foreach(($toolOptions[$selectedToolName ?? ''] ?? []) as $typeOption)
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
                <input type="text" name="size" class="w-full min-w-0 border p-2 rounded" value="{{ old('size') }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">No Reg</label>
                <input type="text" name="number_reg" class="w-full min-w-0 border p-2 rounded" value="{{ old('number_reg') }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">No Seri</label>
                <input type="text" name="number_seri" class="w-full min-w-0 border p-2 rounded" value="{{ old('number_seri') }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Part Name</label>
                <input type="text" name="part_name" class="w-full min-w-0 border p-2 rounded" value="{{ old('part_name') }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Part Number</label>
                <input type="text" name="part_number" class="w-full min-w-0 border p-2 rounded" value="{{ old('part_number') }}">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">NG Date *</label>
                <input type="date" name="ng_date" required value="{{ old('ng_date', date('Y-m-d')) }}" class="w-full min-w-0 border p-2 rounded">
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Division *</label>
                <select name="division" required class="w-full border p-2 rounded">
                    <option value="">-- Pilih Division --</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" @selected(old('division') === $department)>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-semibold mb-1">Category NG *</label>
                <div class="flex gap-2">
                    <select id="category_ng" name="category_ng" required class="w-full min-w-0 border p-2 rounded">
                        <option value="">-- Pilih Category --</option>
                        @foreach($categoryOptions as $cat)
                            <option value="{{ $cat['name'] }}"
                                    data-id="{{ $cat['id'] }}"
                                    data-default="{{ $cat['is_default'] ? 1 : 0 }}"
                                    @selected(old('category_ng') === $cat['name'])>{{ $cat['name'] }}</option>
                        @endforeach
                        <option value="__add_new_category__" @selected(old('category_ng') === '__add_new_category__')>+ Tambah Category Baru</option>
                    </select>
                    <button type="button" id="deleteCategoryBtn" hidden
                            class="shrink-0 rounded bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Hapus
                    </button>
                </div>
                <div id="newCategoryContainer" class="mt-2" @if(old('category_ng') !== '__add_new_category__') hidden @endif>
                    <input id="new_category_ng" name="new_category_ng" type="text" maxlength="255"
                           class="w-full border p-2 rounded" value="{{ old('new_category_ng') }}"
                           placeholder="Masukkan Category baru"
                           @if(old('category_ng') === '__add_new_category__') required @endif>
                    <p class="mt-1 text-xs text-gray-500">Category baru akan disimpan dan bisa dipilih lagi nanti.</p>
                </div>
            </div>
            <div class="list-up-ng-form-note">
                <label class="block text-sm font-semibold mb-1">Note</label>
                <textarea name="note" rows="3" class="w-full min-w-0 border p-2 rounded">{{ old('note') }}</textarea>
            </div>
        </div>
        <div class="flex flex-col-reverse gap-2 sm:flex-row">
            <button type="submit" class="rounded bg-blue-600 px-4 py-2 font-semibold text-white hover:bg-blue-700">Simpan</button>
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
    const addNewCategoryValue = '__add_new_category__';
    const categoriesBaseUrl = @json(url('/list-up-ng/categories'));
    const csrfToken = @json(csrf_token());

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

    // ===== Category NG =====
    const categorySelect = document.getElementById('category_ng');
    const deleteCategoryBtn = document.getElementById('deleteCategoryBtn');

    function toggleNewCategoryInput(show) {
        const container = document.getElementById('newCategoryContainer');
        const input = document.getElementById('new_category_ng');
        container.hidden = !show;
        input.required = show;
        if (!show) {
            input.value = '';
        }
    }

    function refreshCategoryDeleteButton() {
        const opt = categorySelect.options[categorySelect.selectedIndex];
        const deletable = opt && opt.dataset.id && opt.dataset.default === '0';
        deleteCategoryBtn.hidden = !deletable;
    }

    categorySelect.addEventListener('change', function () {
        toggleNewCategoryInput(this.value === addNewCategoryValue);
        refreshCategoryDeleteButton();
    });

    deleteCategoryBtn.addEventListener('click', async function () {
        const opt = categorySelect.options[categorySelect.selectedIndex];
        if (!opt || !opt.dataset.id) return;
        if (!confirm(`Hapus category "${opt.value}"?`)) return;

        const response = await fetch(`${categoriesBaseUrl}/${opt.dataset.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            alert(data.message || 'Gagal menghapus category.');
            return;
        }

        opt.remove();
        categorySelect.value = '';
        toggleNewCategoryInput(false);
        refreshCategoryDeleteButton();
    });

    refreshCategoryDeleteButton();
</script>
@endsection