<x-guest-layout>

    <div class="text-center mb-6">
        <h2 class="text-lg sm:text-xl font-bold text-slate-800">
            🔐 Login Admin
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Masukkan kredensial Anda untuk melanjutkan
        </p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-lg text-xs sm:text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                Email
            </label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username"
                   placeholder="admin@hamatetsu.com"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                          transition">
            @error('email')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                Password
            </label>
            <input id="password"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                          transition">
            @error('password')
                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mb-5">
            <input id="remember_me"
                   type="checkbox"
                   name="remember"
                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <label for="remember_me" class="ml-2 text-xs sm:text-sm text-slate-600">
                Ingat saya
            </label>
        </div>

        <!-- Tombol Login -->
        <button type="submit"
                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-lg py-2.5 text-sm
                       transition shadow-lg shadow-slate-900/20 active:scale-[0.98]">
            Log In
        </button>

    </form>

</x-guest-layout>