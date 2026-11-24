@extends('layouts.app')

@section('content')
<div class="feature-toolbar mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4 lg:px-5">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
    @endif

    <div class="feature-toolbar-layout flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
        <!-- KIRI: Filter -->
        <form method="GET" action="{{ route('verifikasi.periode.index') }}" class="flex w-full min-w-0 flex-wrap items-end gap-2 xl:w-auto xl:flex-nowrap xl:items-center xl:gap-3">
            <div class="flex min-w-0 basis-[calc(50%-0.25rem)] flex-col gap-1 sm:basis-auto xl:flex-row xl:items-center xl:gap-1.5">
                <label for="company-list-up" class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wide text-slate-500">PERUSAHAAN</label>
                <select id="company-list-up" name="company" onchange="this.form.submit()" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 sm:w-auto xl:w-[130px]">
                    @foreach(['HAMATETSU', 'NISSO GIKEN'] as $companyOption)
                        <option value="{{ $companyOption }}" @selected($company === $companyOption)>{{ $companyOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex min-w-0 basis-full flex-col gap-1 sm:basis-auto xl:flex-row xl:items-center xl:gap-1.5">
                <label for="jenis-list-up" class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wide text-slate-500">JENIS LIST UP</label>
                <select id="jenis-list-up" name="jenis" onchange="this.form.submit()" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 sm:w-auto xl:w-[200px]">
                    <option value="verifikasi" {{ $jenis === 'verifikasi' ? 'selected' : '' }}>Verification Internal</option>
                    <option value="verifikasi_qc2" {{ $jenis === 'verifikasi_qc2' ? 'selected' : '' }}>Verification Internal QC2</option>
                </select>
            </div>

            <div class="flex min-w-0 basis-[calc(50%-0.25rem)] flex-col gap-1 sm:basis-auto xl:flex-row xl:items-center xl:gap-1.5">
                <label for="tahun" class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wide text-slate-500">TAHUN</label>
                <select id="tahun" name="tahun" onchange="this.form.submit()" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 sm:w-auto xl:w-[100px]">
                    @for($y = max((int) date('Y') + 2, $tahun); $y >= date('Y') - 12; $y--)
                        <option value="{{ $y }}" @selected($tahun === $y)>YEAR {{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex min-w-0 basis-[calc(50%-0.25rem)] flex-col gap-1 sm:basis-auto xl:flex-row xl:items-center xl:gap-1.5">
                <label for="qc2-shop" class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wide text-slate-500">SHOP</label>
                <select id="qc2-shop" name="shop" onchange="this.form.submit()" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 sm:w-auto xl:w-[130px]">
                    <option value="">Semua Shop</option>
                    @foreach($shops as $shopOption)
                        <option value="{{ $shopOption }}" @selected($shop === $shopOption)>{{ $shopOption }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex min-w-0 basis-full flex-col gap-1 sm:basis-auto xl:flex-row xl:items-center xl:gap-1.5">
                <label for="qc2-tool-name" class="whitespace-nowrap text-[10px] font-semibold uppercase tracking-wide text-slate-500">TOOL NAME</label>
                <select id="qc2-tool-name" name="tool_name" class="w-full min-w-0 rounded-md border-slate-300 bg-white py-1.5 pl-2 pr-8 text-xs focus:border-blue-500 focus:ring-blue-500 sm:w-auto xl:w-[190px]">
                    <option value="">Semua Tool</option>
                    @foreach($toolNameList as $toolOption)
                        <option value="{{ $toolOption }}" @selected($toolName === $toolOption)>{{ $toolOption }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="inline-flex w-full basis-full items-center justify-center whitespace-nowrap rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-700 sm:w-auto sm:basis-auto xl:py-1.5">Cari</button>
        </form>

        <!-- KANAN: Total dan Tombol Aksi -->
        <div class="flex flex-wrap items-center justify-between gap-2 xl:justify-end">
            <p class="whitespace-nowrap text-xs text-slate-500">Halaman ini {{ $schedules->count() }} jadwal QC2 (maks. 100)</p>

            @if(Auth::check() && Auth::user()->role === 'admin')
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <!-- Tombol Import -->
                    <a href="{{ route('verifikasi.qc2.import.form', ['tahun' => $tahun, 'company' => $company]) }}" class="inline-flex items-center gap-1 whitespace-nowrap rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-gray-700 transition hover:bg-gray-50 sm:px-3 sm:py-2 sm:text-sm">@include('components.icons.import') Import</a>

                    <!-- Tombol Export Dropdown -->
                    <div class="relative inline-block" id="exportDropdownVerif">
                        <button type="button" onclick="toggleExportVerif()" class="inline-flex items-center gap-1 whitespace-nowrap rounded-md bg-slate-800 px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-slate-900 sm:px-3 sm:py-2 sm:text-sm">
                            @include('components.icons.export') Export
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div id="exportMenuVerif" class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50 overflow-hidden">
                            <a href="{{ route('verifikasi.qc2.export.excel', ['tahun' => $tahun, 'company' => $company]) }}" class="flex items-center gap-2.5 border-b border-gray-100 px-3 py-2.5 text-xs text-gray-700 transition hover:bg-blue-50">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 shrink-0"><path fill="#1D6F42" d="M14.5 2H5a1 1 0 0 0-1 1v18a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7.5L14.5 2z"/><path fill="#FFFFFF" d="M14 2v6h6L14 2z" opacity=".3"/><text x="12" y="17" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-weight="bold" font-size="9">X</text></svg>
                                <span class="font-medium">Export Excel</span>
                            </a>
                            <a href="{{ route('verifikasi.qc2.export.pdf', ['tahun' => $tahun, 'company' => $company]) }}" class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 transition hover:bg-red-50">
                                <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded bg-red-600 text-[8px] font-bold text-white">PDF</span>
                                <span class="font-medium">Export PDF</span>
                            </a>
                        </div>
                    </div>

                    <!-- Tombol Tambah -->
                    <a href="{{ route('verifikasi.qc2.create', ['tahun' => $tahun, 'company' => $company]) }}" class="inline-flex items-center gap-1 whitespace-nowrap rounded-md bg-black px-2.5 py-1.5 text-[11px] font-semibold text-white transition hover:bg-gray-800 sm:px-3 sm:py-2 sm:text-sm"><span>+</span> Tambah</a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="mb-2 flex items-center justify-between text-xs text-slate-500">
    <span>{{ $schedules->count() }} alat pada halaman ini (QC2)</span>
    <span class="md:hidden">Geser tabel ke samping →</span>
</div>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto table-sticky-scroll table-fill-screen">
        <table class="period-grid data-grid w-full min-w-[1200px] table-auto border-collapse text-xs text-slate-700">
            <thead class="bg-slate-800 text-white">
                <tr class="text-center uppercase tracking-wide">
                    <th rowspan="2" class="border px-3 py-3 align-middle">No</th>
                    <th rowspan="2" class="border px-3 py-3 text-left align-middle">Tool Name</th>
                    <th rowspan="2" class="border px-3 py-3 align-middle">Type</th>
                    <th rowspan="2" class="border px-3 py-3 align-middle">Specification</th>
                    <th rowspan="2" class="border px-3 py-3 align-middle">Number Seri</th>
                    <th rowspan="2" class="border px-3 py-3 align-middle">Number Registration</th>
                    <th rowspan="2" class="border px-3 py-3 align-middle">Shop</th>

                    <th colspan="3" class="border px-3 py-2 text-center">Year {{ $tahun }}</th>

                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <th rowspan="2" class="border px-3 py-3 align-middle">Aksi</th>
                    @endif
                </tr>
                <tr class="text-center uppercase text-[10px]">
                    <th class="border px-3 py-2">Verification Date</th>
                    <th class="border px-3 py-2">Next Verification Date</th>
                    <th class="border px-3 py-2">JGM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $index => $schedule)
                    <tr class="{{ $index % 2 ? 'bg-slate-50' : 'bg-white' }} hover:bg-blue-50/50">
                        <td class="border px-3 py-3 text-center">{{ ($schedules->firstItem() ?? 1) + $index }}</td>
                        <td class="border px-3 py-3 font-semibold text-slate-800">{{ $schedule->tool_name ?: '-' }}</td>
                        <td class="border px-3 py-3 text-center">{{ $schedule->tool_type ?: '-' }}</td>
                        <td class="border px-3 py-3">{{ $schedule->specification ?: '-' }}</td>
                        <td class="border px-3 py-3 text-center">{{ $schedule->serial_number ?: '-' }}</td>
                        <td class="border px-3 py-3 text-center">{{ $schedule->registration_number ?: '-' }}</td>
                        <td class="border px-3 py-3 text-center">{{ $schedule->shop ?: '-' }}</td>

                        <td class="border px-3 py-3 text-center">{{ $schedule->period1_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border px-3 py-3 text-center">{{ $schedule->period1_next_verification_date?->format('d M Y') ?? '-' }}</td>
                        <td class="border px-3 py-3 text-center">{{ $schedule->period1_jgm ?: '-' }}</td>

                        @if(Auth::check() && Auth::user()->role === 'admin')
                            <td class="border px-3 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('verifikasi.qc2.edit', $schedule) }}" class="rounded bg-blue-600 px-2 py-1 font-semibold text-white hover:bg-blue-700">Edit</a>
                                    <form method="POST" action="{{ route('verifikasi.qc2.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal QC2 ini?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded bg-rose-600 px-2 py-1 font-semibold text-white hover:bg-rose-700">Hapus</button>
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

<div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
    {{ $schedules->links() }}
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