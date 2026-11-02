<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0070f2">
    <title>{{ config('app.name', 'QC Tools') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50">

    <header class="fixed inset-x-0 top-0 z-10 h-16 border-b border-slate-200 bg-white">
        <div class="mx-auto flex h-full max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('images/hamatetsu_indonesia_logo.jpg') }}"
                     alt="Logo PT Hamatetsu Indonesia"
                     class="h-10 w-10 shrink-0 object-contain">
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold leading-tight text-slate-800">PT. Hamatetsu Indonesia</span>
                    <span class="hidden truncate text-[10px] font-medium text-slate-500 sm:block">QC Measurement Tools</span>
                </span>
            </a>
        </div>
    </header>

    <main class="flex min-h-screen items-center justify-center px-4 pb-8 pt-24 sm:px-6">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">
                    QC Measurement Tools
                </h1>
                <p class="mt-1 text-xs text-slate-600 sm:text-sm">
                    PT. Hamatetsu Indonesia
                </p>
            </div>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                {{ $slot }}
            </section>

            <div class="mt-5 text-center">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 transition hover:text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0 7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Beranda</span>
                </a>
            </div>

            <p class="mt-4 text-center text-[10px] text-slate-500">
                © {{ date('Y') }} PT. Hamatetsu Indonesia
            </p>
        </div>
    </main>

</body>
</html>