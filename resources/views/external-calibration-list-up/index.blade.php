@extends('layouts.app')

@section('content')
@if(session('success'))<div class="mb-3 rounded border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>@endif
@if($errors->any())<div class="mb-3 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        
        {{-- BAGIAN KIRI: FILTER --}}
        <div class="flex flex-wrap items-center gap-3">
            <label for="external-category" class="text-xs font-semibold text-slate-500">KATEGORI</label>
            <select id="external-category" onchange="location.href=this.value" class="rounded-lg border-slate-300 bg-white px-3 py-2 text-sm">
                <option value="{{ route('eksternal.small', ['tahun' => $year]) }}">Small Tool</option>
                <option value="{{ route('mesin.index', ['tahun' => $year]) }}">Machine Alat Ukur</option>
                <option value="{{ route('master-calibrator.index', ['tahun' => $year]) }}">Master Kalibrator</option>
            </select>
            <form method="GET" action="{{ route('external-list-up.index') }}" class="flex items-center gap-2">
                <label for="external-list-up-year" class="text-xs font-semibold text-slate-500">TAHUN</label>
                <select id="external-list-up-year" name="tahun" onchange="this.form.submit()" class="rounded-lg border-slate-300 bg-white px-3 py-2 text-sm">
                    @for($y = date('Y'); $y >= date('Y') - 12; $y--)
                        <option value="{{ $y }}" @selected($year == $y)>YEAR {{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>

        {{-- BAGIAN KANAN: TOMBOL AKSI --}}
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-end">
            <p class="text-xs text-slate-500 mr-1">Total {{ $schedules->count() }} jadwal</p>
            
            @if(Auth::check() && Auth::user()->role === 'admin')
                <!-- Tombol Import (Rapi dengan Ikon) -->
                <a href="{{ route('external-list-up.import.form', ['tahun' => $year]) }}" 
                   class="border border-blue-600 text-blue-600 hover:bg-blue-50 font-semibold rounded-lg px-4 py-2 text-sm transition inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Import
                </a>

                <!-- Tombol Export Dropdown -->
                <div class="relative inline-block" id="exportDropdownListUp">
                    <button type="button" onclick="toggleExportListUp()" 
                            class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-blue-700 transition inline-flex items-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    {{-- ISI DROPDOWN EXPORT --}}
                    <div id="exportMenuListUp" class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                        
                        <!-- Pilihan Export Excel -->
                        <a href="{{ route('external-list-up.export', ['tahun' => $year]) }}" 
                           class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-blue-50 transition border-b border-gray-100">
                            <!-- Ikon Excel -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0">
                                <path fill="#1D6F42" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/>
                                <path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/>
                                <text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="9">X</text>
                            </svg>
                            <span class="font-medium">Export Excel</span>
                        </a>

                        <!-- Pilihan Export PDF -->
                        <a href="{{ route('external-list-up.export.pdf', ['tahun' => $year]) }}" 
                           class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-red-50 transition">
                            <!-- Ikon PDF -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0">
                                <path fill="#DC2626" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/>
                                <path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/>
                                <text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="7">PDF</text>
                            </svg>
                            <span class="font-medium">Export PDF</span>
                        </a>
                    </div>
                </div>

                <!-- Tombol Tambah -->
                <a href="{{ route('external-list-up.create', ['tahun' => $year]) }}" 
                   class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-blue-700 transition inline-flex items-center gap-1 shadow-sm">
                    <span class="text-lg leading-none">+</span> Tambah
                </a>
            @endif
        </div>
    </div>
</div>

<section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
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

<script>
function toggleExportListUp() {
    document.getElementById('exportMenuListUp').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdownListUp');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('exportMenuListUp').classList.add('hidden');
    }
});
</script>
@endsection