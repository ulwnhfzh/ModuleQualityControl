@extends('layouts.app')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h1 class="text-lg font-bold text-gray-800 mb-4">Import Jadwal Verification</h1>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 rounded mb-4 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('verifikasi.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('components.company-select-field', ['selectedCompany' => old('company', $company)])
            <input type="hidden" name="mode" value="{{ $mode }}">
            <p id="nisso-cycle-note" class="mb-4 rounded border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-800 {{ $company === 'NISSO GIKEN' ? '' : 'hidden' }}">Import NISSO GIKEN memakai jadwal Bulanan, maksimal dua tanggal per alat dalam setahun dan jarak minimal enam bulan.</p>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun</label>
                <select name="tahun" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">File Excel/CSV</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4 text-xs text-blue-700">
                💡 Download template dulu supaya format sesuai:
                <a href="{{ route('verifikasi.template', ['mode' => $mode, 'company' => $company]) }}"
                   class="underline font-semibold">Download Template</a>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-blue-700">
                    Import
                </button>
                <a href="{{ route('verifikasi.index', ['mode' => $mode, 'company' => $company]) }}"
                   class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-semibold hover:bg-gray-300">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
const companyInput = document.querySelector('select[name="company"]');
const modeInput = document.querySelector('input[name="mode"]');
const nissoNote = document.getElementById('nisso-cycle-note');
companyInput?.addEventListener('change', function () {
    if (this.value === 'NISSO GIKEN') modeInput.value = 'harian';
    nissoNote?.classList.toggle('hidden', this.value !== 'NISSO GIKEN');
});
</script>
@endsection
