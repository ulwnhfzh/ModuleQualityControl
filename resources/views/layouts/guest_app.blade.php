<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0f172a">
    <title>StockMaster - QC Tools (Guest)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 16px 16px !important;
            padding-right: 36px !important;
        }
        select::-ms-expand { display: none; }

        .table-header { background-color: #1a202c; color: white; }
        .table-row-even { background-color: #f8fafc; }
        .table-row-odd { background-color: #ffffff; }
        html, body { overflow-x: hidden; }

        details.user-menu > summary { list-style: none; cursor: pointer; }
        details.user-menu > summary::-webkit-details-marker { display: none; }
        details.user-menu[open] > summary .chevron-down { transform: rotate(180deg); }
        .chevron-down { transition: transform 0.15s ease; }

        .tab-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: #64748b; background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease; white-space: nowrap;
            cursor: pointer;
        }
        .tab-btn:hover { background: #e2e8f0; color: #334155; }
        .tab-btn.active {
            background: #1e293b; color: #fff;
            border-color: #1e293b;
            box-shadow: 0 4px 12px rgba(15,23,42,0.25);
        }
        .tab-btn svg { width: 16px; height: 16px; stroke-width: 2; }
    </style>
</head>
<body class="bg-slate-50 antialiased">

    <!-- HEADER -->
    <header class="bg-white border-b border-slate-200 fixed top-0 left-0 right-0 z-50 h-16">
        <div class="h-full px-3 sm:px-6 lg:px-8 flex items-center gap-2 sm:gap-3">

            <div class="flex items-center gap-2 min-w-0 flex-1">
                {{-- ===== LOGO DARI FILE ===== --}}
                <div class="shrink-0 flex items-center justify-center w-9 h-9 rounded-lg overflow-hidden">
                    <img src="{{ asset('images/hamatetsu_indonesia_logo.jpg') }}"
                         alt="Logo PT Hamatetsu Indonesia"
                         class="w-full h-full object-contain">
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="font-bold text-slate-800 leading-tight truncate text-xs sm:text-sm">
                        PT. Hamatetsu Indonesia
                    </h1>
                    <p class="text-slate-500 truncate text-[10px] font-medium hidden sm:block">
                        QC Measurement Tools
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <button type="button" class="p-2 rounded-lg hover:bg-slate-100 transition relative group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600 group-hover:text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"></span>
                </button>

                <div class="flex items-center gap-2 pl-2 sm:pl-3 border-l border-slate-200">
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-semibold text-slate-800 leading-tight">Guest</p>
                        <p class="text-[10px] text-amber-600 leading-tight font-medium">Read Only</p>
                    </div>
                    <div class="bg-gradient-to-br from-amber-400 to-amber-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-white">
                        👁
                    </div>
                </div>

                <a href="{{ route('login') }}"
                   class="bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold transition inline-flex items-center gap-1.5 whitespace-nowrap
                          px-2.5 py-1.5 text-[11px] sm:px-3 sm:py-2 sm:text-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="hidden sm:inline">Login Admin</span>
                    <span class="sm:hidden">Login</span>
                </a>
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA (TANPA SIDEBAR) -->
    <main class="pt-16 min-h-screen">
        <div class="p-4 sm:p-5 md:p-6">
            @yield('content')
        </div>
    </main>

</body>
</html>