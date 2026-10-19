@extends('layouts.app')

@section('content')
<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    @if(session('success'))<div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('verifikasi.periode.index') }}" class="grid grid-cols-[max-content_minmax(0,1fr)] items-center gap-x-3 gap-y-3 sm:flex sm:flex-wrap">
            <label for="jenis-list-up" class="text-xs font-semibold text-slate-500">JENIS LIST UP</label>
            <select id="jenis-list-up" name="jenis" onchange="this.form.submit()" class="w-full min-w-0 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto">
                <option value="kalibrasi" selected>Kalibrasi Internal</option>
                <option value="verifikasi">Verifikasi Internal</option>
            </select>
            <label for="tahun" class="text-xs font-semibold text-slate-500">TAHUN</label>
            <select id="tahun" name="tahun" onchange="this.form.submit()" class="w-full min-w-0 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto">
                @for($y = max((int) date('Y') + 2, $tahun); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" @selected($tahun === $y)>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>
        <div class="grid grid-cols-2 items-center gap-2 sm:flex sm:flex-wrap sm:gap-3 lg:justify-end">
            <p class="col-span-2 text-xs text-slate-500 sm:col-span-1 sm:mr-1">Total {{ $schedules->count() }} jadwal</p>
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('verifikasi.periode.kalibrasi.import.form', ['tahun' => $tahun]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto sm:px-4"><span aria-hidden="true">📥</span> Import</a>
                <a href="{{ route('verifikasi.periode.kalibrasi.export', ['tahun' => $tahun]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:px-4"><span aria-hidden="true">📤</span> Export</a>
                <a href="{{ route('verifikasi.periode.kalibrasi.create', ['tahun' => $tahun]) }}" class="col-span-2 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-black px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:col-span-1 sm:w-auto">+ Tambah Jadwal</a>
            @endif
        </div>
    </div>
</div>

<p class="mb-2 text-xs text-slate-500 md:hidden">Geser tabel ke samping untuk melihat semua kolom →</p>
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1450px] border-collapse text-xs text-slate-700">
            <thead class="bg-slate-800 text-white">
                <tr class="text-center uppercase tracking-wide">
                    <th rowspan="2" class="border border-slate-600 px-3 py-3">No</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 text-left">Tool Name</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3">Type</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3">Specification</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3">Number Seri</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3">Number Registration</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3">Shop</th>
                    <th colspan="3" class="border border-slate-600 bg-slate-700 px-3 py-2">YEAR {{ $tahun }}</th>
                    @if(Auth::check() && Auth::user()->role === 'admin')<th rowspan="2" class="border border-slate-600 px-3 py-3">Aksi</th>@endif
                </tr>
                <tr class="text-center uppercase">
                    <th class="border border-slate-600 px-3 py-2">Calibration Date</th>
                    <th class="border border-slate-600 px-3 py-2">Next Calibration Date</th>
                    <th class="border border-slate-600 px-3 py-2">JGM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $schedule)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-emerald-50/50">
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-200 px-3 py-3 font-semibold text-slate-800">{{ $schedule->tool_name }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->tool_type }}</td>
                        <td class="border border-slate-200 px-3 py-3">{{ $schedule->specification ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->serial_number ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->registration_number ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->shop ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->calibration_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->next_calibration_date?->locale('id')->translatedFormat('d F Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->jgm ?: '-' }}</td>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="border border-slate-200 px-3 py-3"><div class="flex justify-center gap-2">
                                <a href="{{ route('verifikasi.periode.kalibrasi.edit', $schedule) }}" class="rounded bg-amber-400 px-2 py-1 font-semibold text-white hover:bg-amber-500">Edit</a>
                                <form method="POST" action="{{ route('verifikasi.periode.kalibrasi.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal kalibrasi ini?')">@csrf @method('DELETE')<button class="rounded bg-rose-500 px-2 py-1 font-semibold text-white hover:bg-rose-600">Hapus</button></form>
                            </div></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ Auth::check() && Auth::user()->role === 'admin' ? 11 : 10 }}" class="px-4 py-12 text-center text-slate-500">Belum ada jadwal kalibrasi internal untuk tahun {{ $tahun }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
