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

{{-- HEADER --}}
<div class="feature-toolbar bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-4 py-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-base font-bold text-gray-800">Notifikasi</h1>
            <p class="text-xs text-gray-500 mt-0.5">Daftar alat yang memerlukan perhatian</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap text-[10px]">
            <span class="text-gray-500 font-semibold">Keterangan Warna:</span>
            <span class="bg-rose-100 text-rose-700 px-2 py-1 rounded font-semibold">Terlambat</span>
            <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded font-semibold">Jatuh Tempo</span>
            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded font-semibold">Masih Lama</span>
            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded font-semibold">Diproses</span>
            <span class="bg-green-100 text-green-700 px-2 py-1 rounded font-semibold">Selesai</span>
            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded font-semibold">Dibatalkan</span>
        </div>
    </div>
</div>

{{-- RINGKASAN --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-4">
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-rose-700 uppercase tracking-wide">Terlambat</p>
                <p class="text-3xl font-bold text-rose-700 mt-1">{{ $alatTerlambat->count() }}</p>
                <p class="text-[10px] text-rose-600 mt-0.5">Memerlukan tindakan segera</p>
            </div>
            <div class="bg-rose-200 text-rose-700 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-orange-700 uppercase tracking-wide">Jatuh Tempo</p>
                <p class="text-3xl font-bold text-orange-700 mt-1">{{ $alatJatuhTempo7->count() }}</p>
                <p class="text-[10px] text-orange-600 mt-0.5">Segera dipersiapkan</p>
            </div>
            <div class="bg-orange-200 text-orange-700 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Masih Lama</p>
                <p class="text-3xl font-bold text-blue-700 mt-1">{{ $alatJatuhTempo30->count() }}</p>
                <p class="text-[10px] text-blue-600 mt-0.5">Persiapan awal</p>
            </div>
            <div class="bg-blue-200 text-blue-700 rounded-lg p-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- TABEL NOTIFIKASI --}}
@php
    $tabelNotifikasi = [
        ['title' => 'Alat Terlambat', 'items' => $alatTerlambat, 'bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'border' => 'border-rose-200'],
        ['title' => 'Alat Akan Jatuh Tempo', 'items' => $alatJatuhTempo7, 'bg' => 'bg-orange-50', 'text' => 'text-orange-800', 'border' => 'border-orange-200'],
        ['title' => 'Alat Masih Lama', 'items' => $alatJatuhTempo30, 'bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
    ];
@endphp

@foreach($tabelNotifikasi as $tabel)
    @if($tabel['items']->count() > 0)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4 border {{ $tabel['border'] }}">
        <div class="px-4 py-3 border-b {{ $tabel['border'] }} {{ $tabel['bg'] }}">
            <h3 class="font-semibold {{ $tabel['text'] }} text-sm">{{ $tabel['title'] }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-800 text-white uppercase">
                    <tr>
                        <th class="px-3 py-2.5 text-[10px]">No</th>
                        <th class="px-3 py-2.5 text-[10px]">Nama Alat</th>
                        <th class="px-3 py-2.5 text-[10px]">Model</th>
                        <th class="px-3 py-2.5 text-[10px]">Merk</th>
                        <th class="px-3 py-2.5 text-[10px] text-center">Jatuh Tempo</th>
                        <th class="px-3 py-2.5 text-[10px] text-center">Sisa</th>
                        <th class="px-3 py-2.5 text-[10px] text-center">Status</th>
                        <th class="px-3 py-2.5 text-[10px] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tabel['items'] as $index => $item)
                    @php
                        // Tentukan warna baris berdasarkan status
                        $statusNotif = $item->status_notifikasi;
                        $rowColor = '';
                        if ($statusNotif) {
                            if ($statusNotif->status === 'diproses') $rowColor = 'bg-yellow-50';
                            elseif ($statusNotif->status === 'selesai') $rowColor = 'bg-green-50';
                            elseif ($statusNotif->status === 'dibatalkan') $rowColor = 'bg-gray-50';
                        }
                    @endphp
                    <tr class="border-b hover:bg-blue-50 {{ $rowColor }}">
                        <td class="px-3 py-2 font-semibold">{{ $index + 1 }}</td>
                        <td class="px-3 py-2 font-medium">{{ $item->tool_name }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $item->machine_model ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ $item->maker ?? '-' }}</td>
                        <td class="px-3 py-2 text-center text-gray-600">
                            {{ $item->next_date ? \Carbon\Carbon::parse($item->next_date)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            @php $sisa = (int) $item->sisa_hari; @endphp
                            @if($sisa < 0)
                                <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                    Terlambat {{ abs($sisa) }} hari
                                </span>
                            @elseif($sisa == 0)
                                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                    Hari ini
                                </span>
                            @elseif($sisa == 1)
                                <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                    Besok
                                </span>
                            @else
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-semibold">
                                    {{ $sisa }} hari lagi
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if($statusNotif)
                                @if($statusNotif->status === 'diproses')
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-[10px] font-semibold uppercase">
                                        Diproses
                                    </span>
                                    @if($statusNotif->catatan)
                                        <div class="text-[9px] text-yellow-600 mt-0.5">{{ $statusNotif->catatan }}</div>
                                    @endif
                                @elseif($statusNotif->status === 'selesai')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] font-semibold uppercase">
                                        Selesai
                                    </span>
                                    @if($statusNotif->catatan)
                                        <div class="text-[9px] text-green-600 mt-0.5">{{ $statusNotif->catatan }}</div>
                                    @endif
                                @elseif($statusNotif->status === 'dibatalkan')
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[10px] font-semibold uppercase">
                                        Dibatalkan
                                    </span>
                                    @if($statusNotif->catatan)
                                        <div class="text-[9px] text-gray-500 mt-0.5">{{ $statusNotif->catatan }}</div>
                                    @endif
                                @endif
                            @else
                                <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-[10px] font-semibold uppercase">
                                    Belum Diproses
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            <div class="flex justify-center gap-1">
                                @if(!$statusNotif || $statusNotif->status === 'dibatalkan')
                                    <button onclick="showProsesModal({{ $item->id }}, '{{ $item->tool_name }}')"
                                            class="bg-yellow-500 text-white rounded hover:bg-yellow-600 px-2 py-1 text-[10px] font-semibold transition">
                                        Proses
                                    </button>
                                @elseif($statusNotif->status === 'diproses')
                                    <button onclick="showSelesaiModal({{ $item->id }}, '{{ $item->tool_name }}')"
                                            class="bg-green-500 text-white rounded hover:bg-green-600 px-2 py-1 text-[10px] font-semibold transition">
                                        Selesai
                                    </button>
                                    <button onclick="showBatalModal({{ $item->id }}, '{{ $item->tool_name }}')"
                                            class="bg-gray-400 text-white rounded hover:bg-gray-500 px-2 py-1 text-[10px] font-semibold transition">
                                        Batal
                                    </button>
                                @elseif($statusNotif->status === 'selesai')
                                    <span class="text-green-600 font-semibold text-[10px]">Terverifikasi</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endforeach

{{-- EMPTY STATE --}}
@if($alatTerlambat->count() == 0 && $alatJatuhTempo7->count() == 0 && $alatJatuhTempo30->count() == 0)
<div class="bg-green-50 border border-green-200 rounded-xl p-12 text-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-green-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <h3 class="text-lg font-bold text-green-800">Seluruh Alat Terkalibrasi Sesuai Jadwal</h3>
    <p class="text-sm text-green-600 mt-1">Tidak terdapat alat yang memerlukan perhatian dalam periode mendatang.</p>
</div>
@endif

{{-- MODAL PROSES --}}
<div id="modalProses" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="font-bold text-gray-800 mb-2">Tandai Sedang Diproses</h3>
        <p class="text-xs text-gray-600 mb-4">Alat: <strong id="prosesAlatNama"></strong></p>
        <form id="formProses" method="POST">
            @csrf
            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan (opsional)</label>
            <textarea name="catatan" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Siap diambil vendor"></textarea>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-yellow-600">Simpan</button>
                <button type="button" onclick="closeModal('modalProses')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL SELESAI --}}
<div id="modalSelesai" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="font-bold text-gray-800 mb-2">Tandai Selesai</h3>
        <p class="text-xs text-gray-600 mb-4">Alat: <strong id="selesaiAlatNama"></strong></p>
        <form id="formSelesai" method="POST">
            @csrf
            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan (opsional)</label>
            <textarea name="catatan" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Sudah diverifikasi"></textarea>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-green-600">Simpan</button>
                <button type="button" onclick="closeModal('modalSelesai')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL BATAL --}}
<div id="modalBatal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="font-bold text-gray-800 mb-2">Batalkan Status</h3>
        <p class="text-xs text-gray-600 mb-4">Alat: <strong id="batalAlatNama"></strong></p>
        <form id="formBatal" method="POST">
            @csrf
            <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan (wajib)</label>
            <textarea name="catatan" rows="3" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Alat rusak, tidak jadi diverifikasi"></textarea>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-gray-600">Simpan</button>
                <button type="button" onclick="closeModal('modalBatal')" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function showProsesModal(alatId, alatNama) {
    document.getElementById('prosesAlatNama').textContent = alatNama;
    document.getElementById('formProses').action = '/notifikasi/' + alatId + '/diproses';
    document.getElementById('modalProses').classList.remove('hidden');
}

function showSelesaiModal(alatId, alatNama) {
    document.getElementById('selesaiAlatNama').textContent = alatNama;
    document.getElementById('formSelesai').action = '/notifikasi/' + alatId + '/selesai';
    document.getElementById('modalSelesai').classList.remove('hidden');
}

function showBatalModal(alatId, alatNama) {
    document.getElementById('batalAlatNama').textContent = alatNama;
    document.getElementById('formBatal').action = '/notifikasi/' + alatId + '/batal';
    document.getElementById('modalBatal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>

@endsection
