<x-layouts.app title="Notifications" pageTitle="Notifications" pageSubtitle="Période en cours : {{ $periodeLabel ?? '' }} — alertes budget & dettes">

@php
$config = [
    'budget_sain'      => ['icon' => 'check_circle',     'color' => '#006c49', 'bg' => '#d1fae5', 'label' => 'Budget sain'],
    'attention'        => ['icon' => 'warning',           'color' => '#D97706', 'bg' => '#fef3c7', 'label' => 'Attention'],
    'critique'         => ['icon' => 'error',             'color' => '#DC2626', 'bg' => '#fee2e2', 'label' => 'Critique'],
    'plafond_80'       => ['icon' => 'speed',             'color' => '#D97706', 'bg' => '#fef3c7', 'label' => 'Plafond 80%'],
    'plafond_depasse'  => ['icon' => 'block',             'color' => '#DC2626', 'bg' => '#fee2e2', 'label' => 'Plafond dépassé'],
    'epargne_deficit'  => ['icon' => 'savings',           'color' => '#D97706', 'bg' => '#fef3c7', 'label' => 'Déficit épargne'],
    'reajustement'     => ['icon' => 'tune',              'color' => '#6366F1', 'bg' => '#ede9fe', 'label' => 'Réajustement'],
    'quota_applique'   => ['icon' => 'account_balance',   'color' => '#002452', 'bg' => '#e0e7ff', 'label' => 'Quota appliqué'],
    'echeance_proche'  => ['icon' => 'schedule',          'color' => '#D97706', 'bg' => '#fef3c7', 'label' => 'Échéance J-7'],
    'echeance_j1'      => ['icon' => 'event_busy',        'color' => '#DC2626', 'bg' => '#fee2e2', 'label' => 'Échéance demain'],
    'echeance_depassee'=> ['icon' => 'error',             'color' => '#DC2626', 'bg' => '#fee2e2', 'label' => 'Échéance dépassée'],
    'remboursement_partiel' => ['icon' => 'payments',   'color' => '#6366F1', 'bg' => '#ede9fe', 'label' => 'Remboursement'],
];
@endphp

{{-- ===== BARRE D'ACTIONS ===== --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">

    {{-- Filtres --}}
    <div class="flex gap-1 bg-[#F8FAFC] border border-[#E5E7EB] p-1 rounded-xl">
        @foreach(['toutes' => 'Toutes', 'non_lues' => 'Non lues', 'lues' => 'Lues'] as $val => $label)
        <a href="{{ route('alertes.index', ['filtre' => $val]) }}"
           class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                  {{ $filtre === $val ? 'bg-white shadow text-[#002452] font-bold' : 'text-[#6B7280] hover:text-[#002452]' }}">
            {{ $label }}
            @if($val === 'non_lues' && $nonLues > 0)
                <span class="ml-1 bg-[#DC2626] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $nonLues }}</span>
            @endif
        </a>
        @endforeach
    </div>

    <div class="flex items-center gap-2">
        {{-- Analyser maintenant --}}
        <form method="POST" action="{{ route('alertes.analyser') }}">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 text-xs bg-[#002452] text-white px-3 py-2 rounded-lg font-semibold hover:bg-[#003580] transition-colors">
                <span class="material-symbols-outlined text-sm">refresh</span>
                Analyser budget
            </button>
        </form>
        @if($nonLues > 0)
        <form method="POST" action="{{ route('alertes.tout_lire') }}">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 text-xs border border-[#E5E7EB] bg-white text-[#1F2937] px-3 py-2 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                <span class="material-symbols-outlined text-sm">done_all</span>
                Tout lire
            </button>
        </form>
        @endif
        <form method="POST" action="{{ route('alertes.tout_supprimer') }}">
            @csrf @method('DELETE')
            <button type="submit"
                    onclick="return confirm('Supprimer toutes les alertes lues ?')"
                    class="flex items-center gap-1.5 text-xs border border-[#FEE2E2] bg-white text-[#DC2626] px-3 py-2 rounded-lg font-semibold hover:bg-red-50 transition-colors">
                <span class="material-symbols-outlined text-sm">delete_sweep</span>
                Vider les lues
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="mb-4 p-3 rounded-lg bg-[#d1fae5] border border-[#006c49]/20 text-[#065f46] text-sm font-medium flex items-center gap-2">
    <span class="material-symbols-outlined text-base">check_circle</span>
    {{ session('success') }}
</div>
@endif

{{-- ===== LISTE ===== --}}
<div class="space-y-2 mb-20 lg:mb-0">
    @forelse($alertes as $alerte)
    @php $cfg = $config[$alerte->type] ?? ['icon' => 'notifications', 'color' => '#6B7280', 'bg' => '#F3F4F6', 'label' => $alerte->type]; @endphp
    <div class="soft-card flex items-start gap-4 p-4 transition-all
                {{ $alerte->lu_at ? 'opacity-50' : '' }}">

        {{-- Icône --}}
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background: {{ $cfg['bg'] }}">
            <span class="material-symbols-outlined text-xl" style="color: {{ $cfg['color'] }};font-variation-settings:'FILL' 1">{{ $cfg['icon'] }}</span>
        </div>

        {{-- Contenu --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                      style="background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }}">
                    {{ $cfg['label'] }}
                </span>
                @if(!$alerte->lu_at)
                    <span class="text-[10px] bg-[#002452] text-white font-bold px-1.5 py-0.5 rounded-full">Nouveau</span>
                @endif
                <span class="text-[11px] text-[#9CA3AF] ml-auto">
                    {{ $alerte->created_at->diffForHumans() }}
                </span>
            </div>

            <p class="text-sm text-[#1F2937] font-medium leading-snug">{{ $alerte->message }}</p>

            @if($alerte->meta)
            @php $meta = $alerte->meta; @endphp
            @if(isset($meta['mois']) && isset($meta['annee']))
            <p class="text-[11px] text-[#6B7280] mt-0.5">
                {{ \Carbon\Carbon::createFromDate($meta['annee'], $meta['mois'], 1)->translatedFormat('F Y') }}
            </p>
            @endif

            @include('alertes.partials.conseils', ['alerte' => $alerte, 'meta' => $meta, 'cfg' => $cfg])
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1 flex-shrink-0 ml-2">
            @if(!$alerte->lu_at)
            <form method="POST" action="{{ route('alertes.lue', $alerte) }}">
                @csrf
                <button type="submit" title="Marquer comme lue"
                        class="p-1.5 rounded-lg text-[#6B7280] hover:text-[#006c49] hover:bg-[#d1fae5] transition-colors">
                    <span class="material-symbols-outlined text-base">check</span>
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('alertes.supprimer', $alerte) }}">
                @csrf @method('DELETE')
                <button type="submit" title="Supprimer cette notification"
                        class="p-1.5 rounded-lg bg-[#FEF2F2] text-[#DC2626] hover:bg-[#fee2e2] transition-colors border border-[#FECACA]">
                    <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 0">close</span>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="soft-card p-16 text-center">
        <span class="material-symbols-outlined text-6xl text-[#E5E7EB]">notifications_off</span>
        <p class="text-sm text-[#6B7280] mt-3 font-medium">Aucune notification
            {{ $filtre === 'non_lues' ? 'non lue' : ($filtre === 'lues' ? 'lue' : '') }}</p>
        <p class="text-xs text-[#9CA3AF] mt-1">Ajoutez des dépenses pour déclencher les alertes automatiques.</p>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($alertes->hasPages())
<div class="mt-4">{{ $alertes->links() }}</div>
@endif

</x-layouts.app>
