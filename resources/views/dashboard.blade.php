<x-layouts.app title="Dashboard" pageTitle="Vue d'ensemble"
    pageSubtitle="{{ \Carbon\Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y') }}" monthSelector>

{{-- ===== LIGNE HAUTE : SANTÉ + ÉPARGNE DU MOIS + OBJECTIF ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

@php
    $santeCfg = [
        'neutre'    => ['bg'=>'bg-[#F8FAFC]',      'border'=>'border-[#E5E7EB]',    'badge'=>'bg-gray-100 text-gray-500',      'icon'=>'pending',  'iconcol'=>'text-gray-400',  'titre'=>'Non configuré',     'msg'=>'Saisissez votre salaire pour activer le suivi.'],
        'sain'      => ['bg'=>'bg-[#006c49]/5',     'border'=>'border-[#006c49]/20', 'badge'=>'bg-[#006c49]/10 text-[#006c49]', 'icon'=>'verified', 'iconcol'=>'text-[#006c49]', 'titre'=>'Budget maîtrisé',   'msg'=>'Vos dépenses sont en bonne trajectoire.'],
        'attention' => ['bg'=>'bg-[#F59E0B]/5',     'border'=>'border-[#F59E0B]/20', 'badge'=>'bg-[#fef3c7] text-[#92400e]',    'icon'=>'warning',  'iconcol'=>'text-[#F59E0B]', 'titre'=>'Attention budget',  'msg'=>'Plus de 70% de vos revenus sont dépensés.'],
        'critique'  => ['bg'=>'bg-[#EF4444]/5',     'border'=>'border-[#EF4444]/20', 'badge'=>'bg-[#fee2e2] text-[#991b1b]',    'icon'=>'error',    'iconcol'=>'text-[#EF4444]', 'titre'=>'Budget dépassé !',  'msg'=>'Vos dépenses excèdent vos revenus.'],
    ];
    $cfg = $santeCfg[$sante];
@endphp

    {{-- Card 1 : Santé financière --}}
    <div class="soft-card p-5 flex flex-col justify-between relative overflow-hidden {{ $cfg['bg'] }} {{ $cfg['border'] }}">
        <div class="flex items-start justify-between">
            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $cfg['badge'] }}">Santé Financière</span>
            <span class="material-symbols-outlined text-2xl {{ $cfg['iconcol'] }}" style="font-variation-settings:'FILL' 1;">{{ $cfg['icon'] }}</span>
        </div>
        <div class="mt-3">
            <h3 class="font-headline text-lg font-bold text-[#002452]">{{ $cfg['titre'] }}</h3>
            <p class="text-xs text-[#6B7280] mt-1">{{ $cfg['msg'] }}</p>
        </div>
        <div class="mt-4 pt-3 border-t border-[#E5E7EB] flex items-center gap-3">
            <div class="flex-1">
                <p class="text-[10px] text-[#6B7280] font-bold uppercase mb-1">Dépensé / Dispo</p>
                <div class="h-1.5 rounded-full bg-[#E5E7EB] overflow-hidden">
                    @php $pctDep = $revenuTotal > 0 ? min(100, round($totalDepenses / $revenuTotal * 100)) : 0; @endphp
                    <div class="h-full rounded-full transition-all"
                         style="width:{{ $pctDep }}%; background-color:{{ $sante === 'critique' ? '#EF4444' : ($sante === 'attention' ? '#F59E0B' : '#006c49') }}"></div>
                </div>
            </div>
            <span class="text-xs font-bold {{ $sante === 'critique' ? 'text-[#EF4444]' : ($sante === 'attention' ? 'text-[#F59E0B]' : 'text-[#006c49]') }}">{{ $pctDep }}%</span>
        </div>
    </div>

@php
    // Card 2 : Épargne du mois = épargne salaire + réserve bonus
    $epMoisPctSalaire = $epargne_salaire_pct;
    $epMoisSalaire    = (int) $epargneSalaire;
    $epMoisReserve    = (int) $totalReserve;
    $epMoisTotal      = (int) $epargneNaturelle;
