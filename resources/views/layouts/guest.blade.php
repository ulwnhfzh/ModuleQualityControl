<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>{{ config('app.name', 'QC Tools') }} - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        <!-- Logo Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3 mb-3">
                <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 text-white rounded-2xl w-14 h-14 flex items-center justify-center font-bold text-xl shadow-2xl shadow-emerald-500/40">
                    FC
                </div>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">
                QC Measurement Tools
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1">
                PT. Hamatetsu Indonesia
            </p>
        </div>

        <!-- Kartu Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
            {{ $slot }}
        </div>

        <!-- Tombol Kembali -->
        <div class="text-center mt-5">
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-1.5 text-slate-300 hover:text-white text-xs transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-400 text-[10px] mt-4">
            © {{ date('Y') }} PT. Hamatetsu Indonesia
        </p>

    </div>

</body>
</html>