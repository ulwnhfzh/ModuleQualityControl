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
                <option value="kalibrasi" {{ $jenis === 'kalibrasi' ? 'selected' : '' }}>Kalibrasi Internal</option>
                <option value="verifikasi" {{ $jenis === 'verifikasi' ? 'selected' : '' }}>Verifikasi Internal</option>
                <option value="verifikasi_qc2" {{ $jenis === 'verifikasi_qc2' ? 'selected' : '' }}>Verifikasi Internal QC2</option>
            </select>

            <label for="tahun" class="text-xs font-semibold text-slate-500">TAHUN</label>
            <select id="tahun" name="tahun" onchange="this.form.submit()" class="w-full min-w-0 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm sm:w-auto">
                @for($y = max((int) date('Y') + 2, $tahun); $y >= date('Y') - 12; $y--)
                    <option value="{{ $y }}" @selected($tahun === $y)>YEAR {{ $y }}</option>
                @endfor
            </select>
        </form>
        
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-end">
            <p class="text-xs text-slate-500 mr-1">Total {{ $schedules->count() }} jadwal QC2</p>
            
            @if(Auth::check() && Auth::user()->role === 'admin')
                <!-- TOMBOL IMPORT: Diubah ke route yang benar -->
                <a href="{{ route('verifikasi.qc2.import.form', ['tahun' => $tahun]) }}" class="bg-white border border-gray-300 text-gray-700 rounded font-semibold hover:bg-gray-50 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">📥 Import</a>
                
                <div class="relative inline-block" id="exportDropdownVerif">
                    <button type="button" onclick="toggleExportVerif()" class="bg-slate-800 text-white rounded font-semibold hover:bg-slate-900 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm">
                        📤 Export
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <div id="exportMenuVerif" class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                        <!-- TOMBOL EXPORT EXCEL: Diubah ke route yang benar -->
                        <a href="{{ route('verifikasi.qc2.export.excel', ['tahun' => $tahun]) }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-green-50 transition border-b border-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0"><path fill="#1D6F42" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/><path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/><text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="9">X</text></svg>
                            <span class="font-medium">Export Excel</span>
                        </a>
                        <a href="#" class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 hover:bg-red-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0"><path fill="#DC2626" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/><path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/><text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="7">PDF</text></svg>
                            <span class="font-medium">Export PDF</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('verifikasi.qc2.create', ['tahun' => $tahun]) }}" class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition inline-flex items-center gap-1 whitespace-nowrap px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-sm"><span>+</span> Tambah</a>
            @endif
        </div>
    </div>
</div>

<div class="mb-2 flex items-center justify-between text-xs text-slate-500">
    <span>{{ $schedules->count() }} alat terjadwal (QC2)</span>
    <span class="md:hidden">Geser tabel ke samping →</span>
</div>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1200px] table-auto border-collapse text-xs text-slate-700">
            <thead class="bg-slate-800 text-white">
                <tr class="text-center uppercase tracking-wide">
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">No</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 text-left align-middle">Tool Name</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">Type</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">Specification</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">Number Seri</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">Number Registration</th>
                    <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">Shop</th>
                    
                    <th colspan="3" class="border border-slate-600 px-3 py-2 text-center bg-slate-700">Year {{ $tahun }}</th>
                    
                    @if(Auth::check() && Auth::user()->role === 'admin') 
                        <th rowspan="2" class="border border-slate-600 px-3 py-3 align-middle">Aksi</th> 
                    @endif
                </tr>
                <tr class="text-center uppercase text-[10px]">
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
                        
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period1_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period1_next_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border border-slate-200 px-3 py-3 text-center">{{ $schedule->period1_jgm ?: '-' }}</td>
                        
                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="border border-slate-200 px-3 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('verifikasi.qc2.edit', $schedule) }}" class="rounded bg-amber-400 px-2 py-1 font-semibold text-white hover:bg-amber-500">Edit</a>
                                    <form method="POST" action="{{ route('verifikasi.qc2.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal QC2 ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded bg-rose-500 px-2 py-1 font-semibold text-white hover:bg-rose-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ Auth::check() && Auth::user()->role === 'admin' ? 11 : 10 }}" class="px-4 py-12 text-center text-slate-500">
                            Belum ada jadwal QC2 untuk tahun {{ $tahun }}. Tambahkan jadwal untuk mulai mengisi daftar ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleExportVerif() {
    document.getElementById('exportMenuVerif').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdownVerif');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('exportMenuVerif').classList.add('hidden');
    }
});
</script>
@endsection