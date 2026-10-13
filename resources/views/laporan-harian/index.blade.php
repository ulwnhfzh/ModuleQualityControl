@extends('layouts.app')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('error') }}
</div>
@endif

{{-- ===== HEADER ===== --}}
<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-4 py-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-base font-bold text-gray-800">📅 Laporan Harian QC</h1>
            <p class="text-xs text-gray-500 mt-0.5">Ringkasan aktivitas harian QC Measurement Tools</p>
        </div>

        <form method="GET" action="{{ route('laporan.harian.index') }}" class="flex items-center gap-2 flex-wrap">
            <label class="text-[10px] sm:text-xs font-semibold text-gray-500 whitespace-nowrap">TANGGAL</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                   class="border border-gray-300 rounded text-xs sm:text-sm bg-white px-2 py-1.5 sm:px-3 sm:py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                    class="bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm">
                🔍 Tampilkan
            </button>
            <a href="{{ route('laporan.harian.index') }}"
               class="bg-gray-200 text-gray-700 rounded font-semibold hover:bg-gray-300 transition px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm">
                Hari Ini
            </a>
        </form>
    </div>
</div>

{{-- ===== 4 KARTU RINGKASAN ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-4">

    {{-- Kartu 1: Total Aktivitas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">Total Aktivitas</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalAktivitas }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Aktivitas hari ini</p>
            </div>
            <div class="bg-blue-100 text-blue-600 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Kartu 2: User Aktif --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">User Aktif</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $userAktif }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">User login hari ini</p>
            </div>
            <div class="bg-emerald-100 text-emerald-600 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Kartu 3: Verifikasi --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">Verifikasi</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $verifikasiHariIni }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Input/update hari ini</p>
            </div>
            <div class="bg-purple-100 text-purple-600 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Kartu 4: Kalibrasi --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">Kalibrasi</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $kalibrasiHariIni }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Input/update hari ini</p>
            </div>
            <div class="bg-amber-100 text-amber-600 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- ===== BREAKDOWN: Per Jenis & Per Aksi ===== --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-4">

    {{-- Aktivitas per Jenis --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-800 text-sm">📊 Aktivitas per Jenis Data</h3>
        </div>
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 font-semibold">Jenis</th>
                    <th class="px-4 py-2 font-semibold text-center">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitasPerJenis as $item)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">
                        @php
                            $labels = [
                                'tools' => 'Daftar Induk',
                                'verifikasi' => 'Verifikasi',
                                'internal' => 'Kalibrasi Internal',
                                'eksternal' => 'Eksternal Small',
                                'mesin' => 'Eksternal Mesin',
                                'user' => 'User Management',
                                'laporan' => 'Laporan',
                            ];
                        @endphp
                        {{ $labels[$item->jenis_data] ?? $item->jenis_data ?? '-' }}
                    </td>
                    <td class="px-4 py-2 text-center font-semibold">{{ $item->jumlah }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-center py-4 text-gray-400 text-xs">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Aktivitas per Aksi --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-800 text-sm">⚡ Aktivitas per Jenis Aksi</h3>
        </div>
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase">
                <tr>
                    <th class="px-4 py-2 font-semibold">Aksi</th>
                    <th class="px-4 py-2 font-semibold text-center">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitasPerAksi as $item)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">
                        @php
                            $aksiLabels = [
                                'CREATE' => 'Tambah',
                                'UPDATE' => 'Edit',
                                'DELETE' => 'Hapus',
                                'LOGIN' => 'Login',
                                'LOGOUT' => 'Logout',
                                'EXPORT' => 'Export',
                            ];
                        @endphp
                        <span class="font-semibold">{{ $item->aksi }}</span>
                        <span class="text-gray-500 text-[10px]">({{ $aksiLabels[$item->aksi] ?? '-' }})</span>
                    </td>
                    <td class="px-4 py-2 text-center font-semibold">{{ $item->jumlah }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-center py-4 text-gray-400 text-xs">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== TABEL LOG AKTIVITAS HARI INI ===== --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-sm">📋 Log Aktivitas Hari Ini</h3>
        <span class="text-[10px] text-gray-500">{{ $tanggalCarbon->format('d F Y') }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-800 text-white uppercase">
                <tr>
                    <th class="px-3 py-2.5 text-[10px]">No</th>
                    <th class="px-3 py-2.5 text-[10px]">Waktu</th>
                    <th class="px-3 py-2.5 text-[10px]">User</th>
                    <th class="px-3 py-2.5 text-[10px] text-center">Aksi</th>
                    <th class="px-3 py-2.5 text-[10px]">Jenis</th>
                    <th class="px-3 py-2.5 text-[10px]">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                <tr class="border-b hover:bg-blue-50">
                    <td class="px-3 py-2 font-semibold">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 text-gray-600 whitespace-nowrap">{{ $log->created_at->format('H:i:s') }}</td>
                    <td class="px-3 py-2 font-medium">{{ $log->user_name ?? '-' }}</td>
                    <td class="px-3 py-2 text-center">
                        @php
                            $badgeColors = [
                                'CREATE' => 'bg-green-100 text-green-700',
                                'UPDATE' => 'bg-amber-100 text-amber-700',
                                'DELETE' => 'bg-red-100 text-red-700',
                                'LOGIN' => 'bg-blue-100 text-blue-700',
                                'LOGOUT' => 'bg-gray-100 text-gray-700',
                                'EXPORT' => 'bg-purple-100 text-purple-700',
                            ];
                            $color = $badgeColors[$log->aksi] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="{{ $color }} px-2 py-0.5 rounded text-[9px] font-semibold uppercase">{{ $log->aksi }}</span>
                    </td>
                    <td class="px-3 py-2">{{ $log->label_jenis ?? '-' }}</td>
                    <td class="px-3 py-2 text-gray-700">{{ $log->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500 text-sm">Belum ada aktivitas pada tanggal ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== TABEL ALAT JATUH TEMPO ===== --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-sm">⏰ Alat Jatuh Tempo Kalibrasi (30 Hari ke Depan)</h3>
        <span class="text-[10px] text-gray-500">Per {{ $tanggalCarbon->format('d F Y') }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-amber-600 text-white uppercase">
                <tr>
                    <th class="px-3 py-2.5 text-[10px]">No</th>
                    <th class="px-3 py-2.5 text-[10px]">Tool / Machine</th>
                    <th class="px-3 py-2.5 text-[10px]">Machine Model</th>
                    <th class="px-3 py-2.5 text-[10px]">Maker</th>
                    <th class="px-3 py-2.5 text-[10px] text-center">Next Date</th>
                    <th class="px-3 py-2.5 text-[10px] text-center">Sisa Hari</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alatJatuhTempo as $index => $item)
                <tr class="border-b hover:bg-amber-50">
                    <td class="px-3 py-2 font-semibold">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 font-medium">{{ $item->tool_name }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $item->machine_model ?? '-' }}</td>
                    <td class="px-3 py-2 text-gray-600">{{ $item->maker ?? '-' }}</td>
                    <td class="px-3 py-2 text-center text-gray-600">
                        {{ $item->next_date ? \Carbon\Carbon::parse($item->next_date)->format('d/m/Y') : '-' }}
                    </td>
                    <td class="px-3 py-2 text-center">
                        @php $sisa = $item->sisa_hari; @endphp
                        @if($sisa < 0)
                            <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                TERLAMBAT {{ abs($sisa) }} hari
                            </span>
                        @elseif($sisa <= 7)
                            <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                {{ $sisa }} hari lagi
                            </span>
                        @else
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                {{ $sisa }} hari lagi
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500 text-sm">Tidak ada alat yang jatuh tempo dalam 30 hari ke depan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== TOMBOL CETAK ===== --}}
<div class="bg-white rounded-lg shadow-sm p-4">
    <p class="text-xs text-gray-500 mb-3">Cetak laporan harian ini:</p>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('laporan.harian.pdf', ['tanggal' => $tanggal]) }}"
           style="background-color:#dc2626; color:#ffffff;"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-lg hover:opacity-90 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>📄 Cetak PDF</span>
        </a>
        <a href="{{ route('laporan.harian.excel', ['tanggal' => $tanggal]) }}"
           style="background-color:#16a34a; color:#ffffff;"
           class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-lg hover:opacity-90 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
            <span>📊 Cetak Excel</span>
        </a>
    </div>
</div>

@endsection