<x-layouts.app title="Dépenses" pageTitle="Dépenses" pageSubtitle="Suivi journalier, hebdomadaire et mensuel"
    monthSelector :period-mois="$mois" :period-annee="$annee" :periode-label="$periodeLabel" period-route="depenses.index">

<x-period-banners
    :est-periode-passee="$estPeriodePassee"
    :est-periode-future="$estPeriodeFuture"
    :est-periode-courante="$estPeriodeCourante"
    :periode-label="$periodeLabel" />

{{-- ===== BARRE NAVIGATION VUE + PÉRIODE ===== --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">

    <div class="flex flex-wrap items-center gap-2">
        {{-- Sélecteur vue --}}
        <div class="flex bg-white border border-[#E5E7EB] rounded-lg overflow-hidden">
            @foreach(['mois' => 'Période', 'semaine' => 'Semaine', 'jour' => 'Jour'] as $v => $label)
            <a href="{{ route('depenses.index', ['mois' => $mois, 'annee' => $annee, 'vue' => $v]) }}"
               class="px-4 py-2 text-xs font-semibold transition-colors {{ $vue === $v ? 'bg-[#002452] text-white' : 'text-[#6B7280] hover:bg-gray-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <a href="{{ route('profil.recurrences.index', ['type' => 'depense']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-[#002452]/25 bg-[#F0F4FF] text-[#002452] text-xs font-semibold hover:bg-[#002452]/10 transition-colors"
           title="Loyer, abonnements… générés automatiquement chaque mois">
            <span class="material-symbols-outlined text-base">event_repeat</span>
            Dépenses récurrentes
        </a>
    </div>

    <x-period-nav :mois="$mois" :annee="$annee" :periode-label="$periodeLabel"
                  route-name="depenses.index" :route-params="['vue' => $vue]" :show-current-link="true" />
</div>

{{-- ===== KPI ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">receipt_long</span>
            <span class="badge-yellow">Période</span>
        </div>
        <p class="kpi-label">Total dépensé</p>
        <p class="kpi-value">{{ number_format((int)$totalMois, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">today</span>
            <span class="badge-blue">Vue</span>
        </div>
        <p class="kpi-label">{{ $vue === 'jour' ? 'Jour' : ($vue === 'semaine' ? 'Semaine' : 'Vue') }}</p>
        <p class="kpi-value">{{ number_format((int)$totalVue, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">category</span>
        </div>
        <p class="kpi-label">Catégories actives</p>
        <p class="kpi-value">{{ $parCategorie->count() }}</p>
    </div>
    <div class="kpi-card {{ $soldeRestant >= 0 ? 'border-[#006c49]/30 bg-[#006c49]/5' : 'border-[#EF4444]/30 bg-[#EF4444]/5' }}">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined {{ $soldeRestant >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">account_balance</span>
        </div>
        <p class="kpi-label {{ $soldeRestant >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">Solde restant</p>
        <p class="kpi-value {{ $soldeRestant >= 0 ? 'text-[#006c49]' : 'text-[#EF4444]' }}">{{ number_format((int)$soldeRestant, 0, ',', "\u{00A0}") }} FCFA</p>
    </div>
</div>

<div class="grid grid-cols-12 gap-4">

    {{-- ===== FORMULAIRE SAISIE RAPIDE ===== --}}
    <div class="col-span-12 lg:col-span-4 space-y-4">
        @if(!($estPeriodePassee ?? false) && !($estPeriodeFuture ?? false))
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#EF4444]">add_circle</span>
                Saisie rapide
            </h3>
            <form method="POST" action="{{ route('depenses.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="mois"  value="{{ $mois }}" />
                <input type="hidden" name="annee" value="{{ $annee }}" />

                {{-- Montant --}}
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Montant (FCFA)</label>
                    <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] focus-within:ring-2 focus-within:ring-blue-100 bg-white">
                        <span class="pl-3 text-xs font-bold text-[#6B7280] whitespace-nowrap select-none">FCFA</span>
                        <input type="number" name="montant" min="1" step="1" inputmode="numeric" pattern="[0-9]*"
                               required placeholder="0"
                               class="flex-1 pr-3 py-2.5 text-sm bg-transparent outline-none" />
                    </div>
                    @error('montant')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Catégorie --}}
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Catégorie</label>
                    <select name="categorie_id" required
                            class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white">
                        <option value="">-- Choisir --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('categorie_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nom }}
                        </option>
                        @endforeach
                    </select>
                    @error('categorie_id')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Date</label>
                    <input type="date" name="date"
                           value="{{ old('date', ($estPeriodeCourante ?? true) ? now()->format('Y-m-d') : $finPeriode->format('Y-m-d')) }}"
                           min="{{ $debutPeriode->format('Y-m-d') }}"
                           max="{{ $finPeriode->format('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Note (optionnel)</label>
                    <input type="text" name="note" placeholder="Ex: Marché central"
                           class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                </div>

                {{-- Imprévue --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="imprevue" value="1"
                           class="w-4 h-4 rounded border-[#E5E7EB] text-[#EF4444]" />
                    <span class="text-sm text-[#6B7280]">Dépense imprévue</span>
                </label>

                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">save</span>
                    Enregistrer
                </button>
            </form>
        </div>

        <div class="soft-card p-5">
            <h3 class="font-headline text-sm font-semibold text-[#1F2937] mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-[#002452]">upload_file</span>
                Import CSV
            </h3>
            <p class="text-[10px] text-[#6B7280] mb-3">Format : Date;Catégorie;Désignation;Montant;Imprévue (comme l'export rapports).</p>
            <form method="POST" action="{{ route('depenses.import.csv') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="hidden" name="mois" value="{{ $mois }}" />
                <input type="hidden" name="annee" value="{{ $annee }}" />
                <input type="file" name="csv" accept=".csv,text/csv" required
                       class="w-full text-xs file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-[#002452] file:text-white" />
                @error('csv')<p class="text-[#EF4444] text-xs">{{ $message }}</p>@enderror
                <button type="submit" class="w-full py-2 text-xs font-semibold border border-[#E5E7EB] rounded-lg hover:bg-gray-50">Importer pour cette période</button>
            </form>
            @if(session('import_errors') && count(session('import_errors')))
            <ul class="mt-2 text-[10px] text-[#92400e] space-y-0.5 max-h-24 overflow-y-auto">
                @foreach(array_slice(session('import_errors'), 0, 8) as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
            @endif
            <p class="text-[10px] text-[#9CA3AF] mt-2">
                <a href="{{ route('profil.recurrences.index') }}" class="text-[#002452] hover:underline">Gérer les récurrences</a>
            </p>
        </div>
        @endif

        {{-- ===== RÉPARTITION PAR CATÉGORIE ===== --}}
        <div class="soft-card p-5">
            <h3 class="font-headline text-sm font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-base text-[#002452]">donut_large</span>
                Répartition de la période
            </h3>
            @if($parCategorie->isEmpty())
                <p class="text-xs text-[#6B7280] text-center py-4">Aucune dépense sur cette période.</p>
            @else
            <div class="space-y-3">
                @foreach($parCategorie as $catId => $data)
                @php
                    $cat      = $data['categorie'];
                    $total    = (int) $data['total'];
                    $plafond  = $cat?->plafond_mensuel ? (int)$cat->plafond_mensuel : null;
                    $pct      = $plafond ? min(100, round(($total / $plafond) * 100)) : null;
                    $couleur  = $cat?->couleur ?? '#6B7280';
                    $alerte   = $pct !== null && $pct >= 80;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm" style="color: {{ $couleur }}">{{ $cat?->icone ?? 'category' }}</span>
                            <span class="text-xs font-semibold text-[#1F2937]">{{ $cat?->nom ?? 'Inconnu' }}</span>
                            @if($alerte)
                                <span class="badge-red">{{ $pct }}%</span>
                            @endif
                        </div>
                        <span class="text-xs font-bold text-[#1F2937]">{{ number_format($total, 0, ',', "\u{00A0}") }}</span>
                    </div>
                    @if($plafond)
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill {{ $pct >= 100 ? 'bg-[#EF4444]' : ($pct >= 80 ? 'bg-[#F59E0B]' : 'bg-[#006c49]') }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[9px] text-[#6B7280] mt-0.5">
                        {{ number_format($total, 0, ',', "\u{00A0}") }} / {{ number_format($plafond, 0, ',', "\u{00A0}") }} FCFA
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ===== LISTE DES DÉPENSES ===== --}}
    <div class="col-span-12 lg:col-span-8">
        <div class="soft-card overflow-hidden mb-20 lg:mb-0">
            <div class="px-5 py-4 border-b border-[#E5E7EB] bg-white flex items-center justify-between flex-wrap gap-2">
                <h3 class="font-headline text-base font-semibold text-[#1F2937]">
                    @if($vue === 'jour') Dépenses du jour
                    @elseif($vue === 'semaine') Dépenses de la semaine
                    @else Dépenses du mois
                    @endif
                    <span class="text-[#6B7280] font-normal text-sm">({{ $depenses->count() }})</span>
                </h3>
                <span class="font-headline font-bold text-base text-[#EF4444]">
                    − {{ number_format((int)$totalVue, 0, ',', "\u{00A0}") }} FCFA
                </span>
            </div>

            @if($depenses->isEmpty())
            <div class="p-10 text-center">
                <span class="material-symbols-outlined text-5xl text-[#E5E7EB]">receipt_long</span>
                <p class="text-sm text-[#6B7280] mt-2">Aucune dépense enregistrée.</p>
            </div>
            @else
            {{-- Groupement par date --}}
            @foreach($depenses->groupBy(fn($d) => $d->date->format('Y-m-d')) as $dateStr => $groupe)
            @php $dateCarbon = \Carbon\Carbon::parse($dateStr); @endphp
            <div class="border-b border-[#E5E7EB] last:border-0">
                <div class="px-5 py-2 bg-[#F8FAFC] flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[#6B7280] uppercase tracking-wide">
                        {{ $dateCarbon->isToday() ? "Aujourd'hui" : ($dateCarbon->isYesterday() ? 'Hier' : $dateCarbon->translatedFormat('l d M')) }}
                    </span>
                    <span class="text-xs font-bold text-[#EF4444]">
                        − {{ number_format((int)$groupe->sum('montant'), 0, ',', "\u{00A0}") }} FCFA
                    </span>
                </div>
                @foreach($groupe as $depense)
                <div class="px-5 py-3 hover:bg-gray-50 transition-colors" x-data="{ editing: false }">
                    <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background-color: {{ $depense->categorie?->couleur ?? '#6B7280' }}20">
                            <span class="material-symbols-outlined text-sm"
                                  style="color: {{ $depense->categorie?->couleur ?? '#6B7280' }}">
                                {{ $depense->categorie?->icone ?? 'category' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-[#1F2937]">
                                {{ $depense->note ?: ($depense->categorie?->nom ?? 'Dépense') }}
                                @if($depense->imprevue)
                                    <span class="badge-red ml-1">Imprévue</span>
                                @endif
                            </p>
                            <p class="text-xs text-[#6B7280]">{{ $depense->categorie?->nom }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-[#EF4444]">
                            − {{ number_format((int)$depense->montant, 0, ',', "\u{00A0}") }} FCFA
                        </span>
                        @if(!($estPeriodePassee ?? false))
                        <button type="button" @click="editing = !editing"
                                class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-[#EEF2FF] text-[#6B7280] hover:text-[#002452] transition-colors"
                                title="Modifier">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </button>
                        <form method="POST" action="{{ route('depenses.destroy', $depense) }}"
                              onsubmit="return confirm('Supprimer cette dépense ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-[#fee2e2] text-[#6B7280] hover:text-[#EF4444] transition-colors">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </form>
                        @endif
                    </div>
                    </div>
                    @if(!($estPeriodePassee ?? false))
                    <div x-show="editing" x-cloak class="mt-3 pt-3 border-t border-[#F3F4F6]">
                        <form method="POST" action="{{ route('depenses.update', $depense) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @csrf @method('PUT')
                            <div>
                                <label class="block text-[10px] font-semibold text-[#6B7280] uppercase mb-1">Montant</label>
                                <input type="number" name="montant" value="{{ $depense->montant }}" min="1" required
                                       class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-[#6B7280] uppercase mb-1">Catégorie</label>
                                <select name="categorie_id" required class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm bg-white">
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected($cat->id === $depense->categorie_id)>{{ $cat->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-[#6B7280] uppercase mb-1">Date</label>
                                <input type="date" name="date" value="{{ $depense->date->format('Y-m-d') }}" required
                                       class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-[#6B7280] uppercase mb-1">Note</label>
                                <input type="text" name="note" value="{{ $depense->note }}"
                                       class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm" />
                            </div>
                            <div class="sm:col-span-2 flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs text-[#6B7280]">
                                    <input type="checkbox" name="imprevue" value="1" @checked($depense->imprevue) class="rounded" />
                                    Dépense imprévue
                                </label>
                                <div class="flex gap-2">
                                    <button type="button" @click="editing = false" class="px-3 py-1.5 text-xs border border-[#E5E7EB] rounded-lg">Annuler</button>
                                    <button type="submit" class="btn-primary text-xs px-3 py-1.5">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
            @endif
        </div>
    </div>
</div>

</x-layouts.app>
