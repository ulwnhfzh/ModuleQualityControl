@extends('layouts.app')

@section('content')
@if(session('success'))<div class="mb-3 rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif
@if($errors->any())<div class="mb-3 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

<div class="feature-toolbar mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-3">
            <label for="external-category" class="text-xs font-semibold text-slate-600">KATEGORI</label>
            <select id="external-category" onchange="location.href=this.value" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="{{ route('eksternal.small', ['tahun' => $year]) }}">Small Tool</option>
                <option value="{{ route('mesin.index', ['tahun' => $year]) }}">Mesin Alat Ukur</option>
                <option value="{{ route('master-calibrator.index', ['tahun' => $year]) }}">Master Kalibrator</option>
            </select>
            <form method="GET" action="{{ route('external-list-up.index') }}" class="flex items-center gap-2">
                <label for="external-list-up-year" class="text-xs font-semibold text-slate-600">TAHUN</label>
                <select id="external-list-up-year" name="tahun" onchange="this.form.submit()" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm">
                    @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                        <option value="{{ $y }}" @selected($year == $y)>YEAR {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-xs text-slate-500">Total {{ $schedules->count() }} jadwal</span>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <form action="{{ route('external-list-up.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                    @csrf<input type="hidden" name="tahun" value="{{ $year }}">
                    <input type="file" name="file" accept=".xls,.xlsx" required class="max-w-52 text-xs">
                    <button class="rounded border border-blue-600 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">Import Excel</button>
                </form>
                <a href="{{ route('external-list-up.export', ['tahun' => $year]) }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">Export Excel</a>
                <a href="{{ route('external-list-up.create', ['tahun' => $year]) }}" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ Tambah Jadwal</a>
            @endif
        </div>
    </div>
</div>

<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <header class="border-b border-slate-200 px-4 py-3">
        <h1 class="font-semibold text-slate-800">List Up External Calibration</h1>
        <p class="mt-1 text-xs text-slate-500">PT Hamatetsu Indonesia · Tahun {{ $year }}</p>
    </header>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1500px] border-collapse text-xs text-slate-700">
            <thead class="uppercase">
                <tr class="bg-blue-600 text-white">
                    @foreach(['NO','TOOL NAME','SPECIFICATION','NUMBER SERI','NUMBER REGISTRATION','SHOP'] as $heading)
                        <th rowspan="2" class="border border-blue-700 px-3 py-3 text-center">{{ $heading }}</th>
                    @endforeach
                    <th colspan="4" class="border border-blue-700 px-3 py-2 text-center">YEAR {{ $year }}</th>
                    <th rowspan="2" class="border border-blue-700 px-3 py-3 text-center">NOTE</th>
                    @if(Auth::check() && Auth::user()->role === 'admin')<th rowspan="2" class="border border-blue-700 px-3 py-3 text-center">AKSI</th>@endif
                </tr>
                <tr class="bg-blue-100 text-slate-700">
                    @foreach(['CALIBRATION DATE','NEXT CALIBRATION DATE','JGM','NUMBER KALIBRASI'] as $heading)
                        <th class="border border-slate-300 px-3 py-2 text-center">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $item)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-blue-50">
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-200 px-3 py-2 font-semibold">{{ $item->tool_name }}</td>
                        <td class="border border-slate-200 px-3 py-2">{{ $item->specification ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->serial_number ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->registration_number ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->shop ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->calibration_date?->format('d F Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->next_calibration_date?->format('d F Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->jgm ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-center">{{ $item->calibration_count ?? '-' }}</td>
                        <td class="max-w-xs whitespace-normal border border-slate-200 px-3 py-2">{{ $item->note ?: '-' }}</td>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="border border-slate-200 px-3 py-2 text-center"><div class="flex justify-center gap-1">
                                <a href="{{ route('external-list-up.edit', $item) }}" class="rounded bg-blue-600 px-2 py-1 text-white">Edit</a>
                                <form action="{{ route('external-list-up.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">@csrf @method('DELETE')<button class="rounded bg-rose-600 px-2 py-1 text-white">Hapus</button></form>
                            </div></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ Auth::check() && Auth::user()->role === 'admin' ? 12 : 11 }}" class="border border-slate-200 px-4 py-10 text-center text-slate-500">Belum ada jadwal external calibration tahun {{ $year }}. Impor workbook atau tambah jadwal baru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
