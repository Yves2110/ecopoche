@props([
    'title'        => 'EcoPoche',
    'pageTitle'    => "Vue d'ensemble",
    'pageSubtitle' => null,
    'monthSelector' => false,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false }" class="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'EcoPoche' }} — Gestion Budgétaire</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#F8FAFC] text-[#1F2937] font-sans">

{{-- Overlay mobile sidebar --}}
<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/40 z-30 lg:hidden"
    style="display:none;"
></div>

{{-- ===================== SIDEBAR ===================== --}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed left-0 top-0 h-screen w-60 bg-[#002452] z-40 flex flex-col shadow-xl transition-transform duration-200 ease-in-out"
>
    {{-- Logo --}}
    <div class="px-4 py-5 flex items-center gap-3 border-b border-white/10">
        <span class="material-symbols-outlined text-[#6ffbbe] text-2xl" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span>
        <div>
            <h1 class="font-headline text-base font-bold text-white leading-tight">EcoPoche</h1>
            <p class="text-[10px] text-white/50 font-medium tracking-widest uppercase">Gestion Budgétaire</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p class="text-[9px] font-bold uppercase tracking-widest text-white/30 px-3 mb-2">Principal</p>

        <a href="{{ route('dashboard') }}"
           class="sidebar-item {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">dashboard</span>
            <span>Tableau de bord</span>
        </a>

        <a href="{{ route('revenus.index') }}"
           class="sidebar-item {{ request()->routeIs('revenus.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">payments</span>
            <span>Revenus</span>
        </a>

        <a href="{{ route('depenses.index') }}"
           class="sidebar-item {{ request()->routeIs('depenses.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">receipt_long</span>
            <span>Dépenses</span>
        </a>

        <a href="#"
           class="sidebar-item {{ request()->routeIs('charges.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">category</span>
            <span>Charges fixes</span>
        </a>

        <a href="#"
           class="sidebar-item {{ request()->routeIs('epargne.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">savings</span>
            <span>Épargne</span>
        </a>

        <p class="text-[9px] font-bold uppercase tracking-widest text-white/30 px-3 mt-4 mb-2">Analyse</p>

        <a href="#"
           class="sidebar-item {{ request()->routeIs('rapports.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">bar_chart</span>
            <span>Rapports</span>
        </a>

        @if(auth()->user()?->role === 'super_admin' || auth()->user()?->role === 'admin')
        <p class="text-[9px] font-bold uppercase tracking-widest text-white/30 px-3 mt-4 mb-2">Administration</p>
        <a href="#"
           class="sidebar-item {{ request()->routeIs('admin.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
            <span>Administration</span>
        </a>
        @endif
    </nav>

    {{-- Bottom : actions --}}
    <div class="px-3 py-4 border-t border-white/10 space-y-1">
        <a href="#" class="sidebar-item">
            <span class="material-symbols-outlined text-xl">settings</span>
            <span>Paramètres</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-item w-full text-left">
                <span class="material-symbols-outlined text-xl">logout</span>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

{{-- ===================== MAIN ===================== --}}
<div class="lg:ml-60 min-h-screen flex flex-col">

    {{-- TOP HEADER --}}
    <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-[#E5E7EB] px-4 py-3 flex items-center justify-between">
        {{-- Left : burger (mobile) + titre page --}}
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                <span class="material-symbols-outlined text-[#002452]">menu</span>
            </button>
            <div>
                <h2 class="font-headline text-[#002452] font-bold text-base leading-tight">{{ $pageTitle ?? 'Vue d\'ensemble' }}</h2>
                @isset($pageSubtitle)
                <p class="text-xs text-[#6B7280]">{{ $pageSubtitle }}</p>
                @endisset
            </div>
        </div>

        {{-- Right : mois sélecteur + notifs + user --}}
        <div class="flex items-center gap-2">

            {{-- Sélecteur mois/année --}}
            @isset($monthSelector)
            <div class="hidden sm:flex items-center gap-1 bg-[#F8FAFC] border border-[#E5E7EB] rounded-lg px-3 py-1.5">
                <button class="text-[#002452] hover:text-[#10B981] transition-colors">
                    <span class="material-symbols-outlined text-base">chevron_left</span>
                </button>
                <span class="text-sm font-semibold text-[#1F2937] mx-1">{{ now()->translatedFormat('F Y') }}</span>
                <button class="text-[#002452] hover:text-[#10B981] transition-colors">
                    <span class="material-symbols-outlined text-base">chevron_right</span>
                </button>
            </div>
            @endisset

            {{-- Notifications --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="relative p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <span class="material-symbols-outlined text-[#6B7280] text-xl">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#EF4444] rounded-full"></span>
                </button>
            </div>

            {{-- User --}}
            <div class="flex items-center gap-2 pl-3 border-l border-[#E5E7EB] ml-1">
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-[#1F2937] leading-tight">{{ auth()->user()?->name ?? 'Utilisateur' }}</p>
                    <p class="text-[10px] text-[#6B7280] uppercase font-medium tracking-wide">
                        {{ match(auth()->user()?->role) {
                            'super_admin' => 'Super Admin',
                            'admin' => 'Administrateur',
                            default => 'Compte personnel'
                        } }}
                    </p>
                </div>
                <div class="w-8 h-8 rounded-full bg-[#002452] flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
            </div>
        </div>
    </header>

    {{-- CONTENU PRINCIPAL --}}
    <main class="flex-1 p-4 lg:p-6 max-w-[1280px] w-full mx-auto">
        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert-banner alert-green mb-4" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <span class="material-symbols-outlined text-[#065f46] text-xl" style="font-variation-settings:'FILL' 1;">check_circle</span>
            <p class="text-sm font-medium text-[#065f46]">{{ session('success') }}</p>
        </div>
        @endif
        @if(session('error'))
        <div class="alert-banner alert-red mb-4">
            <span class="material-symbols-outlined text-[#991b1b] text-xl" style="font-variation-settings:'FILL' 1;">error</span>
            <p class="text-sm font-medium text-[#991b1b]">{{ session('error') }}</p>
        </div>
        @endif

        {{ $slot }}
    </main>

    {{-- BOTTOM NAV (mobile) --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#E5E7EB] z-20 flex items-center justify-around py-2">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('dashboard') ? 'text-[#002452]' : 'text-[#6B7280]' }}">
            <span class="material-symbols-outlined text-2xl" style="{{ request()->routeIs('dashboard') ? 'font-variation-settings:\'FILL\' 1;' : '' }}">dashboard</span>
            <span class="text-[9px] font-medium">Accueil</span>
        </a>
        <a href="{{ route('depenses.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('depenses.*') ? 'text-[#002452]' : 'text-[#6B7280]' }}">
            <span class="material-symbols-outlined text-2xl" style="{{ request()->routeIs('depenses.*') ? "font-variation-settings:'FILL' 1;" : '' }}">receipt_long</span>
            <span class="text-[9px] font-medium">Dépenses</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('epargne.*') ? 'text-[#002452]' : 'text-[#6B7280]' }}">
            <span class="material-symbols-outlined text-2xl">savings</span>
            <span class="text-[9px] font-medium">Épargne</span>
        </a>
        <a href="#" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('rapports.*') ? 'text-[#002452]' : 'text-[#6B7280]' }}">
            <span class="material-symbols-outlined text-2xl">bar_chart</span>
            <span class="text-[9px] font-medium">Rapports</span>
        </a>
    </nav>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
