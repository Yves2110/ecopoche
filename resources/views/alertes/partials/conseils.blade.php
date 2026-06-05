@php
    use App\Services\AlerteConseilsService;
    $budgetAlerte = null;
    if (isset($meta['mois'], $meta['annee'])) {
        $budgetAlerte = \App\Models\Budget::where('user_id', auth()->id())
            ->where('mois', $meta['mois'])
            ->where('annee', $meta['annee'])
            ->first();
    }
    $conseils = AlerteConseilsService::pourType($alerte->type, $meta, $budgetAlerte);
    $typesAvecConseils = [
        'critique', 'attention', 'plafond_80', 'plafond_depasse', 'epargne_deficit',
        'echeance_proche', 'echeance_j1', 'echeance_depassee', 'remboursement_partiel', 'budget_sain', 'quota_applique',
    ];
@endphp

@if(in_array($alerte->type, $typesAvecConseils) && count($conseils) > 0)
<div class="mt-3 rounded-xl p-3 space-y-2"
     style="background: {{ $cfg['bg'] }}; border: 1px solid {{ $cfg['color'] }}33">
    <p class="text-xs font-semibold text-[#374151] flex items-center gap-1 mb-2">
        <span class="material-symbols-outlined text-sm" style="color:{{ $cfg['color'] }}">tips_and_updates</span>
        @if($alerte->type === 'critique')
            Actions immédiates recommandées
        @elseif(str_starts_with($alerte->type, 'echeance'))
            Que faire maintenant
        @else
            Conseils personnalisés
        @endif
    </p>
    <ul class="text-xs space-y-1.5 text-[#4B5563]">
        @foreach($conseils as $conseil)
        <li class="flex items-start gap-2">
            <span class="material-symbols-outlined text-sm mt-px" style="color:{{ $cfg['color'] }}">chevron_right</span>
            <span>{{ $conseil }}</span>
        </li>
        @endforeach
    </ul>
    @if(in_array($alerte->type, ['critique', 'attention', 'plafond_80', 'plafond_depasse']))
    <a href="{{ route('depenses.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80" style="color:{{ $cfg['color'] }};border-color:{{ $cfg['color'] }}33;background:white">
        <span class="material-symbols-outlined text-sm">receipt_long</span>Voir mes dépenses
    </a>
    @elseif(str_starts_with($alerte->type, 'echeance') || $alerte->type === 'remboursement_partiel')
    <a href="{{ route('dettes.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80" style="color:{{ $cfg['color'] }};border-color:{{ $cfg['color'] }}33;background:white">
        <span class="material-symbols-outlined text-sm">swap_horiz</span>Emprunts & prêts
    </a>
    @elseif($alerte->type === 'epargne_deficit')
    <a href="{{ route('epargne.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80" style="color:#006c49;border-color:#006c4933;background:white">
        <span class="material-symbols-outlined text-sm">savings</span>Aller à l'épargne
    </a>
    @endif
</div>
@endif
