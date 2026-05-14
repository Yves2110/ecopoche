<x-layouts.app title="Notifications" pageTitle="Notifications" pageSubtitle="Alertes & messages système">

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

            {{-- Conseils affichés directement pour alertes actionnables --}}
            @if(in_array($alerte->type, ['critique', 'attention', 'plafond_depasse', 'epargne_deficit']))
            <div class="mt-3 rounded-xl p-3 space-y-2"
                 style="background: {{ $cfg['bg'] }}; border: 1px solid {{ $cfg['color'] }}33">

                @if($alerte->type === 'critique')
                    @php $suggestions = $meta['suggestions'] ?? null; @endphp
                    <p class="text-xs font-semibold text-[#374151] flex items-center gap-1 mb-2">
                        <span class="material-symbols-outlined text-sm" style="color:{{ $cfg['color'] }}">assignment_late</span>
                        Actions immédiates recommandées
                    </p>
                    <ul class="text-xs space-y-1.5 text-[#4B5563]">
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#DC2626] mt-px">block</span>Geler toutes les dépenses non essentielles jusqu'à la fin du mois.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#6366F1] mt-px">lock_open</span>Envisagez de débloquer une partie de votre réserve si disponible.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">remove_shopping_cart</span>Réduisez les sorties, loisirs et achats en ligne.</li>
                        @if($suggestions)
                        <li class="flex items-start gap-2 font-medium text-[#374151]"><span class="material-symbols-outlined text-sm mt-px" style="color:{{ $cfg['color'] }}">bar_chart</span>{{ $suggestions }}</li>
                        @endif
                    </ul>
                    <a href="{{ route('depenses.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80" style="color:{{ $cfg['color'] }};border-color:{{ $cfg['color'] }}33;background:white">
                        <span class="material-symbols-outlined text-sm">receipt_long</span>Voir mes dépenses
                    </a>

                @elseif($alerte->type === 'attention')
                    <p class="text-xs font-semibold text-[#374151] flex items-center gap-1 mb-2">
                        <span class="material-symbols-outlined text-sm" style="color:{{ $cfg['color'] }}">tips_and_updates</span>
                        Conseils pour rester dans le budget
                    </p>
                    <ul class="text-xs space-y-1.5 text-[#4B5563]">
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">edit_note</span>Notez chaque dépense avant de la faire, même les petites.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">local_grocery_store</span>Faites une seule course groupée au lieu de plusieurs petits achats.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">directions_walk</span>Limitez les déplacements et transports non planifiés.</li>
                    </ul>

                @elseif($alerte->type === 'plafond_depasse')
                    @php $cat = $meta['categorie'] ?? 'cette catégorie'; $depasse = $meta['depasse'] ?? 0; @endphp
                    <p class="text-xs font-semibold text-[#374151] flex items-center gap-1 mb-2">
                        <span class="material-symbols-outlined text-sm" style="color:{{ $cfg['color'] }}">assignment_late</span>
                        Plafond {{ $cat }} dépassé — que faire ?
                    </p>
                    <ul class="text-xs space-y-1.5 text-[#4B5563]">
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#DC2626] mt-px">block</span>N'ajoutez plus de dépenses dans <strong>{{ $cat }}</strong> ce mois.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">tune</span>Ajustez votre plafond le mois prochain si ce montant est récurrent.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#006c49] mt-px">swap_horiz</span>Compensez en réduisant une autre catégorie de {{ number_format($depasse, 0, ',', ' ') }} FCFA.</li>
                    </ul>
                    <a href="{{ route('depenses.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80" style="color:{{ $cfg['color'] }};border-color:{{ $cfg['color'] }}33;background:white">
                        <span class="material-symbols-outlined text-sm">tune</span>Gérer les plafonds
                    </a>

                @elseif($alerte->type === 'epargne_deficit')
                    @php $deficit = $meta['deficit'] ?? 0; $objectif = $meta['objectif'] ?? 0; @endphp
                    <p class="text-xs font-semibold text-[#374151] flex items-center gap-1 mb-2">
                        <span class="material-symbols-outlined text-sm" style="color:{{ $cfg['color'] }}">tips_and_updates</span>
                        Rattraper le déficit d'épargne
                    </p>
                    <ul class="text-xs space-y-1.5 text-[#4B5563]">
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">remove_shopping_cart</span>Réduisez loisirs et sorties d'environ {{ number_format($deficit / 2, 0, ',', ' ') }} FCFA.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#D97706] mt-px">savings</span>Versez le déficit manuellement dans votre objectif d'épargne.</li>
                        <li class="flex items-start gap-2"><span class="material-symbols-outlined text-sm text-[#006c49] mt-px">trending_up</span>Envisagez de ramener votre objectif à {{ number_format($objectif * 0.8, 0, ',', ' ') }} FCFA le mois prochain.</li>
                    </ul>
                    <a href="{{ route('epargne.index') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-colors hover:opacity-80" style="color:#006c49;border-color:#006c4933;background:white">
                        <span class="material-symbols-outlined text-sm">savings</span>Aller à l'épargne
                    </a>
                @endif
            </div>
            @endif
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
