<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#0f172a">
    <title>StockMaster - QC Tools</title>
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

        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-radius: 8px;
            font-size: 14px; font-weight: 500;
            color: #94a3b8; transition: all 0.15s ease;
            width: 100%; text-align: left;
            background: transparent; border: none; cursor: pointer;
        }
        .sidebar-link:hover { background-color: rgba(255,255,255,0.05); color: #e2e8f0; }
        .sidebar-link.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff; font-weight: 600;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .sidebar-link .icon {
            width: 20px; height: 20px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .sidebar-link .icon svg { width: 18px; height: 18px; stroke-width: 2; }
        .sidebar-sublink {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 6px;
            font-size: 13px; font-weight: 500;
            color: #94a3b8; transition: all 0.15s ease;
            width: 100%; text-align: left;
        }
        .sidebar-sublink:hover { background-color: rgba(255,255,255,0.05); color: #e2e8f0; }
        .sidebar-sublink.active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #6ee7b7; font-weight: 600;
            border-left: 3px solid #10b981; padding-left: 9px;
        }
        .sidebar-sublink .dot {
            width: 6px; height: 6px; border-radius: 50%;
            background-color: #475569; flex-shrink: 0;
        }
        .sidebar-sublink:hover .dot { background-color: #94a3b8; }
        .sidebar-sublink.active .dot {
            background-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        .chevron { transition: transform 0.2s ease; }
        .sidebar-section {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: #475569; padding: 0 12px; margin: 12px 0 6px;
        }
        .table-header { background-color: #1a202c; color: white; }
        .table-row-even { background-color: #f8fafc; }
        .table-row-odd { background-color: #ffffff; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
        html, body { overflow-x: hidden; }

        details.user-menu > summary { list-style: none; cursor: pointer; }
        details.user-menu > summary::-webkit-details-marker { display: none; }
        details.user-menu[open] > summary .chevron-down { transform: rotate(180deg); }
        .chevron-down { transition: transform 0.15s ease; }

        .sidebar-desktop {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 256px;
            z-index: 40;
        }
        @media (max-width: 1023px) {
            .sidebar-desktop { display: none; }
        }

        .main-content { margin-left: 256px; }
        @media (max-width: 1023px) {
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body class="bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">

    <!-- HEADER -->
    <header class="bg-white border-b border-slate-200 fixed top-0 left-0 right-0 z-50 h-16">
        <div class="h-full px-2 sm:px-4 lg:px-6 flex items-center gap-2 sm:gap-3">

            <div class="flex items-center gap-2 min-w-0 flex-1">
                <button type="button" @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden bg-slate-100 hover:bg-slate-200 rounded-lg p-2 shrink-0 transition">
                    <svg x-show="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="sidebarOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

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

                {{-- ===== NOTIFIKASI BELL ===== --}}
                @auth
                    <a href="{{ route('notifikasi.index') }}" class="p-2 rounded-lg hover:bg-slate-100 transition relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600 group-hover:text-slate-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>

                        @if(isset($globalNotifikasi) && $globalNotifikasi > 0)
                            <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[9px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center ring-2 ring-white px-1">
                                {{ $globalNotifikasi > 99 ? '99+' : $globalNotifikasi }}
                            </span>
                        @endif
                    </a>
                @endauth

                @auth
                    <details class="user-menu relative">
                        <summary class="flex items-center gap-2 pl-2 sm:pl-3 border-l border-slate-200 hover:bg-slate-50 rounded-lg p-1 pr-2 transition">
                            <div class="hidden sm:block text-right">
                                <p class="text-xs font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-500 leading-tight">
                                    {{ Auth::user()->role === 'admin' ? 'Administrator' : 'User' }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-br from-emerald-400 to-emerald-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-[10px] shadow-sm ring-2 ring-white">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <svg class="chevron-down w-4 h-4 text-slate-400 hidden sm:block"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>

                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </details>
                @else
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
                @endauth
            </div>
        </div>
    </header>

    <!-- OVERLAY -->
    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
         class="fixed left-0 right-0 bottom-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
         style="top: 64px;"></div>

    <!-- SIDEBAR DESKTOP -->
    <aside class="sidebar-desktop hidden lg:flex flex-col bg-slate-900 border-r border-slate-800"
           x-data="{
               kalibrasiOpen: {{ request()->routeIs('internal.*') || request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'true' : 'false' }},
               eksternalOpen: {{ request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'true' : 'false' }}
           }">
        <div class="flex-1 overflow-y-auto sidebar-scroll py-3">
            <p class="sidebar-section">Main Menu</p>
            <nav class="px-3 space-y-1">

                <a href="{{ route('dashboard.index') }}" class="sidebar-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg></span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('tools.index') }}" class="sidebar-link {{ request()->routeIs('tools.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg></span>
                    <span>Daftar Induk</span>
                </a>

                <a href="{{ route('verifikasi.index') }}" class="sidebar-link {{ request()->routeIs('verifikasi.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                    <span>Jadwal Verifikasi Internal</span>
                </a>

                <button type="button" @click="kalibrasiOpen = !kalibrasiOpen"
                        class="sidebar-link {{ request()->routeIs('internal.*') || request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <span class="flex-1 text-left">Kalibrasi</span>
                    <svg class="chevron w-4 h-4" :class="kalibrasiOpen ? 'rotate-90' : ''"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div x-show="kalibrasiOpen" x-cloak class="pl-3 space-y-1 mt-1">
                    <button type="button" @click="eksternalOpen = !eksternalOpen"
                            class="sidebar-sublink {{ request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'active' : '' }}">
                        <span class="dot"></span>
                        <span class="flex-1 text-left">Eksternal</span>
                        <svg class="chevron w-3.5 h-3.5" :class="eksternalOpen ? 'rotate-90' : ''"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div x-show="eksternalOpen" x-cloak class="pl-4 space-y-0.5">
                        <a href="{{ route('eksternal.small') }}" class="sidebar-sublink {{ request()->routeIs('eksternal.small*') ? 'active' : '' }}">
                            <span class="dot"></span>
                            <span>Small Tool</span>
                        </a>

                        <a href="{{ route('mesin.index') }}" class="sidebar-sublink {{ request()->routeIs('mesin.*') ? 'active' : '' }}">
                            <span class="dot"></span>
                            <span>Mesin Alat Ukur</span>
                        </a>
                    </div>

                    <a href="{{ route('internal.index') }}" class="sidebar-sublink {{ request()->routeIs('internal.*') ? 'active' : '' }}">
                        <span class="dot"></span>
                        <span>Internal</span>
                    </a>
                </div>
            </nav>

            @if(Auth::check() && Auth::user()->role === 'admin')
            <p class="sidebar-section mt-6">Lainnya</p>
            <nav class="px-3 space-y-1">

                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></span>
                    <span>Reports</span>
                </a>

                <a href="{{ route('laporan.harian.index') }}" class="sidebar-link {{ request()->routeIs('laporan.harian.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                    <span>Laporan Harian</span>
                </a>

                <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <span>Settings</span>
                </a>
            </nav>
            @endif
        </div>

        <div class="p-4 border-t border-slate-800 shrink-0">
            <p class="text-[10px] text-slate-500 text-center leading-relaxed">
                Prototype • © 2026<br>
                <span class="text-slate-600">v1.0.0</span>
            </p>
        </div>
    </aside>

    <!-- SIDEBAR MOBILE -->
    <aside x-show="sidebarOpen" x-cloak
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="lg:hidden fixed left-0 z-50 bg-slate-900 w-64 shadow-2xl flex flex-col"
           style="top: 64px; bottom: 0;"
           x-data="{
               kalibrasiOpen: {{ request()->routeIs('internal.*') || request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'true' : 'false' }},
               eksternalOpen: {{ request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'true' : 'false' }}
           }">
        <div class="flex-1 overflow-y-auto sidebar-scroll py-3">
            <p class="sidebar-section">Main Menu</p>
            <nav class="px-3 space-y-1">

                <a href="{{ route('dashboard.index') }}" @click="sidebarOpen = false" class="sidebar-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg></span>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('tools.index') }}" @click="sidebarOpen = false" class="sidebar-link {{ request()->routeIs('tools.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg></span>
                    <span>Daftar Induk</span>
                </a>

                <a href="{{ route('verifikasi.index') }}" @click="sidebarOpen = false" class="sidebar-link {{ request()->routeIs('verifikasi.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                    <span>Jadwal Verifikasi Internal</span>
                </a>

                <button type="button" @click="kalibrasiOpen = !kalibrasiOpen"
                        class="sidebar-link {{ request()->routeIs('internal.*') || request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <span class="flex-1 text-left">Kalibrasi</span>
                    <svg class="chevron w-4 h-4" :class="kalibrasiOpen ? 'rotate-90' : ''"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div x-show="kalibrasiOpen" x-cloak class="pl-3 space-y-1 mt-1">
                    <button type="button" @click="eksternalOpen = !eksternalOpen"
                            class="sidebar-sublink {{ request()->routeIs('eksternal.small*') || request()->routeIs('mesin.*') ? 'active' : '' }}">
                        <span class="dot"></span>
                        <span class="flex-1 text-left">Eksternal</span>
                        <svg class="chevron w-3.5 h-3.5" :class="eksternalOpen ? 'rotate-90' : ''"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <div x-show="eksternalOpen" x-cloak class="pl-4 space-y-0.5">
                        <a href="{{ route('eksternal.small') }}" @click="sidebarOpen = false" class="sidebar-sublink {{ request()->routeIs('eksternal.small*') ? 'active' : '' }}">
                            <span class="dot"></span>
                            <span>Small Tool</span>
                        </a>

                        <a href="{{ route('mesin.index') }}" @click="sidebarOpen = false" class="sidebar-sublink {{ request()->routeIs('mesin.*') ? 'active' : '' }}">
                            <span class="dot"></span>
                            <span>Mesin Alat Ukur</span>
                        </a>
                    </div>

                    <a href="{{ route('internal.index') }}" @click="sidebarOpen = false" class="sidebar-sublink {{ request()->routeIs('internal.*') ? 'active' : '' }}">
                        <span class="dot"></span>
                        <span>Internal</span>
                    </a>
                </div>
            </nav>

            @if(Auth::check() && Auth::user()->role === 'admin')
            <p class="sidebar-section mt-6">Lainnya</p>
            <nav class="px-3 space-y-1">

                <a href="{{ route('reports.index') }}" @click="sidebarOpen = false" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></span>
                    <span>Reports</span>
                </a>

                <a href="{{ route('laporan.harian.index') }}" @click="sidebarOpen = false" class="sidebar-link {{ request()->routeIs('laporan.harian.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                    <span>Laporan Harian</span>
                </a>

                <a href="{{ route('settings.index') }}" @click="sidebarOpen = false" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></span>
                    <span>Settings</span>
                </a>
            </nav>
            @endif
        </div>

        <div class="p-4 border-t border-slate-800 shrink-0">
            <p class="text-[10px] text-slate-500 text-center leading-relaxed">
                Prototype • © 2026
            </p>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content pt-16 min-h-screen">
        <div class="p-4 sm:p-5 md:p-6">
            @yield('content')
        </div>
    </main>

</body>
</html>