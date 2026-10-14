@extends('layouts.app')

@section('content')

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 rounded mb-3 px-4 py-3 text-sm">
    {{ session('error') }}
</div>
@endif

<div class="bg-white rounded-lg shadow-sm mb-3 md:mb-4 px-4 py-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-base font-bold text-gray-800">Settings</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola user & hak akses aplikasi</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('settings.notifikasi') }}"
               class="bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 transition inline-flex items-center gap-1 whitespace-nowrap
                      px-3 py-2 text-xs sm:text-sm">
                ⚙️ Pengaturan Notifikasi
            </a>

            <a href="{{ route('settings.user.create') }}"
               class="bg-black text-white rounded font-semibold hover:bg-gray-800 transition inline-flex items-center gap-1 whitespace-nowrap
                      px-3 py-2 text-xs sm:text-sm">
                <span>+</span> Tambah User
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-800 text-white uppercase">
                <tr>
                    <th class="px-4 py-3 text-[11px] md:text-xs">No</th>
                    <th class="px-4 py-3 text-[11px] md:text-xs">Nama</th>
                    <th class="px-4 py-3 text-[11px] md:text-xs">Email</th>
                    <th class="px-4 py-3 text-[11px] md:text-xs text-center">Role</th>
                    <th class="px-4 py-3 text-[11px] md:text-xs">Terdaftar</th>
                    <th class="px-4 py-3 text-[11px] md:text-xs text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                <tr class="border-b hover:bg-blue-50">
                    <td class="px-4 py-3 font-semibold">{{ $index + 1 }}</td>
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($user->role === 'admin')
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[10px] font-semibold uppercase">Admin</span>
                        @else
                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[10px] font-semibold uppercase">User</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('settings.user.edit', $user->id) }}"
                               class="bg-yellow-400 text-white rounded hover:bg-yellow-500 transition px-2 py-1 text-[10px]">Edit</a>

                            @if(Auth::id() !== $user->id)
                            <form action="{{ route('settings.user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white rounded hover:bg-red-600 transition px-2 py-1 text-[10px]">Hapus</button>
                            </form>
                            @else
                            <span class="bg-gray-200 text-gray-500 px-2 py-1 text-[10px] rounded">Anda</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500 text-sm">Belum ada user.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection