<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     * Karena login sudah ada di welcome, redirect ke welcome.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('welcome');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // ===== LOG ACTIVITY: LOGIN =====
        $user = Auth::user();
        if ($user) {
            ActivityLog::create([
                'user_id'    => $user->id,
                'user_name'  => $user->name,
                'aksi'       => 'LOGIN',
                'jenis_data' => 'user',
                'data_id'    => $user->id,
                'keterangan' => 'Login berhasil - ' . $user->name . ' (' . $user->email . ')',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        // Redirect ke dashboard.index setelah login berhasil
        return redirect()->intended(route('dashboard.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // ===== LOG ACTIVITY: LOGOUT =====
        // Log HARUS sebelum logout, karena setelah logout Auth::user() = null
        $user = Auth::user();
        if ($user) {
            ActivityLog::create([
                'user_id'    => $user->id,
                'user_name'  => $user->name,
                'aksi'       => 'LOGOUT',
                'jenis_data' => 'user',
                'data_id'    => $user->id,
                'keterangan' => 'Logout - ' . $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect ke landing page (welcome) setelah logout
        return redirect()->route('welcome');
    }
}