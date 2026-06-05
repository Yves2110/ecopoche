@props([
    'estPeriodePassee' => false,
    'estPeriodeFuture' => false,
    'estPeriodeCourante' => true,
    'periodeLabel' => '',
    'context' => 'default',
])

@if($estPeriodePassee)
<div class="soft-card p-4 mb-4 flex items-start gap-3 border-[#E5E7EB] bg-[#F8FAFC]">
    <span class="material-symbols-outlined text-[#6B7280]">inventory_2</span>
    <div>
        <p class="font-semibold text-sm text-[#374151]">Période clôturée — {{ $periodeLabel }}</p>
        <p class="text-xs text-[#6B7280] mt-0.5">Consultation seule. Les saisies ne sont plus possibles sur cette période.</p>
    </div>
</div>
@elseif($estPeriodeFuture)
<div class="soft-card p-4 mb-4 flex items-start gap-3 border-blue-200 bg-blue-50">
    <span class="material-symbols-outlined text-blue-600">event_upcoming</span>
    <div>
        <p class="font-semibold text-sm text-blue-900">Période à venir — {{ $periodeLabel }}</p>
        <p class="text-xs text-blue-700 mt-0.5">
            @if($context === 'epargne')
                Vous pouvez préparer l'objectif d'épargne. Le suivi réel concerne surtout la période active.
            @else
                Consultation limitée jusqu'au début effectif de la période.
            @endif
        </p>
    </div>
</div>
@endif