@endphp

    {{-- Card 2 : Épargne du mois --}}
    <div class="soft-card p-5 flex flex-col justify-between" style="background-color:#002452">
        <div class="flex items-start justify-between">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/10 text-white/70">Épargne du mois</span>
            </div>
            <span class="material-symbols-outlined text-2xl" style="color:#6ffbbe;font-variation-settings:'FILL' 1;">savings</span>
        </div>
        <div class="mt-3">
            <p class="font-headline text-2xl font-bold text-white">{{ number_format($epMoisTotal, 0, ',', "\u{00A0}") }} <span class="text-sm font-normal text-white/60">FCFA</span></p>
        </div>
        <div class="mt-4 pt-3 border-t border-white/10 grid grid-cols-2 gap-3">
            <div>
                <p class="text-[10px] text-white/50 font-bold uppercase">Sur salaire ({{ $epMoisPctSalaire }}%)</p>
                <p class="text-sm font-bold text-white mt-0.5">{{ number_format($epMoisSalaire, 0, ',', "\u{00A0}") }}</p>
            </div>
            <div>
                <p class="text-[10px] text-white/50 font-bold uppercase">Réserve bonus</p>
                <p class="text-sm font-bold" style="color:#6ffbbe;margin-top:0.125rem">{{ number_format($epMoisReserve, 0, ',', "\u{00A0}") }}</p>
            </div>
        </div>
    </div>

@php
    // Card 3 : Objectif épargne actif
    if ($objectifActif) {
        $objPct    = $objectifActif->pourcentage;
        $objNom    = $objectifActif->nom;
        $objCible  = (int) $objectifActif->montant_cible;
        $objActuel = (int) $objectifActif->montant_actuel;
        $objRestant = (int) $objectifActif->restant;
        if ($objPct >= 100)      $objCfg = ['bg'=>'#006c49', 'accent'=>'#6ffbbe', 'badge'=>'Atteint !',     'icon'=>'check_circle'];
        elseif ($objPct >= 50)   $objCfg = ['bg'=>'#004f35', 'accent'=>'#6ffbbe', 'badge'=>'En bonne voie','icon'=>'trending_up'];
        elseif ($objPct >= 20)   $objCfg = ['bg'=>'#92400e', 'accent'=>'#FCD34D', 'badge'=>'À améliorer',  'icon'=>'warning'];
        else                     $objCfg = ['bg'=>'#1e3a5f', 'accent'=>'#93C5FD', 'badge'=>'Démarré',      'icon'=>'flag'];
    } else {
        $objPct = 0; $objNom = 'Aucun objectif actif'; $objCible = 0; $objActuel = 0; $objRestant = 0;
        $objCfg = ['bg'=>'#374151', 'accent'=>'#9CA3AF', 'badge'=>'Non défini', 'icon'=>'flag'];
    }
@endphp

    {{-- Card 3 : Objectif épargne --}}
    <div class="soft-card p-5 flex flex-col justify-between text-white" style="background-color:{{ $objCfg['bg'] }}">
        <div class="flex items-start justify-between">
            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/10 text-white/70">{{ $objCfg['badge'] }}</span>
            <span class="material-symbols-outlined text-2xl" style="color:{{ $objCfg['accent'] }};font-variation-settings:'FILL' 1;">{{ $objCfg['icon'] }}</span>
        </div>
        <div class="mt-2">
            <p class="text-xs text-white/60 font-medium truncate">{{ $objNom }}</p>
            <p class="font-headline text-2xl font-bold mt-0.5">{{ number_format($objActuel, 0, ',', "\u{00A0}") }} <span class="text-sm font-normal text-white/60">/ {{ number_format($objCible, 0, ',', "\u{00A0}") }}</span></p>
        </div>
        <div class="mt-4 pt-3 border-t border-white/10">
            <div class="flex justify-between text-[10px] text-white/50 font-bold uppercase mb-1.5">
                <span>Progression</span>
                <span style="color:{{ $objCfg['accent'] }}">{{ $objPct }}%</span>
            </div>
            <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700"
                     style="width:{{ $objPct }}%; background-color:{{ $objCfg['accent'] }}"></div>
            </div>
            @if($objectifActif && $objRestant > 0)
            <p class="text-[10px] text-white/40 mt-1.5">Reste {{ number_format($objRestant, 0, ',', "\u{00A0}") }} FCFA</p>
            @endif
        </div>
    </div>

