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

    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="alternate icon" href="/favicon.ico" />

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

        <a href="{{ route('epargne.index') }}"
           class="sidebar-item {{ request()->routeIs('epargne.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">savings</span>
            <span>Épargne</span>
        </a>

        <a href="{{ route('alertes.index') }}"
           class="sidebar-item {{ request()->routeIs('alertes.*') ? 'sidebar-item-active' : '' }} relative">
            <span class="material-symbols-outlined text-xl">notifications</span>
            <span>Alertes</span>
            @php $sidebarNbAlertes = auth()->check() ? \App\Models\Alerte::where('user_id', auth()->id())->whereNull('lu_at')->count() : 0; @endphp
            @if($sidebarNbAlertes > 0)
            <span class="ml-auto text-[10px] bg-[#DC2626] text-white font-bold px-1.5 py-0.5 rounded-full">{{ $sidebarNbAlertes }}</span>
            @endif
        </a>

        <p class="text-[9px] font-bold uppercase tracking-widest text-white/30 px-3 mt-4 mb-2">Analyse</p>

        <a href="{{ route('rapports.index') }}"
           class="sidebar-item {{ request()->routeIs('rapports.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">bar_chart</span>
            <span>Rapports</span>
        </a>

        @if(auth()->user()?->role === 'super_admin' || auth()->user()?->role === 'admin')
        <p class="text-[9px] font-bold uppercase tracking-widest text-white/30 px-3 mt-4 mb-2">Administration</p>
        <a href="{{ route('admin.index') }}"
           class="sidebar-item {{ request()->routeIs('admin.*') ? 'sidebar-item-active' : '' }}">
            <span class="material-symbols-outlined text-xl">admin_panel_settings</span>
            <span>Administration</span>
        </a>
        @endif
    </nav>

    {{-- Bottom : actions --}}
    <div class="px-3 py-4 border-t border-white/10 space-y-1">
        <a href="{{ route('profil.index') }}"
           class="sidebar-item {{ request()->routeIs('profil.*') ? 'sidebar-item-active' : '' }}">
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
            @php $nbAlertes = auth()->check() ? \App\Models\Alerte::where('user_id', auth()->id())->whereNull('lu_at')->count() : 0; @endphp
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" class="relative p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <span class="material-symbols-outlined text-[#6B7280] text-xl"
                          style="{{ $nbAlertes > 0 ? 'font-variation-settings:\'FILL\' 1' : '' }}; color: {{ $nbAlertes > 0 ? '#DC2626' : '#6B7280' }}">notifications</span>
                    @if($nbAlertes > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-[#DC2626] text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 leading-none">
                        {{ $nbAlertes > 99 ? '99+' : $nbAlertes }}
                    </span>
                    @endif
                </button>

                {{-- Mini-dropdown --}}
                <div x-show="open" x-transition
                     class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-xl border border-[#E5E7EB] z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-[#E5E7EB] flex items-center justify-between">
                        <span class="font-headline font-bold text-sm text-[#1F2937]">Notifications</span>
                        @if($nbAlertes > 0)
                        <span class="text-[10px] bg-[#DC2626] text-white font-bold px-2 py-0.5 rounded-full">{{ $nbAlertes }} non lue{{ $nbAlertes > 1 ? 's' : '' }}</span>
                        @endif
                    </div>

                    @php
                        $dernieresAlertes = auth()->check()
                            ? \App\Models\Alerte::where('user_id', auth()->id())
                                ->whereNull('lu_at')
                                ->orderByDesc('created_at')
                                ->take(5)
                                ->get()
                            : collect();
                        $alerteConfig = [
                            'budget_sain'     => ['#006c49', 'check_circle'],
                            'attention'       => ['#D97706', 'warning'],
                            'critique'        => ['#DC2626', 'error'],
                            'plafond_80'      => ['#D97706', 'speed'],
                            'plafond_depasse' => ['#DC2626', 'block'],
                            'epargne_deficit' => ['#D97706', 'savings'],
                            'reajustement'    => ['#6366F1', 'tune'],
                            'quota_applique'  => ['#002452', 'account_balance'],
                        ];
                    @endphp

                    <div class="max-h-64 overflow-y-auto divide-y divide-[#F3F4F6]">
                        @forelse($dernieresAlertes as $al)
                        @php [$acol, $aicon] = $alerteConfig[$al->type] ?? ['#6B7280', 'notifications']; @endphp
                        <div class="flex items-start gap-2 px-3 py-2.5 hover:bg-[#F8FAFC] group">
                            <span class="material-symbols-outlined text-base mt-0.5 flex-shrink-0"
                                  style="color: {{ $acol }};font-variation-settings:'FILL' 1">{{ $aicon }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-[#1F2937] leading-snug line-clamp-2">{{ $al->message }}</p>
                                <p class="text-[10px] text-[#9CA3AF] mt-0.5">{{ $al->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-0.5 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <form method="POST" action="{{ route('alertes.lue', $al) }}">
                                    @csrf
                                    <button type="submit" title="Marquer lue" class="p-1 rounded text-[#9CA3AF] hover:text-[#006c49] hover:bg-[#d1fae5]">
                                        <span class="material-symbols-outlined text-sm">check</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('alertes.supprimer', $al) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Supprimer" class="p-1 rounded text-[#9CA3AF] hover:text-[#DC2626] hover:bg-[#fee2e2]">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="px-4 py-6 text-center text-xs text-[#9CA3AF]">Aucune nouvelle notification</div>
                        @endforelse
                    </div>

                    <div class="px-4 py-3 border-t border-[#E5E7EB] flex items-center justify-between">
                        <a href="{{ route('alertes.index') }}" class="text-xs font-semibold text-[#002452] hover:underline">
                            Voir toutes les alertes →
                        </a>
                        @if($nbAlertes > 0)
                        <form method="POST" action="{{ route('alertes.tout_lire') }}">
                            @csrf
                            <button type="submit" class="text-xs text-[#6B7280] hover:text-[#1F2937] font-medium">
                                Tout lire
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
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

        {{-- Bannière impersonnification (visible sur toutes les pages) --}}
        @if(session('impersonnation_id'))
        <div class="flex items-center justify-between bg-[#D97706] text-white text-sm px-4 py-2.5 rounded-xl mb-4 border border-[#b45309]">
            <span class="flex items-center gap-2 font-medium">
                <span class="material-symbols-outlined text-base">switch_account</span>
                Mode accès — compte de <strong class="mx-1">{{ auth()->user()->name }}</strong>
                <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full hidden sm:inline">{{ auth()->user()->email }}</span>
            </span>
            <form method="POST" action="{{ route('admin.stop_impersonner') }}">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 text-xs font-bold bg-white text-[#D97706] hover:bg-white/90 px-3 py-1.5 rounded-lg transition-colors shadow">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Retour à mon compte
                </button>
            </form>
        </div>
        @endif

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
        <a href="{{ route('epargne.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('epargne.*') ? 'text-[#002452]' : 'text-[#6B7280]' }}">
            <span class="material-symbols-outlined text-2xl">savings</span>
            <span class="text-[9px] font-medium">Épargne</span>
        </a>
        <a href="{{ route('alertes.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 relative {{ request()->routeIs('alertes.*') ? 'text-[#002452]' : 'text-[#6B7280]' }}">
            <span class="material-symbols-outlined text-2xl" style="{{ request()->routeIs('alertes.*') ? "font-variation-settings:'FILL' 1;" : '' }}">notifications</span>
            @if(isset($nbAlertes) && $nbAlertes > 0)<span class="absolute top-0.5 right-1 w-4 h-4 bg-[#DC2626] rounded-full text-white text-[8px] font-bold flex items-center justify-center">{{ $nbAlertes }}</span>@endif
            <span class="text-[9px] font-medium">Alertes</span>
        </a>
    </nav>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
