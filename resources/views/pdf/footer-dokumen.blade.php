<table style="width:100%; border-collapse:collapse; margin-top:18px; page-break-inside:avoid;">
    <tr>
        <td style="width:33.33%; border:1px solid #333; height:22px; text-align:center; font-weight:bold; font-size:9px;">APPROVED</td>
        <td style="width:33.33%; border:1px solid #333; height:22px; text-align:center; font-weight:bold; font-size:9px;">CHECKED</td>
        <td style="width:33.33%; border:1px solid #333; height:22px; text-align:center; font-weight:bold; font-size:9px;">ISSUED</td>
    </tr>
    <tr>
        <td style="border:1px solid #333; height:55px;">&nbsp;</td>
        <td style="border:1px solid #333; height:55px;">&nbsp;</td>
        <td style="border:1px solid #333; height:55px;">&nbsp;</td>
    </tr>
    <tr>
        <td style="border:none; padding:5px 0; font-size:8px;">&nbsp;</td>
        <td style="border:none; padding:5px 0; font-size:8px;">&nbsp;</td>
        <td style="border:none; padding:5px 0; font-size:8px; text-align:right;">Issued date: {{ now()->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td colspan="3" style="border:none; padding-top:5px; font-size:8px; color:#666; text-align:right;">
            Nomor ekspor: <strong>{{ $nomorDokumen ?? '-' }}</strong> · Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ Auth::user()->name ?? 'System' }}
        </td>
    </tr>
</table>