</div>

{{-- ===== KPI CARDS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280] text-lg">wallet</span>
            <span class="badge-green">Fixe</span>
        </div>
        <p class="kpi-label">Revenu fixe</p>
        <p class="kpi-value">{{ number_format((int)$budget->salaire_fixe, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>

    <div class="kpi-card border-[#006c49]/20 bg-[#006c49]/5">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#006c49] text-lg">payments</span>
            <span class="text-[10px] bg-[#d1fae5] text-[#065f46] font-bold px-2 py-0.5 rounded-full">30% bonus</span>
        </div>
        <p class="kpi-label text-[#006c49]">Bonus dépensable</p>
        <p class="kpi-value text-[#006c49]">{{ number_format((int)$totalDepensable, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>

    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280] text-lg">shopping_cart</span>
            <span class="badge-yellow">Ce mois</span>
        </div>
        <p class="kpi-label">Total dépensé</p>
        <p class="kpi-value">{{ number_format((int)$totalDepenses, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>

    <div class="kpi-card {{ $soldeDisponible >= 0 ? 'border-[#006c49]/30 bg-[#006c49]/5' : 'border-[#EF4444]/30 bg-[#EF4444]/5' }}">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-lg {{ $soldeDisponible >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">show_chart</span>
        </div>
        <p class="kpi-label {{ $soldeDisponible >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">Solde disponible</p>
        <p class="kpi-value {{ $soldeDisponible >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">{{ number_format((int)$soldeDisponible, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>
</div>

{{-- ===== GRAPHIQUES ===== --}}
<div class="grid grid-cols-12 gap-4 mb-4">

    {{-- Flux de trésorerie --}}
    <div class="col-span-12 lg:col-span-7 soft-card p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-headline text-base font-semibold text-[#1F2937]">Flux — 14 derniers jours</h3>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>
                <span class="text-xs text-[#6B7280]">Dépenses</span>
            </div>
        </div>
        <div class="relative h-52">
            <canvas id="chartFlux"></canvas>
        </div>
    </div>

    {{-- Répartition analytique --}}
    <div class="col-span-12 lg:col-span-5 soft-card p-5">
        <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4">Répartition Analytique</h3>
        @if($parCategorie->isEmpty())
            <div class="flex flex-col items-center justify-center h-36 text-center">
                <span class="material-symbols-outlined text-4xl text-[#E5E7EB]">donut_large</span>
                <p class="text-xs text-[#6B7280] mt-2">Aucune dépense ce mois.</p>
            </div>
        @else
        <div class="flex items-center gap-4">
            <div class="relative w-28 h-28 flex-shrink-0">
                <canvas id="chartDonut"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-[9px] text-[#6B7280] font-bold uppercase">Total</span>
                    <span class="font-headline font-bold text-xs text-[#1F2937]">{{ number_format((int)$totalDepenses, 0, ',', "\u{00A0}") }}</span>
                </div>
            </div>
            <div class="flex-1 space-y-2">
                @foreach($parCategorie as $cat)
                @php $pct = $totalDepenses > 0 ? round($cat['total'] / $totalDepenses * 100) : 0; @endphp
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $cat['couleur'] }}"></span>
                        <span class="text-xs text-[#1F2937] truncate max-w-24">{{ $cat['nom'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-[#6B7280]">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== ALERTES ===== --}}
@if($budget->salaire_fixe == 0)
<div class="soft-card p-4 mb-4 flex items-center gap-3 border-amber-200 bg-amber-50">
    <div class="bg-amber-100 text-amber-700 p-2 rounded-lg flex-shrink-0">
        <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">info</span>
    </div>
    <div class="flex-1">
        <p class="font-semibold text-sm text-amber-900">Aucun salaire fixe configuré pour ce mois</p>
        <p class="text-xs text-amber-700">Commencez par saisir votre salaire fixe pour activer le suivi budgétaire.</p>
    </div>
    <a href="{{ route('revenus.index') }}" class="btn-primary text-xs py-1.5 px-3 flex-shrink-0">Configurer</a>
</div>
@endif
@foreach($alertes as $alerte)
<div class="soft-card p-4 mb-2 flex items-center gap-3
    {{ $alerte->gravite === 'danger' ? 'border-[#EF4444]/30 bg-[#EF4444]/5' : ($alerte->gravite === 'warning' ? 'border-amber-200 bg-amber-50' : 'border-blue-200 bg-blue-50') }}">
    <span class="material-symbols-outlined text-lg
        {{ $alerte->gravite === 'danger' ? 'text-[#EF4444]' : ($alerte->gravite === 'warning' ? 'text-amber-600' : 'text-blue-600') }}"
        style="font-variation-settings:'FILL' 1;">{{ $alerte->gravite === 'danger' ? 'error' : ($alerte->gravite === 'warning' ? 'warning' : 'info') }}</span>
    <p class="flex-1 text-sm font-medium text-[#1F2937]">{{ $alerte->message }}</p>
</div>
@endforeach

{{-- ===== DERNIÈRES DÉPENSES ===== --}}
<div class="soft-card overflow-hidden mb-20 lg:mb-0">
    <div class="px-5 py-4 border-b border-[#E5E7EB] flex justify-between items-center bg-white">
        <h3 class="font-headline text-base font-semibold text-[#1F2937]">Dernières Dépenses</h3>
        <a href="{{ route('depenses.index') }}" class="text-[#006c49] text-xs font-bold hover:underline flex items-center gap-1">
            Tout voir <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-[#F8FAFC] text-[10px] text-[#6B7280] border-b border-[#E5E7EB] uppercase font-semibold tracking-wider">
                <tr>
                    <th class="px-5 py-3">Désignation</th>
                    <th class="px-5 py-3">Catégorie</th>
                    <th class="px-5 py-3 hidden sm:table-cell">Date</th>
                    <th class="px-5 py-3 text-right">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB] text-sm">
                @forelse($dernieresDepenses as $dep)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-[#1F2937]">
                        {{ $dep->note ?: ($dep->categorie?->nom ?? 'Dépense') }}
                        @if($dep->imprevue)<span class="badge-red ml-1">Imprévue</span>@endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm" style="color: {{ $dep->categorie?->couleur ?? '#6B7280' }}">{{ $dep->categorie?->icone ?? 'category' }}</span>
                            <span class="text-xs text-[#6B7280]">{{ $dep->categorie?->nom ?? '—' }}</span>
                        </span>
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell text-xs text-[#6B7280]">{{ $dep->date->translatedFormat('d M Y') }}</td>
                    <td class="px-5 py-3 text-right font-bold text-[#EF4444]">− {{ number_format((int)$dep->montant, 0, ',', "\u{00A0}") }} FCFA</td>
                </tr>
                @empty
                <tr>
                    <td class="px-5 py-6 text-[#6B7280] italic text-sm" colspan="4">Aucune dépense ce mois-ci.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Graphique flux
    const ctxFlux = document.getElementById('chartFlux');
    if (ctxFlux) {
        new Chart(ctxFlux, {
            type: 'bar',
            data: {
                labels: @json($joursLabels),
                datasets: [{
                    label: 'Dépenses',
                    data: @json($joursData),
                    backgroundColor: 'rgba(239,68,68,0.15)',
                    borderColor: '#EF4444',
                    borderWidth: 1.5,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                    y: { grid: { color: '#F3F4F6' }, ticks: { font: { size: 10 }, color: '#9CA3AF',
                        callback: v => v === 0 ? '0' : (v/1000).toFixed(0)+'k' } }
                }
            }
        });
    }

    // Graphique donut
    const ctxDonut = document.getElementById('chartDonut');
    if (ctxDonut && {{ $parCategorie->isNotEmpty() ? 'true' : 'false' }}) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: @json($parCategorie->pluck('nom')->values()),
                datasets: [{
                    data: @json($parCategorie->pluck('total')->values()),
                    backgroundColor: @json($parCategorie->pluck('couleur')->values()),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: {
                    callbacks: {
                        title: ctx => ctx[0].label,
                        label: ctx => '  ' + ctx.parsed.toLocaleString('fr-FR') + ' FCFA'
                    }
                }}
            }
        });
    }
});
</script>
@endpush

</x-layouts.app>
