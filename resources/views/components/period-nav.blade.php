@props([
    'mois',
    'annee',
    'periodeLabel',
    'routeName' => 'revenus.index',
    'routeParams' => [],
    'showCurrentLink' => true,
])

@php
    $prec = \App\Services\BudgetPeriodService::periodePrecedente((int) $mois, (int) $annee);
    $next = \App\Services\BudgetPeriodService::periodeSuivante((int) $mois, (int) $annee);
    $base = array_merge($routeParams, ['mois' => null, 'annee' => null]);
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-between flex-wrap gap-3']) }}>
    <div class="flex items-center gap-2">
        <a href="{{ route($routeName, array_merge($routeParams, ['mois' => $prec['mois'], 'annee' => $prec['annee']])) }}"
           class="p-1.5 rounded-lg border border-[#E5E7EB] hover:bg-gray-50 transition-colors"
           title="Période précédente">
            <span class="material-symbols-outlined text-[#002452]">chevron_left</span>
        </a>
        <span class="text-base font-bold text-[#1F2937] min-w-32 text-center">{{ $periodeLabel }}</span>
        <a href="{{ route($routeName, array_merge($routeParams, ['mois' => $next['mois'], 'annee' => $next['annee']])) }}"
           class="p-1.5 rounded-lg border border-[#E5E7EB] hover:bg-gray-50 transition-colors"
           title="Période suivante">
            <span class="material-symbols-outlined text-[#002452]">chevron_right</span>
        </a>
    </div>
    @if($showCurrentLink)
    <a href="{{ route($routeName, $routeParams) }}" class="text-xs text-[#006c49] font-semibold hover:underline">
        Période courante →
    </a>
    @endif
</div>
