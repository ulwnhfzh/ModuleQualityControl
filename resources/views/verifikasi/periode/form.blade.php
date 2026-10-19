@extends('layouts.app')

@section('content')
@php($editing = isset($schedule))
<div class="mx-auto max-w-5xl rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    <div class="mb-6">
        <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun]) }}" class="text-sm font-medium text-emerald-700">← Kembali ke tabel periode</a>
        <h1 class="mt-3 text-xl font-bold text-slate-800">{{ $editing ? 'Edit' : 'Tambah' }} Jadwal Verifikasi Periode</h1>
        <p class="mt-1 text-sm text-slate-500">Informasi produk diisi dan disimpan langsung pada List Up ini.</p>
    </div>
    @if(session('success'))<div class="mb-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ $editing ? route('verifikasi.periode.update', $schedule) : route('verifikasi.periode.store') }}" class="space-y-6">
        @csrf @if($editing) @method('PUT') @endif
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="text-sm font-semibold text-slate-700">Tool Name
                <input name="tool_name" value="{{ old('tool_name', $schedule->tool_name ?? '') }}" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Nama alat">
            </label>
            <label class="text-sm font-semibold text-slate-700">Type
                <div class="relative mt-1" x-data="{
                    open: false,
                    query: @js(old('tool_type', $schedule->tool_type ?? '')).toLocaleUpperCase(),
                    duplicateMessage: '',
                    options: [...new Set(@js($types->values()).map(type => type.trim().toLocaleUpperCase()))],
                    findExistingType(value) {
                        const normalized = value.trim().toLocaleUpperCase();
                        return this.options.find(option => option.trim().toLocaleUpperCase() === normalized) || null;
                    },
                    addCurrentType() {
                        const value = this.query.trim().toLocaleUpperCase();
                        if (!value) return;
                        const existing = this.findExistingType(value);
                        if (existing) {
                            this.query = existing;
                            this.duplicateMessage = 'Tipe ' + existing + ' sudah terdaftar. Pilih tipe yang tersedia agar tidak membuat duplikat.';
                            this.open = true;
                            return;
                        }
                        this.options.unshift(value);
                        this.query = value;
                        this.duplicateMessage = '';
                        this.open = false;
                    }
                }" @click.outside="open = false">
                    <input name="tool_type" x-model="query" @focus="open = true" @input="query = $event.target.value.toLocaleUpperCase(); duplicateMessage = findExistingType(query) ? 'Tipe ini sudah terdaftar. Pilih opsi yang tersedia agar tidak membuat duplikat.' : ''; open = true" @change="query = query.trim().toLocaleUpperCase()" @keydown.escape="open = false" @keydown.enter.prevent="addCurrentType()" required maxlength="100" autocomplete="off" class="w-full rounded-lg border-slate-300 py-2.5 pl-3 pr-10 text-sm uppercase shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" placeholder="Pilih atau ketik tipe" aria-label="Type">
                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-emerald-700" aria-label="Buka pilihan tipe">
                        <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" /></svg>
                    </button>
                    <div x-show="open" x-cloak x-transition.origin.top class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-xl">
                        <template x-for="type in options.filter(option => option.toLowerCase().includes(query.trim().toLowerCase()))" :key="type">
                            <button type="button" @click="query = type; duplicateMessage = ''; open = false" class="flex w-full items-center justify-between px-3 py-2 text-left text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-800">
                                <span x-text="type"></span>
                                <svg x-show="query.toLowerCase() === type.toLowerCase()" class="h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" /></svg>
                            </button>
                        </template>
                        <button type="button" x-show="query.trim() && !findExistingType(query)" @click="addCurrentType()" class="flex w-full items-center gap-2 border-t border-slate-100 px-3 py-2.5 text-left text-sm font-medium text-emerald-700 hover:bg-emerald-50">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100">+</span>
                            <span>Tambahkan “<span x-text="query.trim()"></span>” sebagai tipe baru</span>
                        </button>
                        <p x-show="duplicateMessage" x-text="duplicateMessage" class="border-t border-amber-100 bg-amber-50 px-3 py-2.5 text-xs font-medium text-amber-800"></p>
                        <p x-show="!query.trim() && options.length === 0" class="px-3 py-3 text-sm text-slate-500">Belum ada tipe. Ketik untuk menambahkan.</p>
                    </div>
                </div>
                <span class="mt-1 block text-xs font-normal text-slate-500">Pilih tipe yang ada atau ketik tipe baru. Setelah jadwal disimpan, tipe baru muncul di pilihan berikutnya.</span>
            </label>
            <label class="text-sm font-semibold text-slate-700">Specification
                <input name="specification" value="{{ old('specification', $schedule->specification ?? '') }}" maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Spesifikasi alat">
            </label>
            <label class="text-sm font-semibold text-slate-700">Number Seri
                <input name="serial_number" value="{{ old('serial_number', $schedule->serial_number ?? '') }}" maxlength="100" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Nomor seri">
            </label>
            <label class="text-sm font-semibold text-slate-700">Number Registration
                <input name="registration_number" value="{{ old('registration_number', $schedule->registration_number ?? '') }}" maxlength="100" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Nomor registrasi">
            </label>
            <label class="text-sm font-semibold text-slate-700">Shop
                <input name="shop" value="{{ old('shop', $schedule->shop ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Contoh: P1">
            </label>
        </div>
        @foreach([1, 2] as $period)
            <fieldset class="rounded-xl border border-slate-200 p-4">
                <legend class="px-2 text-sm font-bold text-slate-800">Period {{ $period }} · {{ $tahun }}</legend>
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="text-sm font-medium text-slate-700">Verification Date
                        <x-date-entry name="period{{ $period }}_verification_date" :value="old('period'.$period.'_verification_date', isset($schedule) ? $schedule->{'period'.$period.'_verification_date'}?->format('Y-m-d') : '')" input-class="mt-1 w-full rounded-lg border-slate-300 text-sm" />
                    </label>
                    <label class="text-sm font-medium text-slate-700">Next Verification Date
                        <x-date-entry name="period{{ $period }}_next_verification_date" :value="old('period'.$period.'_next_verification_date', isset($schedule) ? $schedule->{'period'.$period.'_next_verification_date'}?->format('Y-m-d') : '')" input-class="mt-1 w-full rounded-lg border-slate-300 text-sm" />
                    </label>
                    <label class="text-sm font-medium text-slate-700">JGM
                        <input name="period{{ $period }}_jgm" value="{{ old('period'.$period.'_jgm', $schedule->{'period'.$period.'_jgm'} ?? '') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm" placeholder="Contoh: OK">
                    </label>
                </div>
            </fieldset>
        @endforeach
        <div class="flex justify-end gap-2">
            <a href="{{ route('verifikasi.periode.index', ['tahun' => $tahun]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
            <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
            @unless($editing)
                <button type="submit" name="save_mode" value="another" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Simpan & Tambah Lagi</button>
            @endunless
        </div>
    </form>
</div>
@endsection
