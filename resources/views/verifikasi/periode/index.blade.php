@extends('layouts.app')

@section('content')
<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
    @endif
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('verifikasi.periode.index') }}" class="grid grid-cols-[max-content_minmax(0,1fr)] items-center gap-x-3 gap-y-3 sm:flex sm:flex-wrap">
            <label for="jenis-list-up" class="text-xs font-semibold text-slate-500">JENIS LIST UP</label>
            <select id="jenis-list-up" name="jenis" onchange="this.form.submit()" class="w-full min-w-0 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto">
                <option value="kalibrasi">Kalibrasi Internal</option>
                <option value="verifikasi" selected>Verifikasi Internal</option>
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
                <a href="{{ route('verifikasi.periode.import.form', ['tahun' => $tahun]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto sm:px-4">
                    <span aria-hidden="true">📥</span> Import
                </a>
                <a href="{{ route('verifikasi.periode.export', ['tahun' => $tahun]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto sm:px-4">
                    <span aria-hidden="true">📤</span> Export
                </a>
                <a href="{{ route($jenis === 'kalibrasi' ? 'verifikasi.periode.kalibrasi.create' : 'verifikasi.periode.create', ['tahun' => $tahun]) }}" class="col-span-2 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-black px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 sm:col-span-1 sm:w-auto">+ Tambah Jadwal</a>
            @endif
        </div>
    </div>
</div>

<div class="mb-2 flex items-center justify-between text-xs text-slate-500">
    <span>{{ $schedules->count() }} alat terjadwal</span>
    <span class="md:hidden">Geser tabel ke samping untuk melihat semua periode →</span>
</div>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <!-- Tambahan table-auto agar proporsi kolom lebih baik -->
        <table class="w-full min-w-[1450px] table-auto border-collapse text-xs text-slate-700">
            <thead class="bg-slate-800 text-white">
                <!-- BARIS 1: Header Utama -->
                <tr class="text-center uppercase tracking-wide">
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">No</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 text-left align-middle">Tool Name</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Type</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Specification</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Number Seri</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Number Registration</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Shop</th>
                    
                    <!-- Header Tahun -->
                    <th colspan="6" class="border border-slate-600 px-3 py-2 text-center bg-slate-700">Year {{ $tahun }}</th>
                    
                    @if(Auth::check() && Auth::user()->role === 'admin') 
                        <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Aksi</th> 
                    @endif
                </tr>

                <!-- BARIS 2: Header Periode -->
                <tr class="text-center uppercase">
                    <th colspan="3" class="border border-slate-600 px-3 py-2 bg-slate-700">Periode 1</th>
                    <th colspan="3" class="border border-slate-600 px-3 py-2 bg-slate-700">Periode 2</th>
                </tr>

                <!-- BARIS 3: Header Sub-Kolom -->
                <tr class="text-center uppercase text-[10px]">
                    <th class="border border-slate-600 px-3 py-2">Verification Date</th>
                    <th class="border border-slate-600 px-3 py-2">Next Verification Date</th>
                    <th class="border border-slate-600 px-3 py-2">JGM</th>
                    <th class="border border-slate-600 px-3 py-2">Verification Date</th>
                    <th class="border border-slate-600 px-3 py-2">Next Verification Date</th>
                    <th class="border border-slate-600 px-3 py-2">JGM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $schedule)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-emerald-50/50">
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-200 px-3 py-3 font-semibold text-slate-800">{{ $schedule->tool_name ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->tool_type ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3">{{ $schedule->specification ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->serial_number ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->registration_number ?: '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->shop ?: '-' }}</td>
                        
                        <!-- Data Periode 1 -->
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period1_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period1_next_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period1_jgm ?: '-' }}</td>
                        
                        <!-- Data Periode 2 -->
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period2_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period2_next_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period2_jgm ?: '-' }}</td>
                        
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="border border-slate-200 px-3 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('verifikasi.periode.edit', $schedule) }}" class="rounded bg-amber-400 px-2 py-1 font-semibold text-white hover:bg-amber-500">Edit</a>
                                    <form method="POST" action="{{ route('verifikasi.periode.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal periode ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded bg-rose-500 px-2 py-1 font-semibold text-white hover:bg-rose-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <!-- Perbaikan Colspan: 7 kolom kiri + 6 kolom periode + 1 kolom aksi (jika admin) -->
                    <tr>
                        <td colspan="{{ Auth::check() && Auth::user()->role === 'admin' ? 14 : 13 }}" class="px-4 py-12 text-center text-slate-500">
                            Belum ada jadwal periode untuk tahun {{ $tahun }}. Tambahkan jadwal untuk mulai mengisi daftar ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
