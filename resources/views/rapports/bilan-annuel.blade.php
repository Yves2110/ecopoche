<x-layouts.app title="Bilan annuel {{ $annee }}" pageTitle="Bilan Annuel {{ $annee }}"
    pageSubtitle="Récapitulatif complet de l'année {{ $annee }}">

@php $periodeLabel = "Bilan annuel {$annee}"; @endphp

{{-- ===== TOOLBAR ===== --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">

    {{-- Sélecteur d'année --}}
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-semibold text-[#6B7280] uppercase tracking-wide">Année :</span>
        <div class="flex gap-1 bg-[#F8FAFC] border border-[#E5E7EB] p-1 rounded-xl flex-wrap">
            @foreach($anneesDisponibles as $yr)
            <a href="{{ route('rapports.bilan-annuel', ['annee' => $yr]) }}"
               class="px-3 py-1.5 rounded-lg text-sm transition-all {{ $annee == $yr ? 'bg-white shadow text-[#002452] font-bold' : 'text-[#6B7280] hover:text-[#002452]' }}">
                {{ $yr }}
            </a>
            @endforeach
        </div>
        <a href="{{ route('rapports.index') }}"
           class="flex items-center gap-1 text-xs text-[#6B7280] hover:text-[#002452] transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span> Rapports
        </a>
    </div>

    {{-- Exports --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('rapports.bilan-annuel.pdf', ['annee' => $annee]) }}"
           class="flex items-center gap-2 px-4 py-2 rounded-lg border border-[#EF4444] text-[#EF4444] text-sm font-semibold hover:bg-[#EF4444]/5 transition-colors">
            <span class="material-symbols-outlined text-base">picture_as_pdf</span>
            Exporter PDF
        </a>
    </div>
</div>

{{-- ===== KPIs ANNUELS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">wallet</span>
            <span class="badge-blue">{{ $annee }}</span>
        </div>
        <p class="kpi-label">Total revenus</p>
        <p class="kpi-value">{{ number_format($totalRevenu, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>

    <div class="kpi-card {{ $totalDepenses > $totalRevenu ? 'border-[#EF4444]/30 bg-[#EF4444]/5' : '' }}">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined {{ $totalDepenses > $totalRevenu ? 'text-[#EF4444]' : 'text-[#6B7280]' }}">shopping_cart</span>
        </div>
        <p class="kpi-label {{ $totalDepenses > $totalRevenu ? 'text-[#EF4444]' : '' }}">Total dépenses</p>
        <p class="kpi-value {{ $totalDepenses > $totalRevenu ? 'text-[#EF4444]' : '' }}">{{ number_format($totalDepenses, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>

    <div class="kpi-card border-[#006c49]/20 bg-[#006c49]/5">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#006c49]">savings</span>
        </div>
        <p class="kpi-label text-[#006c49]">Total épargné</p>
        <p class="kpi-value text-[#006c49]">{{ number_format($totalEpargne, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>

    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">percent</span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                {{ $tauxEpargne >= 15 ? 'bg-[#d1fae5] text-[#065f46]' : ($tauxEpargne >= 5 ? 'bg-[#fef3c7] text-[#92400e]' : 'bg-[#fee2e2] text-[#991b1b]') }}">
                {{ $tauxEpargne >= 15 ? 'Excellent' : ($tauxEpargne >= 5 ? 'Correct' : 'Faible') }}
            </span>
        </div>
        <p class="kpi-label">Taux d'épargne annuel</p>
        <p class="kpi-value">{{ $tauxEpargne }}%</p>
    </div>
</div>

{{-- ===== MEILLEUR / PIRE MOIS ===== --}}
@if($meilleurMois || $pireMois)
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
    @if($meilleurMois)
    <div class="soft-card p-4 border-[#006c49]/30 bg-[#006c49]/5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#006c49] flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-white text-lg">trending_up</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-[#006c49] uppercase tracking-wide">Meilleur mois</p>
                <p class="font-headline font-bold text-[#1F2937] text-sm">{{ $meilleurMois['label'] }}</p>
                <p class="text-xs text-[#6B7280]">Solde : <span class="font-bold text-[#006c49]">+{{ number_format($meilleurMois['solde'], 0, ',', "\u{00A0}") }} FCFA</span></p>
            </div>
        </div>
    </div>
    @endif
    @if($pireMois)
    <div class="soft-card p-4 {{ $pireMois['solde'] < 0 ? 'border-[#EF4444]/30 bg-[#EF4444]/5' : 'border-[#F59E0B]/30 bg-[#F59E0B]/5' }}">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $pireMois['solde'] < 0 ? 'bg-[#EF4444]' : 'bg-[#F59E0B]' }} flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-white text-lg">trending_down</span>
            </div>
            <div>
                <p class="text-[10px] font-bold {{ $pireMois['solde'] < 0 ? 'text-[#EF4444]' : 'text-[#D97706]' }} uppercase tracking-wide">Mois le plus difficile</p>
                <p class="font-headline font-bold text-[#1F2937] text-sm">{{ $pireMois['label'] }}</p>
                <p class="text-xs text-[#6B7280]">Solde : <span class="font-bold {{ $pireMois['solde'] < 0 ? 'text-[#EF4444]' : 'text-[#D97706]' }}">{{ number_format($pireMois['solde'], 0, ',', "\u{00A0}") }} FCFA</span></p>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ===== GRAPHIQUES ===== --}}
<div class="grid grid-cols-12 gap-4 mb-5">

    {{-- Graphique comparatif annuel --}}
    <div class="col-span-12 lg:col-span-8 soft-card p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-headline text-base font-semibold text-[#1F2937]">Comparatif mensuel {{ $annee }}</h3>
            <div class="flex items-center gap-4 text-xs text-[#6B7280]">
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#002452]/20 inline-block"></span>Revenus</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#EF4444]/70 inline-block"></span>Dépenses</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#006c49]/70 inline-block"></span>Épargne</span>
            </div>
        </div>
        <div class="relative h-64">
            <canvas id="chartAnnuel"></canvas>
        </div>
    </div>

    {{-- Top catégories --}}
    <div class="col-span-12 lg:col-span-4 soft-card p-5">
        <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[#006c49] text-lg">leaderboard</span>
            Top dépenses {{ $annee }}
        </h3>
        @if($topCategories->isEmpty())
        <div class="flex flex-col items-center justify-center h-36 text-center">
            <span class="material-symbols-outlined text-4xl text-[#E5E7EB]">bar_chart</span>
            <p class="text-xs text-[#6B7280] mt-2">Aucune dépense sur l'année.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($topCategories as $i => $cat)
            @php
                $maxCat = $topCategories->first()['total'];
                $pctCat = $maxCat > 0 ? round($cat['total'] / $maxCat * 100) : 0;
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-[#6B7280] w-4">{{ $i+1 }}</span>
                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $cat['couleur'] }}"></span>
                        <span class="text-xs font-semibold text-[#1F2937]">{{ $cat['nom'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-[#1F2937]">{{ number_format($cat['total'], 0, ',', "\u{00A0}") }}</span>
                </div>
                <div class="h-2 bg-[#E5E7EB] rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="width:{{ $pctCat }}%;background:{{ $cat['couleur'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- ===== TABLEAU DÉTAIL ANNUEL ===== --}}
<div class="soft-card overflow-hidden mb-20 lg:mb-0">
    <div class="px-5 py-4 border-b border-[#E5E7EB] bg-white flex items-center justify-between">
        <h3 class="font-headline text-base font-semibold text-[#1F2937]">Détail mensuel — {{ $annee }}</h3>
        <span class="badge-blue">12 mois</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left" style="min-width:680px">
            <thead class="bg-[#F8FAFC] text-[10px] text-[#6B7280] border-b border-[#E5E7EB] uppercase font-semibold tracking-wider">
                <tr>
                    <th class="px-5 py-3">Mois</th>
                    <th class="px-5 py-3 text-right">Revenus</th>
                    <th class="px-5 py-3 text-right">Dépenses</th>
                    <th class="px-5 py-3 text-right">Solde</th>
                    <th class="px-5 py-3 text-right">Épargne</th>
                    <th class="px-5 py-3 text-center">Taux dep.</th>
                    <th class="px-5 py-3 text-center">Santé</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB] text-sm">
                @foreach(array_reverse($historique) as $h)
                @php
                    $taux = $h['revenu'] > 0 ? round($h['depenses'] / $h['revenu'] * 100) : 0;
                    $sante = match(true) {
                        $h['revenu'] == 0 => ['label'=>'—',        'cls'=>'text-[#9CA3AF]'],
                        $h['solde'] < 0   => ['label'=>'Dépassé',  'cls'=>'text-[#EF4444] font-bold'],
                        $taux >= 70        => ['label'=>'Attention','cls'=>'text-[#F59E0B] font-semibold'],
                        default            => ['label'=>'Sain',     'cls'=>'text-[#006c49] font-semibold'],
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-semibold text-[#1F2937] whitespace-nowrap">{{ $h['label'] }}</td>
                    <td class="px-5 py-3 text-right text-[#1F2937]">
                        {{ $h['revenu'] > 0 ? number_format($h['revenu'], 0, ',', "\u{00A0}") : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right {{ $h['depenses'] > 0 ? 'text-[#EF4444]' : 'text-[#9CA3AF]' }}">
                        {{ $h['depenses'] > 0 ? number_format($h['depenses'], 0, ',', "\u{00A0}") : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold {{ $h['solde'] >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">
                        {{ $h['revenu'] > 0 ? (($h['solde'] >= 0 ? '+' : '') . number_format($h['solde'], 0, ',', "\u{00A0}")) : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right text-[#006c49] font-semibold">
                        {{ $h['epargne'] > 0 ? number_format($h['epargne'], 0, ',', "\u{00A0}") : '—' }}
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-[#6B7280]">
                        {{ $h['revenu'] > 0 ? $taux . '%' : '—' }}
                    </td>
                    <td class="px-5 py-3 text-center text-xs {{ $sante['cls'] }}">{{ $sante['label'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-[#F8FAFC] border-t-2 border-[#E5E7EB] text-sm font-bold">
                <tr>
                    <td class="px-5 py-3 text-[#1F2937]">Total {{ $annee }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($totalRevenu, 0, ',', "\u{00A0}") }} FCFA</td>
                    <td class="px-5 py-3 text-right text-[#EF4444]">{{ number_format($totalDepenses, 0, ',', "\u{00A0}") }} FCFA</td>
                    <td class="px-5 py-3 text-right {{ ($totalRevenu - $totalDepenses) >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">
                        {{ number_format($totalRevenu - $totalDepenses, 0, ',', "\u{00A0}") }} FCFA
                    </td>
                    <td class="px-5 py-3 text-right text-[#006c49]">{{ number_format($totalEpargne, 0, ',', "\u{00A0}") }} FCFA</td>
                    <td class="px-5 py-3 text-center text-[#6B7280]">—</td>
                    <td class="px-5 py-3 text-center text-[#002452]">{{ $tauxEpargne }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartAnnuel');
    if (!ctx) return;

    const labels   = @json(array_column($historique, 'label'));
    const revenus  = @json(array_column($historique, 'revenu'));
    const depenses = @json(array_column($historique, 'depenses'));
    const epargne  = @json(array_column($historique, 'epargne'));

    window.chartAnnuel = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Revenus',  data: revenus,  backgroundColor: 'rgba(0,36,82,0.15)',   borderColor: '#002452', borderWidth: 1.5, borderRadius: 4, order: 3 },
                { label: 'Dépenses', data: depenses, backgroundColor: 'rgba(239,68,68,0.65)', borderColor: '#EF4444', borderWidth: 0,   borderRadius: 4, order: 2 },
                { label: 'Épargne',  data: epargne,  backgroundColor: 'rgba(0,108,73,0.75)',  borderColor: '#006c49', borderWidth: 0,   borderRadius: 4, order: 1 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => '  ' + c.dataset.label + ' : ' + c.parsed.y.toLocaleString('fr-FR') + ' FCFA' } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                y: { grid: { color: '#F3F4F6' }, ticks: { font: { size: 10 }, color: '#9CA3AF',
                    callback: v => v === 0 ? '0' : (v / 1000).toFixed(0) + 'k' }}
            }
        }
    });
});

</script>
@endpush

</x-layouts.app>
