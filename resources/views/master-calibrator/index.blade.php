@extends('layouts.app')

@section('content')
<div class="feature-toolbar mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('master-calibrator.index') }}" class="flex flex-wrap items-center gap-3">
            <label for="calibration-category" class="text-xs font-semibold text-slate-600">KATEGORI</label>
            <select id="calibration-category" onchange="location.href=this.value" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="{{ route('eksternal.small', ['tahun' => $tahun]) }}">Small Tool</option>
                <option value="{{ route('mesin.index', ['tahun' => $tahun]) }}">Machine Alat Ukur</option>
                <option value="{{ route('master-calibrator.index', ['tahun' => $tahun]) }}" selected>Master Kalibrator</option>
            </select>
            <label for="year" class="text-xs font-semibold text-slate-600">TAHUN</label>
            <select id="year" name="tahun" onchange="this.form.submit()" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm">
                @for($year = date('Y'); $year >= date('Y') - 12; $year--)
                    <option value="{{ $year }}" @selected($tahun == $year)>YEAR {{ $year }}</option>
                @endfor
            </select>
        </form>
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-xs text-slate-500">Total {{ $schedules->count() }} alat</span>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <form action="{{ route('master-calibrator.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="file" name="file" accept=".xls,.xlsx" required class="max-w-52 text-xs">
                    <button class="rounded border border-blue-600 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">Import workbook</button>
                </form>
                <a href="{{ route('master-calibrator.export', ['tahun' => $tahun]) }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">Export</a>
                <a href="{{ route('master-calibrator.create', ['tahun' => $tahun]) }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah Alat</a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))<div class="mb-3 rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif
@if($errors->any())<div class="mb-3 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <header class="border-b border-slate-200 px-4 py-3">
        <h1 class="font-semibold text-slate-800">Master Kalibrator — {{ $tahun }}</h1>
        <p class="mt-1 text-xs text-slate-500">Struktur kolom mengikuti workbook master: identitas alat, hasil per bulan, dan catatan.</p>
    </header>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1800px] border-collapse text-xs text-slate-700">
            <thead class="uppercase">
                <tr class="bg-blue-600 text-white">
                    @foreach(['NO','TOOL NAME','TYPE','SIZE','NUMBER SERI','NUMBER REG','PART NAME','PART NUMBER'] as $heading)
                        <th rowspan="2" class="border border-blue-700 px-3 py-3 text-center">{{ $heading }}</th>
                    @endforeach
                    <th colspan="12" class="border border-blue-700 px-3 py-2 text-center">RESULT</th>
                    <th rowspan="2" class="border border-blue-700 px-3 py-3 text-center">NOTE</th>
                    @if(Auth::check() && Auth::user()->role === 'admin')<th rowspan="2" class="border border-blue-700 px-3 py-3 text-center">AKSI</th>@endif
                </tr>
                <tr class="bg-blue-100 text-slate-700">
                    @foreach(['JAN','FEB','MAR','APR','MEI','JUN','JUL','AGS','SEP','OKT','NOV','DES'] as $month)
                        <th class="border border-slate-300 px-3 py-2 text-center">{{ $month }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $item)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-blue-50">
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $item->tool_name }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->type ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->size ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->number_seri ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->number_reg ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->part_name ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->part_number ?: '-' }}</td>
                        @foreach(['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $month)
                            <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->$month ?: '-' }}</td>
                        @endforeach
                        <td class="max-w-xs whitespace-normal border border-slate-200 px-3 py-2">{{ $item->note ?: '-' }}</td>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="border border-slate-200 px-3 py-2 text-center"><div class="flex justify-center gap-1">
                                <a href="{{ route('master-calibrator.edit', $item) }}" class="rounded bg-blue-600 px-2 py-1 text-white">Edit</a>
                                <form action="{{ route('master-calibrator.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data alat ini?')">@csrf @method('DELETE')<button class="rounded bg-rose-600 px-2 py-1 text-white">Hapus</button></form>
                            </div></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ Auth::check() && Auth::user()->role === 'admin' ? 22 : 21 }}" class="border border-slate-200 px-4 py-10 text-center text-slate-500">Belum ada data Master Kalibrator untuk tahun {{ $tahun }}. Impor workbook alat atau pilih Tambah Alat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
