{{--
    KOMPONEN FOOTER DOKUMEN PDF
    ===========================
    Komponen reusable untuk semua template PDF.

    Cara pakai:
    @include('pdf.footer-dokumen', [
        'nomorDokumen' => $nomorDokumen, // opsional
    ])
--}}

<div class="footer-dokumen" style="margin-top: 25px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 9px; color: #666;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; padding: 0; text-align: left;">
                @if(isset($nomorDokumen) && $nomorDokumen)
                    <div>Dokumen: <strong>{{ $nomorDokumen }}</strong></div>
                @endif
                <div>Dicetak oleh: <strong>{{ Auth::user()->name ?? 'System' }}</strong>
                    ({{ Auth::user()->role === 'admin' ? 'Administrator' : 'User' ?? '-' }})
                </div>
                <div>Pada: {{ now()->format('d F Y, H:i:s') }}</div>
            </td>
            <td style="border: none; padding: 0; text-align: right; vertical-align: bottom;">
                <div style="font-size: 8px; color: #999;">
                    Dokumen ini dicetak otomatis dari<br>
                    Sistem QC Measurement Tools<br>
                    PT. Hamatetsu Indonesia
                </div>
            </td>
        </tr>
    </table>
</div>