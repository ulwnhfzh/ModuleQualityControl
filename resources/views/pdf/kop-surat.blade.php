{{--
    KOMPONEN KOP SURAT PDF
    ======================
    Komponen reusable untuk semua template PDF.

    Cara pakai:
    @include('pdf.kop-surat', [
        'judul' => 'DAFTAR INDUK ALAT UKUR',
        'nomorDokumen' => $nomorDokumen,  // opsional
    ])

    Pastikan template PDF Anda punya style:
    <style>
        .kop-header { ... }
        .kop-logo { ... }
        .kop-judul { ... }
        .kop-nomor { ... }
    </style>
--}}

<div class="kop-header" style="border-bottom: 3px double #1e40af; padding-bottom: 10px; margin-bottom: 15px;">
    <table style="width: 100%; border: none;">
        <tr>
            {{-- KOLOM KIRI: LOGO --}}
            <td style="width: 12%; vertical-align: middle; border: none; padding: 0;">
                @php
                    $logoPath = public_path('images/hamatetsu_indonesia_logo.jpg');
                    $logoBase64 = file_exists($logoPath)
                        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
                        : null;
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo" style="width: 60px; height: 60px; object-fit: contain;">
                @endif
            </td>

            {{-- KOLOM TENGAH: INFO PT --}}
            <td style="width: 50%; vertical-align: middle; border: none; padding: 0 10px; text-align: center;">
                <div style="font-size: 16px; font-weight: bold; color: #1e40af; letter-spacing: 0.5px;">
                    PT. HAMATETSU INDONESIA
                </div>
                <div style="font-size: 10px; color: #333; margin-top: 2px;">
                    QC Measurement Tools
                </div>
                <div style="font-size: 9px; color: #666; margin-top: 3px;">
                    Jl. Industri No. 123, Jakarta
                </div>
                <div style="font-size: 9px; color: #666;">
                    Telp: (021) 1234567 | Email: info@hamatetsu.com
                </div>
                <div style="font-size: 9px; color: #666;">
                    www.hamatetsu.com
                </div>
            </td>

            {{-- KOLOM KANAN: KOSONG (untuk balance) --}}
            <td style="width: 12%; vertical-align: middle; border: none; padding: 0;"></td>
        </tr>
    </table>
</div>

{{-- JUDUL DOKUMEN --}}
@if(isset($judul))
<div style="text-align: center; margin-bottom: 15px;">
    <div style="font-size: 13px; font-weight: bold; color: #000; text-decoration: underline; letter-spacing: 0.5px;">
        {{ strtoupper($judul) }}
    </div>
    @if(isset($nomorDokumen) && $nomorDokumen)
        <div style="font-size: 10px; color: #333; margin-top: 3px;">
            Nomor: <strong>{{ $nomorDokumen }}</strong>
        </div>
    @endif
</div>
@endif