@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm mb-6 max-w-4xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Edit Jadwal Verification</h2>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('verifikasi.update', $verifikasi->id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('components.company-select-field', ['selectedCompany' => old('company', $verifikasi->company)])

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1">Nama Alat Ukur</label>
                <input type="text" name="tool_name" value="{{ $verifikasi->tool_name }}" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tahun</label>
                <input type="number" name="tahun" value="{{ $verifikasi->tahun }}" min="2000" max="2100" step="1" required class="w-full border border-gray-300 p-2 rounded focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
        </div>

        <h3 class="font-semibold text-gray-700 mb-3 border-b pb-2">Jumlah Verification per Bulan</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
            @php
                $bulan = [
                    'jan' => 'Januari', 'feb' => 'Februari', 'mar' => 'Maret', 'apr' => 'April',
                    'may' => 'Mei', 'jun' => 'Juni', 'jul' => 'Juli', 'aug' => 'Agustus',
                    'sep' => 'September', 'oct' => 'Oktober', 'nov' => 'November', 'dec' => 'Desember'
                ];
            @endphp

            @foreach($bulan as $name => $label)
            <div>
                <label class="block text-xs font-semibold mb-1 text-gray-600">{{ $label }}</label>
                <input type="number" name="{{ $name }}" value="{{ $verifikasi->$name }}" min="0" max="2147483647" step="1" inputmode="numeric" class="w-full border border-gray-300 p-2 rounded text-center focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            @endforeach
        </div>

        <label class="mb-6 flex cursor-pointer items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4">
            <input type="checkbox" name="sudah_diverifikasi" value="1" @checked(old('sudah_diverifikasi', $verifikasi->verified_at ? '1' : '')) class="mt-1 rounded border-blue-400 text-blue-600 focus:ring-blue-500">
            <span>
                <span class="block text-sm font-semibold text-blue-900">Konfirmasi sudah diverifikasi</span>
                <span class="mt-1 block text-xs text-blue-800">Pemeriksa: {{ $verifikasi->verified_by ?: 'belum dicatat' }}. Mencentang akan mencatat akun Anda; menghapus centang membatalkan konfirmasi.</span>
            </span>
        </label>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-semibold hover:bg-blue-700 transition">Update</button>
            <a href="{{ route('verifikasi.index') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
        </div>
    </form>
</div>
@endsection
