<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();
        return view('settings.index', compact('users'));
    }

    public function createUser()
    {
        return view('settings.create');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // ===== LOG ACTIVITY =====
        // Jangan log password!
        $dataLog = $user->toArray();
        unset($dataLog['password']);

        $this->logCreate(
            'user',
            $user->id,
            'Tambah User: ' . $user->name . ' (' . $user->email . ') - Role: ' . strtoupper($user->role),
            $dataLog
        );

        return redirect()->route('settings.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function editUser(User $user)
    {
        return view('settings.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        // Simpan data lama SEBELUM update
        $dataLama = $user->toArray();
        unset($dataLama['password']); // Jangan log password

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // ===== LOG ACTIVITY =====
        $dataBaru = $user->fresh()->toArray();
        unset($dataBaru['password']);

        $this->logUpdate(
            'user',
            $user->id,
            'Edit User: ' . $user->name . ' (' . $user->email . ')',
            $dataLama,
            $dataBaru
        );

        return redirect()->route('settings.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroyUser(User $user)
    {
        $currentUser = Auth::user();

        if ($currentUser && $currentUser->id === $user->id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri!');
        }

        // Simpan data SEBELUM hapus
        $dataLama = $user->toArray();
        unset($dataLama['password']);

        $userId = $user->id;
        $keterangan = 'Hapus User: ' . $user->name . ' (' . $user->email . ')';

        $user->delete();

        // ===== LOG ACTIVITY =====
        $this->logDelete('user', $userId, $keterangan, $dataLama);

        return redirect()->route('settings.index')->with('success', 'User berhasil dihapus!');
    }
}