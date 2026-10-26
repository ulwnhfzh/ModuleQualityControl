@extends('layouts.app')

@section('content')
@php($editing = isset($schedule))
<div class="mx-auto max-w-5xl">
    
    <!-- HEADER HALAMAN -->
    <div class="mb-6">
        <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi']) }}" class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke List Up Periode
        </a>
        <h1 class="mt-3 text-2xl font-bold text-slate-800">{{ $editing ? 'Edit' : 'Tambah' }} Jadwal Kalibrasi Internal</h1>
        <p class="text-sm text-slate-500 mt-1">Lengkapi informasi alat dan periode kalibrasi di bawah ini.</p>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        
        @if(session('success'))
            <div class="bg-emerald-50 border-b border-emerald-200 px-6 py-4 text-sm text-emerald-800 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-4 text-sm text-rose-800 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ $editing ? route('verifikasi.periode.kalibrasi.update', $schedule) : route('verifikasi.periode.kalibrasi.store') }}" class="p-6 sm:p-8 space-y-8" x-data="{ isNewType: false, isNewShop: false }">
            @csrf @if($editing) @method('PUT') @endif
            <input type="hidden" name="tahun" value="{{ $tahun }}">

            <!-- BAGIAN 1: INFORMASI ALAT -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    Informasi Alat
                </h3>
                
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tool Name</label>
                        <select name="tool_name" id="tool_name" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Pilih atau Cari Alat --</option>
                            @foreach($tools as $tool)
                                <option value="{{ $tool->tool_name }}" {{ old('tool_name', $editing ? $schedule->tool_name : '') == $tool->tool_name ? 'selected' : '' }}>
                                    {{ $tool->tool_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Type</label>
                        <select name="tool_type" id="tool_type" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" @change="isNewType = ($event.target.value === '__new')">
                            <option value="">-- Pilih Tool Name Dulu --</option>
                        </select>
                        <input x-show="isNewType" x-cloak type="text" name="tool_type_new" value="{{ old('tool_type_new') }}"
                               :required="isNewType" maxlength="100" placeholder="Ketik type baru"
                               class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="text-xs text-slate-500 mt-1">Pilih tipe yang ada atau ketik tipe baru.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Shop</label>
                        <select name="shop" id="shop" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" @change="isNewShop = ($event.target.value === '__new')">
                            <option value="">Pilih shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop }}" {{ old('shop', $editing ? $schedule->shop : '') == $shop ? 'selected' : '' }}>{{ $shop }}</option>
                            @endforeach
                            <option value="__new" {{ old('shop') === '__new' ? 'selected' : '' }}>＋ Tambah shop baru…</option>
                        </select>
                        <input x-show="isNewShop" x-cloak type="text" name="shop_new" value="{{ old('shop_new') }}"
                               :required="isNewShop" maxlength="100" placeholder="Ketik shop baru"
                               class="mt-2 w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Specification</label>
                        <input name="specification" value="{{ old('specification', $editing ? $schedule->specification : '') }}" maxlength="255" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Spesifikasi alat">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Number Seri</label>
                        <input name="serial_number" value="{{ old('serial_number', $editing ? $schedule->serial_number : '') }}" maxlength="100" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Nomor seri">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Number Registration</label>
                        <input name="registration_number" value="{{ old('registration_number', $editing ? $schedule->registration_number : '') }}" maxlength="100" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Nomor registrasi">
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: PERIODE 1 -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Periode 1 (Tahun {{ $tahun }})
                </h3>
                
                <div class="grid gap-5 sm:grid-cols-3 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Calibration Date</label>
                        <input type="date" name="calibration_date" id="period1_calibration_date" 
                               value="{{ old('calibration_date', $editing ? $schedule->calibration_date?->format('Y-m-d') : '') }}" 
                               class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" onchange="hitungNextDate(1)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Next Calibration Date</label>
                        <input type="date" name="next_calibration_date" id="period1_next_calibration_date" 
                               value="{{ old('next_calibration_date', $editing ? $schedule->next_calibration_date?->format('Y-m-d') : '') }}" 
                               class="w-full rounded-lg border-slate-300 text-sm bg-slate-200 text-slate-600 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">JGM</label>
                        <input name="jgm" value="{{ old('jgm', $editing ? $schedule->jgm : '') }}" maxlength="30" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: OK">
                    </div>
                </div>
            </div>

            <!-- BAGIAN 3: PERIODE 2 -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Periode 2 (Tahun {{ $tahun }})
                </h3>
                
                <div class="grid gap-5 sm:grid-cols-3 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Calibration Date</label>
                        <input type="date" name="period2_calibration_date" id="period2_calibration_date" 
                               value="{{ old('period2_calibration_date', $editing ? $schedule->period2_calibration_date?->format('Y-m-d') : '') }}" 
                               class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" onchange="hitungNextDate(2)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Next Calibration Date</label>
                        <input type="date" name="period2_next_calibration_date" id="period2_next_calibration_date" 
                               value="{{ old('period2_next_calibration_date', $editing ? $schedule->period2_next_calibration_date?->format('Y-m-d') : '') }}" 
                               class="w-full rounded-lg border-slate-300 text-sm bg-slate-200 text-slate-600 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">JGM</label>
                        <input name="period2_jgm" value="{{ old('period2_jgm', $editing ? $schedule->period2_jgm : '') }}" maxlength="30" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" placeholder="Contoh: OK">
                    </div>
                </div>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun, 'jenis' => 'kalibrasi']) }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Simpan
                </button>
                @unless($editing)
                    <button type="submit" name="save_mode" value="another" class="inline-flex justify-center items-center rounded-lg bg-slate-800 px-6 py-2.5 text-sm font-semibold text-white hover:bg-slate-900 shadow-sm transition">
                        Simpan & Tambah Lagi
                    </button>
                @endunless
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const toolsWithTypes = @json($toolsWithTypes);
    const oldToolType = @json(old('tool_type', $editing ? $schedule->tool_type : ''));
    
    $(document).ready(function() {
        $('#tool_name').select2({ 
            placeholder: "-- Pilih atau Cari Alat --", 
            allowClear: true, 
            width: '100%'
        });

        $('#tool_name').on('change', function() {
            const selectedTool = $(this).val();
            updateTypeDropdown(selectedTool);
        });

        const initialTool = $('#tool_name').val();
        if (initialTool) {
            updateTypeDropdown(initialTool, oldToolType);
        }
    });

    function updateTypeDropdown(toolName, selectedValue = null) {
        const typeSelect = $('#tool_type');
        typeSelect.empty();

        // KONDISI 1: Tool Name KOSONG
        if (!toolName) {
            typeSelect.append('<option value="">-- Pilih Tool Name Dulu --</option>');
            const alpineData = typeSelect[0].closest('form').__x;
            if (alpineData) alpineData.$data.isNewType = false;
            return;
        }

        // KONDISI 2: Tool Name DIPILIH
        typeSelect.append('<option value="">Pilih type</option>');

        let types = toolsWithTypes[toolName] || [];

        if (types.length > 0) {
            types.forEach(function(type) {
                const isSelected = (selectedValue && selectedValue.toUpperCase() === type.toUpperCase()) ? 'selected' : '';
                typeSelect.append(`<option value="${type}" ${isSelected}>${type}</option>`);
            });
        } else {
            typeSelect.append('<option value="">-- Belum ada type untuk alat ini --</option>');
        }
        
        const isNewSelected = (selectedValue && selectedValue === '__new') ? 'selected' : '';
        typeSelect.append(`<option value="__new" ${isNewSelected}>＋ Tambah type baru…</option>`);
    }

    function hitungNextDate(periode) {
        const inputVerif = document.getElementById('period' + periode + '_calibration_date');
        const inputNext = document.getElementById('period' + periode + '_next_calibration_date');
        if (inputVerif.value) {
            let date = new Date(inputVerif.value);
            date.setMonth(date.getMonth() + 6);
            inputNext.value = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
        } else {
            inputNext.value = '';
        }
    }
</script>
@endsection