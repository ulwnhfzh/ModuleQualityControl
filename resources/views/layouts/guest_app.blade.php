<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0f172a">
    <title>QC Measurement Tools - Guest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        .table-scroll::-webkit-scrollbar { height: 8px; }
        .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .table-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
        .table-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }

        html, body { overflow-x: hidden; }
    </style>
</head>
<body class="bg-slate-50 antialiased">

    <header class="bg-white border-b border-slate-200 fixed top-0 left-0 right-0 z-50 h-14 shadow-sm">
        <div class="h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">

            <div class="flex items-center gap-2.5 min-w-0">
                <div class="shrink-0 flex items-center justify-center w-9 h-9 rounded-lg overflow-hidden">
                    <img src="{{ asset('images/hamatetsu_indonesia_logo.jpg') }}"
                         alt="Logo PT Hamatetsu Indonesia"
                         class="w-full h-full object-contain">
                </div>
                <div class="min-w-0">
                    <h1 class="font-bold text-slate-800 leading-tight truncate text-sm">
                        PT. Hamatetsu Indonesia
                    </h1>
                    <p class="text-slate-500 truncate text-[10px] font-medium">
                        QC Measurement Tools
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="hidden sm:flex items-center gap-2">
                    <div class="text-right">
                        <p class="text-xs font-semibold text-slate-800 leading-tight">Guest</p>
                        <p class="text-[10px] text-amber-600 leading-tight font-medium">Read Only</p>
                    </div>
                    <div class="bg-amber-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm shadow-sm">
                        👁
                    </div>
                </div>

                <a href="{{ route('welcome') }}"
                   class="bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold transition inline-flex items-center gap-1.5
                          px-3 py-2 text-xs border border-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </header>

    <main class="pt-14 min-h-screen">
        <div class="p-4 sm:p-5 md:p-6 max-w-[1600px] mx-auto">
            @yield('content')
        </div>
    </main>

</body>
</html>