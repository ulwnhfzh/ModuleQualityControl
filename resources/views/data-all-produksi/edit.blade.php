@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('data-all-produksi.index', ['division' => $item->division]) }}" class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Data All Produksi
        </a>
        <h1 class="mt-3 text-2xl font-bold text-slate-800">Edit Data All Produksi</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @if($errors->any())
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-4 text-sm text-rose-800 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form action="{{ route('data-all-produksi.update', $item->id) }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')
            
            <!-- BAGIAN 1: INFORMASI ALAT -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    Informasi Alat
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Division *</label>
                        <select name="division" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">-- Pilih Division --</option>
                            @foreach($divisionList as $division)
                                <option value="{{ $division }}" {{ old('division', $item->division) == $division ? 'selected' : '' }}>{{ $division }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tool Name *</label>
                        <input type="text" name="tool_name" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('tool_name', $item->tool_name) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Type *</label>
                        <input type="text" name="type" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('type', $item->type) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Size *</label>
                        <input type="text" name="size" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('size', $item->size) }}">
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: INFORMASI PART -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    Informasi Part & Registrasi
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Part Name</label>
                        <input type="text" name="part_name" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('part_name', $item->part_name) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Part Number</label>
                        <input type="text" name="part_number" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('part_number', $item->part_number) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Number Seri</label>
                        <input type="text" name="number_seri" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('number_seri', $item->number_seri) }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Number Reg</label>
                        <input type="text" name="number_reg" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500" value="{{ old('number_reg', $item->number_reg) }}">
                    </div>
                </div>
            </div>

            <!-- BAGIAN 3: RESULT (BULAN) - TIPE TEXT -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-200 pb-2 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Jumlah Result / Penggunaan
                </h3>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @foreach(['jan'=>'JAN', 'feb'=>'FEB', 'mar'=>'MAR', 'apr'=>'APR', 'may'=>'MEI', 'jun'=>'JUN', 'jul'=>'JUL', 'aug'=>'AUG', 'sep'=>'SEP', 'oct'=>'OCT', 'nov'=>'NOV', 'dec'=>'DEC'] as $key => $label)
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ $label }}</label>
                            <!-- UBAH KE TYPE="TEXT" -->
                            <input type="text" name="{{ $key }}" value="{{ old($key, $item->$key) }}" class="w-full rounded-lg border-slate-300 text-sm text-center focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- BAGIAN 4: NOTE -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Note (Keterangan Tambahan)</label>
                <textarea name="note" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('note', $item->note) }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('data-all-produksi.index', ['division' => $item->division]) }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>
@endsection2