@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <!-- HEADER -->
    <div class="mb-6">
        <a href="{{ route('data-all-produksi.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Data All Produksi
        </a>
        <h1 class="mt-3 text-2xl font-bold text-slate-800">Import Data All Produksi</h1>
        <p class="text-sm text-slate-500 mt-1">Upload file Excel (.xlsx, .xls) atau CSV. Pastikan formatnya sesuai.</p>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        
        @if(session('error'))
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-4 text-sm text-rose-800 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <form action="{{ route('data-all-produksi.import.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih File Excel/CSV</label>
                <input type="file" name="file" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                @error('file') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h4 class="text-sm font-bold text-blue-800 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Informasi Penting
                </h4>
                <ul class="list-disc list-inside text-xs text-blue-700 space-y-1">
                    <li>Anda bisa meng-upload <strong>satu file Excel dengan banyak Sheet</strong> sekaligus.</li>
                    <li>Sistem akan otomatis membaca <strong>Nama Sheet</strong> sebagai <strong>Division</strong> (contoh: "QA 1", "LINE SCC").</li>
                    <li>Pastikan baris ke-4 pada Excel Anda sudah berisi data (baris 1-3 dianggap header).</li>
                    <li>Jika ada data dengan <strong>Division, Tool Name, dan Size</strong> yang sama, data lama akan otomatis diperbarui.</li>
                </ul>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('data-all-produksi.index') }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    Proses Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection