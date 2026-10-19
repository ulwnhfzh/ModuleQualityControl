@php
    $logoPath = public_path('images/hamatetsu_indonesia_logo.jpg');
    $logoBase64 = is_file($logoPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
@endphp

<table style="width:100%; border-collapse:collapse; border:1px solid #333; margin-bottom:12px;">
    <tr>
        <td style="width:18%; border:1px solid #333; text-align:center; vertical-align:middle; padding:8px;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo Hamatetsu" style="max-width:105px; max-height:55px;">
            @else
                <strong>HT</strong>
            @endif
        </td>
        <td style="width:52%; border:1px solid #333; text-align:center; vertical-align:middle; padding:8px;">
            <div style="font-size:11px; font-weight:bold;">PT. HAMATETSU INDONESIA</div>
            <div style="font-size:9px; margin-top:3px;">QC Measurement Tools</div>
            <div style="font-size:14px; font-weight:bold; margin-top:8px;">{{ strtoupper($judul ?? 'DOKUMEN QC') }}</div>
        </td>
        <td style="width:30%; border:1px solid #333; padding:0; font-size:8px;">
            <table style="width:100%; border-collapse:collapse;">
                <tr><td style="border:1px solid #777; padding:4px;">Halaman</td><td style="border:1px solid #777; padding:4px;">&nbsp;</td></tr>
                <tr><td style="border:1px solid #777; padding:4px;">Nomor Dokumen</td><td style="border:1px solid #777; padding:4px;"><strong>F-QC-01</strong></td></tr>
                <tr><td style="border:1px solid #777; padding:4px;">Tanggal Terbit</td><td style="border:1px solid #777; padding:4px;">10 Juli 2023</td></tr>
                <tr><td style="border:1px solid #777; padding:4px;">Revisi</td><td style="border:1px solid #777; padding:4px;">02</td></tr>
            </table>
        </td>
    </tr>
</table>
