<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0f172a">
    <title>Selamat Datang - QC Measurement Tools</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        html, body { overflow-x: hidden; }
        [x-cloak] { display: none !important; }

        body {
            background-color: #f8fafc;
            background-image:
                radial-gradient(circle at 15% 15%, rgba(16, 185, 129, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(15, 23, 42, 0.06) 0%, transparent 40%);
            min-height: 100vh;
        }

        /* ===== PANEL KIRI: BRANDING ===== */
        .brand-panel {
            background: linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%);
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.08) 0%, transparent 40%);
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            filter: blur(40px);
        }

        /* ===== CARD PILIHAN ===== */
        .choice-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            padding: 18px 20px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            width: 100%;
            text-align: left;
        }
        .choice-btn.admin {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            box-shadow: 0 8px 20px -8px rgba(16, 185, 129, 0.5);
        }
        .choice-btn.admin:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px -8px rgba(16, 185, 129, 0.6);
        }
        .choice-btn.admin .icon-wrap {
            background: rgba(255,255,255,0.2);
            color: #ffffff;
        }
        .choice-btn.admin:hover .icon-wrap {
            background: rgba(255,255,255,0.35);
            transform: scale(1.1) rotate(-5deg);
        }
        .choice-btn.admin .arrow { color: rgba(255,255,255,0.7); }
        .choice-btn.admin:hover .arrow { color: #ffffff; transform: translateX(4px); }

        .choice-btn.guest {
            background: #ffffff;
            border: 2px solid #d1fae5;
            box-shadow: 0 4px 12px -4px rgba(15, 23, 42, 0.08);
        }
        .choice-btn.guest:hover {
            transform: translateY(-3px);
            border-color: #10b981;
            box-shadow: 0 16px 32px -8px rgba(16, 185, 129, 0.25);
        }
        .choice-btn.guest .icon-wrap {
            background: #ecfdf5;
            color: #059669;
        }
        .choice-btn.guest:hover .icon-wrap {
            background: #059669;
            color: #ffffff;
            transform: scale(1.1) rotate(-5deg);
        }
        .choice-btn.guest .arrow { color: #cbd5e1; }
        .choice-btn.guest:hover .arrow { color: #059669; transform: translateX(4px); }

        .icon-wrap {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .arrow { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }

        .form-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #ffffff;
            color: #0f172a;
        }
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .form-input::placeholder { color: #94a3b8; }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .password-wrapper:focus-within {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
        .password-wrapper input {
            flex: 1;
            border: none;
            outline: none;
            padding: 12px 14px;
            font-size: 14px;
            background: transparent;
            color: #0f172a;
            border-radius: 12px 0 0 12px;
        }
        .password-wrapper input::placeholder { color: #94a3b8; }

        .toggle-password {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin-right: 4px;
            border-radius: 10px;
            color: #94a3b8;
            background: transparent;
            border: none;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            transition: all 0.15s ease;
        }
        .toggle-password:hover {
            color: #059669;
            background: #ecfdf5;
        }
        .toggle-password:active {
            color: #047857;
            background: #d1fae5;
            transform: scale(0.95);
        }
        .toggle-password svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8"
      x-data="{ view: 'choice' }">

    {{-- Card Utama Split Screen --}}
    <div class="w-full max-w-2xl fade-in">
        <div class="grid grid-cols-1 md:grid-cols-2 rounded-[32px] overflow-hidden shadow-2xl shadow-emerald-900/20 bg-white items-stretch">

            {{-- ========== PANEL KIRI: BRANDING ========== --}}
            {{-- Sudut kiri-atas dan kiri-bawah dibuat membulat --}}
            <div class="brand-panel relative text-white p-5 sm:p-6 md:p-8 flex flex-col justify-between min-h-[220px] md:min-h-[560px]
                        rounded-t-[32px] md:rounded-tr-none md:rounded-l-[32px]">

                <div class="relative z-10">
                    {{-- LOGO --}}
                    <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white shadow-xl shadow-emerald-900/30 ring-1 ring-white/40 p-3 mb-4 md:mb-5">
                        <img src="{{ asset('images/hamatetsu_indonesia_logo.jpg') }}"
                             alt="Logo PT Hamatetsu Indonesia"
                             class="w-full h-full object-contain">
                    </div>

                    <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold leading-tight">
                        PT. Hamatetsu<br>Indonesia
                    </h1>

                    <p class="text-sm text-emerald-50 mt-3 md:mt-4 leading-relaxed max-w-sm">
                        Sistem Manajemen Alat Ukur & Kalibrasi untuk Quality Control.
                    </p>
                </div>

                <div class="relative z-10 mt-6 md:mt-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-sm border border-white/20 text-[10px] font-semibold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        Quality Control — Measurement Tools
                    </div>
                </div>

            </div>

            {{-- ========== PANEL KANAN: FORM DINAMIS ========== --}}
            {{-- Sudut kanan-atas dan kanan-bawah dibuat membulat --}}
            <div class="p-6 sm:p-8 flex flex-col justify-center bg-white
                        rounded-b-[32px] md:rounded-bl-none md:rounded-r-[32px]">

                {{-- ====== STATE 1: PILIHAN AKSES ====== --}}
                <div x-show="view === 'choice'">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-extrabold text-slate-900">
                            Selamat Datang
                        </h2>
                        <p class="text-sm text-slate-500 mt-1.5">
                            Silakan pilih jenis akses untuk melanjutkan
                        </p>
                        <div class="flex items-center justify-center mt-4">
                            <span class="h-px w-10 bg-gradient-to-r from-transparent to-emerald-300"></span>
                            <span class="mx-2 w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span class="h-px w-10 bg-gradient-to-l from-transparent to-emerald-300"></span>
                        </div>
                    </div>

                    <div class="space-y-3">

                        {{-- CARD ADMIN --}}
                        <button type="button" @click="view = 'login'" class="choice-btn admin group">
                            <div class="icon-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 ml-4">
                                <p class="text-base font-bold text-white leading-tight">
                                    Login Admin
                                </p>
                                <p class="text-xs text-emerald-50 mt-0.5 leading-snug">
                                    Akses penuh manajemen data
                                </p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="arrow w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        {{-- CARD GUEST --}}
                        <a href="{{ route('guest.index') }}" class="choice-btn guest group">
                            <div class="icon-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0 ml-4">
                                <p class="text-base font-bold text-slate-900 leading-tight">
                                    Lihat Data
                                </p>
                                <p class="text-xs text-slate-500 mt-0.5 leading-snug">
                                    Mode read-only tanpa login
                                </p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="arrow w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-200">
                        <div class="flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Guest hanya dapat melihat data tanpa mengubah atau menghapus.</span>
                        </div>
                    </div>
                </div>

                {{-- ====== STATE 2: FORM LOGIN ====== --}}
                <div x-show="view === 'login'" x-cloak>

                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-extrabold text-slate-900">
                            Login Admin
                        </h2>
                        <p class="text-sm text-slate-500 mt-1.5">
                            Masukkan kredensial Anda
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 px-3 py-2 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
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
                                   class="form-input">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Password
                            </label>
                            <div class="password-wrapper">
                                <input id="password"
                                       type="password"
                                       name="password"
                                       required
                                       autocomplete="current-password"
                                       placeholder="••••••••">

                                <button type="button"
                                        class="toggle-password"
                                        onmousedown="showPassword()"
                                        onmouseup="hidePassword()"
                                        onmouseleave="hidePassword()"
                                        ontouchstart="showPassword()"
                                        ontouchend="hidePassword()"
                                        ontouchcancel="hidePassword()"
                                        aria-label="Tampilkan password">
                                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center mb-5">
                            <input id="remember_me"
                                   type="checkbox"
                                   name="remember"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <label for="remember_me" class="ml-2 text-xs text-slate-600">
                                Ingat saya
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-2.5 text-sm
                                       transition shadow-lg shadow-emerald-600/30 active:scale-[0.98]">
                            Log In
                        </button>
                    </form>

                    <div class="text-center mt-5">
                        <button type="button" @click="view = 'choice'"
                                class="inline-flex items-center gap-1.5 text-slate-500 hover:text-emerald-700 text-xs font-medium transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Pilihan Akses</span>
                        </button>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <p class="text-[10px] text-slate-400 tracking-wide">
                        © {{ date('Y') }} PT. HAMATETSU INDONESIA
                    </p>
                </div>

            </div>

        </div>
    </div>

    <script>
        function showPassword() {
            var input = document.getElementById('password');
            var eyeClosed = document.getElementById('eyeClosed');
            var eyeOpen = document.getElementById('eyeOpen');
            if (input) input.type = 'text';
            if (eyeClosed) eyeClosed.classList.add('hidden');
            if (eyeOpen) eyeOpen.classList.remove('hidden');
        }
        function hidePassword() {
            var input = document.getElementById('password');
            var eyeClosed = document.getElementById('eyeClosed');
            var eyeOpen = document.getElementById('eyeOpen');
            if (input) input.type = 'password';
            if (eyeClosed) eyeClosed.classList.remove('hidden');
            if (eyeOpen) eyeOpen.classList.add('hidden');
        }
    </script>

</body>
</html>