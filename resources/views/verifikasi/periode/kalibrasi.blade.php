@extends('layouts.app')

@section('content')
<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    @if(session('success'))<div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif
    
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <!-- FILTER TAHUN -->
        <form method="GET" action="{{ route('verifikasi.periode.index') }}" class="grid grid-cols-[max-content_minmax(0,1fr)] items-center gap-x-3 gap-y-3 sm:flex sm:flex-wrap">
            <!-- Hidden input agar saat ganti tahun, jenis tetap 'kalibrasi' -->
            <input type="hidden" name="jenis" value="kalibrasi">

            <label for="tahun" class="text-xs font-semibold text-slate-500">TAHUN</label>
            <select id="tahun" name="tahun" onchange="this.form.submit()" class="w-full min-w-0 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto">
                @for($y = max((int) date('Y') + 2, $tahun); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" @selected($tahun === $y)>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>
        
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-end">
            <p class="text-xs text-slate-500 mr-1">Total {{ $schedules->count() }} jadwal</p>
            
            @if(Auth::check() && Auth::user()->role === 'admin')
                <!-- Tombol Import -->
                <a href="{{ route('verifikasi.periode.kalibrasi.import.form', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1 whitespace-nowrap rounded-md border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:px-3 sm:py-2 sm:text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M4 16v4h16v-4" />
                    </svg>
                    Import
                </a>

                <!-- Tombol Export Dropdown -->
                <div class="relative inline-block" id="exportDropdownKalibrasi">
                    <button type="button" onclick="toggleExportKalibrasi()" class="inline-flex items-center gap-1 whitespace-nowrap rounded-md bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:px-3 sm:py-2 sm:text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v12m0 0 4-4m-4 4-4-4M4 8V4h16v4" />
                        </svg>
                        Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="exportMenuKalibrasi" class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                        <a href="{{ route('verifikasi.periode.kalibrasi.export', ['tahun' => $tahun]) }}" class="flex items-center gap-2.5 border-b border-gray-100 px-3 py-2.5 text-xs text-gray-700 transition hover:bg-blue-50">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0">
                                <path fill="#1D6F42" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/>
                                <path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/>
                                <text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="9">X</text>
                            </svg>
                            <span class="font-medium">Export Excel</span>
                        </a>
                        <a href="{{ route('verifikasi.periode.kalibrasi.export.pdf', ['tahun' => $tahun]) }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 transition hover:bg-blue-50">
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-red-600 text-[8px] font-bold text-white">PDF</span>
                            <span class="font-medium">Export PDF</span>
                        </a>
                    </div>
                </div>

                <!-- Tombol Tambah -->
                <a href="{{ route('verifikasi.periode.kalibrasi.create', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1 whitespace-nowrap rounded-md bg-blue-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:px-3 sm:py-2 sm:text-sm">
                    <span>+</span> Tambah
                </a>
            @endif
        </div>
    </div>
</div>

<p class="mb-2 text-xs text-slate-500 md:hidden">Geser tabel ke samping untuk melihat semua kolom →</p>
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="calibration-table w-full min-w-[1000px] table-auto border-collapse text-xs text-slate-700">
            <thead class="bg-slate-800 text-white">
                <tr class="text-center uppercase tracking-wide">
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">No</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 text-left align-middle">Tool Name</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Type</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Specification</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Number Seri</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Number Registration</th>
                    <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Shop</th>
                    
                    <th colspan="3" class="border border-slate-600 px-3 py-2 text-center bg-slate-700">Year {{ $tahun }}</th>
                    
                    @if(Auth::check() && Auth::user()->role === 'admin') 
                        <th rowspan="3" class="border border-slate-600 px-3 py-3 align-middle">Aksi</th> 
                    @endif
                </tr>

                <tr class="text-center uppercase">
                    <th colspan="3" class="border border-slate-600 px-3 py-2 bg-slate-700">Kalibrasi Tahunan</th>
                </tr>

                <tr class="text-center uppercase text-[10px]">
                    <th class="border border-slate-600 px-3 py-2">Calibration Date</th>
                    <th class="border border-slate-600 px-3 py-2">Next Calibration Date</th>
                    <th class="border border-slate-600 px-3 py-2">JGM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $schedule)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-blue-50">
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
                                <a href="{{ route('verifikasi.periode.kalibrasi.edit', $schedule) }}" class="rounded-md bg-blue-600 px-2 py-1 font-semibold text-white hover:bg-blue-700">Edit</a>
                                <form method="POST" action="{{ route('verifikasi.periode.kalibrasi.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal kalibrasi ini?')">@csrf @method('DELETE')<button class="rounded-md bg-rose-600 px-2 py-1 font-semibold text-white hover:bg-rose-700">Hapus</button></form>
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

<style>
    .main-content .calibration-table th,
    .main-content .calibration-table td {
        border-color: #d9d9d9;
    }

    .main-content .calibration-table thead tr:first-child th {
        background-color: #0070f2;
        border-color: #0054b5;
        color: #fff;
    }

    .main-content .calibration-table thead tr:not(:first-child) th {
        background-color: #eaf2f8;
        color: #1f2937;
    }
</style>

<script>
function toggleExportKalibrasi() {
    document.getElementById('exportMenuKalibrasi').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdownKalibrasi');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('exportMenuKalibrasi').classList.add('hidden');
    }
});
</script>
@endsection