@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('defects.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Kembali ke Data NG
        </a>
        <h1 class="mt-3 text-2xl font-bold text-slate-800">Edit Data Kerusakan (NG)</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data kerusakan untuk divisi {{ $defect->division }}.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @if($errors->any())
            <div class="bg-rose-50 border-b border-rose-200 px-6 py-4 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('defects.update', $defect->id) }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tahun</label>
                    <input type="number" name="tahun" value="{{ old('tahun', $defect->tahun) }}" class="w-full rounded-lg border-slate-300 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Bulan</label>
                    <select name="bulan" class="w-full rounded-lg border-slate-300 text-sm" required>
                        @foreach(\App\Models\ToolDefect::MONTHS as $m)
                            <option value="{{ $m }}" {{ old('bulan', $defect->bulan) == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Division</label>
                    <select name="division" class="w-full rounded-lg border-slate-300 text-sm" required>
                        @foreach(\App\Models\ToolDefect::DIVISIONS as $div)
                            <option value="{{ $div }}" {{ old('division', $defect->division) == $div ? 'selected' : '' }}>{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-6">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Jenis Kerusakan</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach(['aus'=>'AUS', 'erorr'=>'ERORR', 'gompal'=>'GOMPAL', 'hilang'=>'HILANG', 'karat'=>'KARAT', 'macet'=>'MACET', 'min_std'=>'MIN STD', 'over_std'=>'OVER STD', 'patah'=>'PATAH', 'rusak'=>'RUSAK', 'seret'=>'SERET', 'lain_lain'=>'LAIN-LAIN'] as $key => $label)
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ $label }}</label>
                            <input type="number" name="{{ $key }}" value="{{ old($key, $defect->$key) }}" min="0" class="w-full rounded-lg border-slate-300 text-sm text-center" required>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('defects.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="px-6 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm transition">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>
@endsection