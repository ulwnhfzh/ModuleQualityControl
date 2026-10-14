# 📋 CHEAT SHEET - KODE DOKUMEN QC

## Kode Prefix Dokumen

| Fitur | Kode | Format | Contoh |
|---|---|---|---|
| Daftar Induk | DIN | QC/DIN/[TAHUN]/[URUT] | QC/DIN/2026/001 |
| Verifikasi | VFR | QC/VFR/[TAHUN]/[URUT] | QC/VFR/2026/001 |
| Kalibrasi Internal | INT | QC/INT/[TAHUN]/[URUT] | QC/INT/2026/001 |
| Eksternal Small | EKS | QC/EKS/[TAHUN]/[URUT] | QC/EKS/2026/001 |
| Eksternal Mesin | MSN | QC/MSN/[TAHUN]/[URUT] | QC/MSN/2026/001 |
| Laporan Harian | LAP | QC/LAP/[TAHUN]/[URUT] | QC/LAP/2026/001 |

## Aturan Nomor Dokumen

1. **Format**: QC/[KODE]/[TAHUN]/[NOMOR URUT]
2. **Nomor urut**: 3 digit (001, 002, 003, ...)
3. **Reset**: Setiap tahun baru, nomor urut otomatis reset
4. **Format counter**: disimpan di tabel `dokumen_counter`
5. **Audit trail**: Setiap cetak dokumen tercatat di `activity_logs`

## Aksi yang Tercatat (Audit Trail)

| Aksi | Kapan Tercatat |
|---|---|
| CREATE | Tambah data di 5 fitur + import |
| UPDATE | Edit data di 5 fitur + user |
| DELETE | Hapus data di 5 fitur + user |
| LOGIN | Login berhasil |
| LOGOUT | Logout |
| EXPORT | Download Excel/PDF |

## Kode Warna Audit Trail

| Aksi | Warna | Arti |
|---|---|---|
| CREATE | 🟢 Hijau | Tambah data baru |
| UPDATE | 🟡 Kuning | Edit data |
| DELETE | 🔴 Merah | Hapus data |
| LOGIN | 🔵 Biru | Login |
| LOGOUT | ⚫ Abu-abu | Logout |
| EXPORT | 🟣 Ungu | Export/cetak |