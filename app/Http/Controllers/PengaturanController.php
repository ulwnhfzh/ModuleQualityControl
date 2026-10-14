<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    use LogsActivity;

    public function notifikasi()
    {
        $pengaturan = [
            'warning_days_cepat'   => Pengaturan::get('warning_days_cepat', 7),
            'warning_days_standar' => Pengaturan::get('warning_days_standar', 30),
            'notif_dashboard'      => Pengaturan::get('notif_dashboard', 1),
            'notif_header'         => Pengaturan::get('notif_header', 1),
        ];

        return view('settings.notifikasi', compact('pengaturan'));
    }

    public function updateNotifikasi(Request $request)
    {
        $request->validate([
            'warning_days_cepat'   => 'required|integer|min:1|max:365',
            'warning_days_standar' => 'required|integer|min:1|max:365',
            'notif_dashboard'      => 'nullable|in:1',
            'notif_header'         => 'nullable|in:1',
        ]);

        $dataLama = [
            'warning_days_cepat'   => Pengaturan::get('warning_days_cepat'),
            'warning_days_standar' => Pengaturan::get('warning_days_standar'),
            'notif_dashboard'      => Pengaturan::get('notif_dashboard'),
            'notif_header'         => Pengaturan::get('notif_header'),
        ];

        // Validasi: warning standar harus > warning cepat
        if ($request->warning_days_standar <= $request->warning_days_cepat) {
            return back()->with('error', 'Warning standar harus lebih besar dari warning cepat!');
        }

        // Update
        Pengaturan::set('warning_days_cepat', $request->warning_days_cepat);
        Pengaturan::set('warning_days_standar', $request->warning_days_standar);
        Pengaturan::set('notif_dashboard', $request->has('notif_dashboard') ? '1' : '0');
        Pengaturan::set('notif_header', $request->has('notif_header') ? '1' : '0');

        $dataBaru = [
            'warning_days_cepat'   => $request->warning_days_cepat,
            'warning_days_standar' => $request->warning_days_standar,
            'notif_dashboard'      => $request->has('notif_dashboard') ? '1' : '0',
            'notif_header'         => $request->has('notif_header') ? '1' : '0',
        ];

        // Log
        $this->logActivity(
            'UPDATE',
            'pengaturan',
            null,
            'Update Pengaturan Notifikasi - Cepat: ' . $request->warning_days_cepat . ' hari, Standar: ' . $request->warning_days_standar . ' hari',
            $dataLama,
            $dataBaru
        );

        return back()->with('success', 'Pengaturan notifikasi berhasil diperbarui!');
    }
}