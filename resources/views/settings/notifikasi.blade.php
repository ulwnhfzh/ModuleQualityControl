@extends('layouts.app')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('error') }}
</div>
@endif

{{-- HEADER --}}
<div class="bg-white rounded-lg shadow-sm mb-4 px-4 py-3">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-base font-bold text-gray-800">⚙️ Pengaturan Notifikasi</h1>
            <p class="text-xs text-gray-500 mt-0.5">Atur sensitivitas dan tampilan notifikasi jatuh tempo</p>
        </div>
        <a href="{{ route('settings.index') }}"
           class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
            ← Kembali ke Settings
        </a>
    </div>
</div>

{{-- FORM --}}
<form action="{{ route('settings.notifikasi.update') }}" method="POST">
    @csrf

    {{-- KATEGORI WARNING --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm mb-4">
        <h2 class="font-bold text-slate-800 mb-4">🔔 Kategori Notifikasi</h2>

        <div class="space-y-4">

            {{-- Warning Cepat --}}
            <div class="border border-orange-200 bg-orange-50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">🟠</div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-orange-800 mb-1">
                            Warning Cepat (Warna Oranye)
                        </label>
                        <p class="text-xs text-orange-600 mb-2">
                            Notifikasi muncul untuk alat yang akan jatuh tempo dalam beberapa hari ke depan.
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="warning_days_cepat"
                                   value="{{ $pengaturan['warning_days_cepat'] }}"
                                   min="1" max="365"
                                   class="w-24 border border-orange-300 rounded px-3 py-2 text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="text-sm text-orange-700 font-medium">hari</span>
                        </div>
                        <p class="text-[10px] text-orange-600 mt-1">
                            Contoh: Jika diisi <strong>7</strong>, maka alat yang jatuh tempo ≤ 7 hari akan muncul dengan warna oranye.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Warning Standar --}}
            <div class="border border-blue-200 bg-blue-50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">🔵</div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-blue-800 mb-1">
                            Warning Standar (Warna Biru)
                        </label>
                        <p class="text-xs text-blue-600 mb-2">
                            Notifikasi standar untuk alat yang akan jatuh tempo dalam beberapa hari/minggu ke depan.
                        </p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="warning_days_standar"
                                   value="{{ $pengaturan['warning_days_standar'] }}"
                                   min="1" max="365"
                                   class="w-24 border border-blue-300 rounded px-3 py-2 text-sm font-bold text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="text-sm text-blue-700 font-medium">hari</span>
                        </div>
                        <p class="text-[10px] text-blue-600 mt-1">
                            Contoh: Jika diisi <strong>30</strong>, maka alat yang jatuh tempo ≤ 30 hari akan muncul dengan warna biru.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info Terlambat --}}
            <div class="border border-rose-200 bg-rose-50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">🔴</div>
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-rose-800 mb-1">
                            Terlambat (Warna Merah)
                        </label>
                        <p class="text-xs text-rose-600">
                            <strong>Otomatis</strong> — Alat yang sudah melewati tanggal jatuh tempo akan selalu muncul dengan warna merah.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAMPILAN NOTIFIKASI --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm mb-4">
        <h2 class="font-bold text-slate-800 mb-4">📍 Tampilan Notifikasi</h2>

        <div class="space-y-3">

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="notif_dashboard" value="1"
                       {{ $pengaturan['notif_dashboard'] == 1 ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm text-gray-700">Tampilkan notifikasi di <strong>Dashboard</strong></span>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="notif_header" value="1"
                       {{ $pengaturan['notif_header'] == 1 ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <span class="text-sm text-gray-700">Tampilkan badge notifikasi di <strong>Header</strong> (ikon bell)</span>
            </label>
        </div>
    </div>

    {{-- TOMBOL SIMPAN --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
        <p class="text-xs text-gray-500">
            ⚠️ Perubahan akan langsung berlaku setelah disimpan.
        </p>
        <div class="flex gap-2">
            <a href="{{ route('settings.index') }}"
               class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">
                Batal
            </a>
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded text-sm font-semibold hover:bg-blue-700">
                💾 Simpan Pengaturan
            </button>
        </div>
    </div>

</form>

@endsection