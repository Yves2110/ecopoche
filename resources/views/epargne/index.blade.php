<x-layouts.app title="Épargne" pageTitle="Épargne" pageSubtitle="Objectifs & suivi mensuel"
    x-data="{ onglet: '{{ request('onglet', 'objectifs') }}' }">

@php
    $objMois  = $epargne ? (int)$epargne->objectif : 0;
    $reelMois = $epargne ? (int)$epargne->reel : 0;
    $defMois  = max(0, $objMois - $reelMois);
    $pctMois  = $objMois > 0 ? min(100, round($reelMois / $objMois * 100)) : 0;
    $totalCible  = (int) $objectifs->sum('montant_cible');
    $totalActuel = (int) $objectifs->sum('montant_actuel');
@endphp

{{-- ===== ONGLETS ===== --}}
<div class="flex gap-1 mb-5 bg-[#F8FAFC] border border-[#E5E7EB] p-1 rounded-xl w-fit">
    <button @click="onglet='objectifs'"
            :class="onglet==='objectifs' ? 'bg-white shadow text-[#002452] font-bold' : 'text-[#6B7280] hover:text-[#002452]'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-all">
        <span class="material-symbols-outlined text-base">flag</span>
        Objectifs d'épargne
        @if($objectifs->count())<span class="bg-[#002452] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $objectifs->count() }}</span>@endif
    </button>
    <button @click="onglet='suivi'"
            :class="onglet==='suivi' ? 'bg-white shadow text-[#002452] font-bold' : 'text-[#6B7280] hover:text-[#002452]'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-all">
        <span class="material-symbols-outlined text-base">calendar_month</span>
        Suivi mensuel
    </button>
</div>

{{-- ===== ONGLET 1 : OBJECTIFS ===== --}}
<div x-show="onglet==='objectifs'" x-transition>

    {{-- KPIs objectifs --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
        <div class="kpi-card">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined text-[#6B7280]">flag</span>
                <span class="badge-blue">{{ $objectifs->count() }} objectif(s)</span>
            </div>
            <p class="kpi-label">Cible totale</p>
            <p class="kpi-value">{{ number_format($totalCible, 0, ',', "\u{00A0}") }} FCFA</p>
        </div>
        <div class="kpi-card border-[#006c49]/30 bg-[#006c49]/5">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined text-[#006c49]">account_balance_wallet</span>
            </div>
            <p class="kpi-label text-[#006c49]">Total accumulé</p>
            <p class="kpi-value text-[#006c49]">{{ number_format($totalActuel, 0, ',', "\u{00A0}") }} FCFA</p>
        </div>
        <div class="kpi-card col-span-2 lg:col-span-1">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined text-[#6B7280]">check_circle</span>
                <span class="badge-green">Atteints</span>
            </div>
            <p class="kpi-label">Objectifs complétés</p>
            <p class="kpi-value">{{ $objectifs->where('atteint', true)->count() }} / {{ $objectifs->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4">

        {{-- Formulaire nouvel objectif --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="soft-card p-5">
                <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#006c49]">add_circle</span>
                    Nouvel objectif
                </h3>

                @if(session('success'))
                <div class="mb-4 p-3 rounded-lg bg-[#d1fae5] border border-[#006c49]/20 text-[#065f46] text-sm font-medium flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    {{ session('success') }}
                </div>
                @endif

                <form method="POST" action="{{ route('epargne.objectifs.store') }}" class="space-y-3"
                      x-data="{ cible: 0, actuel: 0 }">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Nom de l'objectif</label>
                        <input type="text" name="nom" placeholder="Ex: Voyage, Voiture, Urgences..."
                               value="{{ old('nom') }}"
                               class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" required />
                        @error('nom')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Montant cible (FCFA)</label>
                        <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] bg-white">
                            <span class="pl-3 text-xs font-bold text-[#6B7280] whitespace-nowrap">FCFA</span>
                            <input type="number" name="montant_cible" min="1" step="1" inputmode="numeric"
                                   x-model="cible"
                                   class="flex-1 pr-3 py-2.5 text-sm bg-transparent outline-none" placeholder="0" required />
                        </div>
                        @error('montant_cible')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Début</label>
                            <input type="date" name="date_debut" value="{{ now()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Échéance (opt.)</label>
                            <input type="date" name="date_fin"
                                   class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Icône</label>
                        <select name="icone" class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white">
                            <option value="savings">Épargne générale</option>
                            <option value="flight">Voyage</option>
                            <option value="directions_car">Voiture</option>
                            <option value="home">Maison</option>
                            <option value="school">Éducation</option>
                            <option value="medical_services">Santé / Urgences</option>
                            <option value="devices">Équipement</option>
                            <option value="celebration">Événement</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Couleur</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="couleur" value="#006c49"
                                   class="w-10 h-10 rounded-lg border border-[#E5E7EB] cursor-pointer p-1" />
                            <span class="text-xs text-[#6B7280]">Couleur de la barre de progression</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Note (optionnel)</label>
                        <textarea name="note" rows="2" placeholder="Pourquoi cet objectif ?"
                                  class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white resize-none"></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">add</span>
                        Créer l'objectif
                    </button>
                </form>
            </div>
        </div>

        {{-- Liste des objectifs --}}
        <div class="col-span-12 lg:col-span-8 space-y-3 mb-20 lg:mb-0">
            @forelse($objectifs as $obj)
            @php
                $pct = $obj->pourcentage;
                $restant = $obj->restant;
                $moisR = $obj->mois_restants;
                $mensuelSugg = ($moisR && $moisR > 0 && $restant > 0) ? (int) ceil($restant / $moisR) : null;
            @endphp
            <div class="soft-card p-5 {{ $obj->atteint ? 'border-[#006c49]/30 bg-[#006c49]/5' : '' }}"
                 x-data="{ verserOpen: false }">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: {{ $obj->couleur }}22">
                            <span class="material-symbols-outlined text-xl" style="color: {{ $obj->couleur }}; font-variation-settings:'FILL' 1;">{{ $obj->icone }}</span>
                        </div>
                        <div>
                            <h4 class="font-headline font-bold text-[#1F2937] text-sm flex items-center gap-2">
                                {{ $obj->nom }}
                                @if($obj->atteint)
                                    <span class="badge-green text-[10px]">Atteint</span>
                                @endif
                            </h4>
                            <p class="text-[11px] text-[#6B7280] mt-0.5">
                                Depuis {{ $obj->date_debut->translatedFormat('d M Y') }}
                                @if($obj->date_fin)
                                    · Échéance {{ $obj->date_fin->translatedFormat('d M Y') }}
                                    @if($moisR !== null)
                                        <span class="{{ $moisR <= 1 ? 'text-[#EF4444] font-bold' : '' }}">
                                            ({{ $moisR }} mois restant{{ $moisR > 1 ? 's' : '' }})
                                        </span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-sm text-[#1F2937]">{{ number_format((int)$obj->montant_actuel, 0, ',', "\u{00A0}") }} FCFA</p>
                        <p class="text-[11px] text-[#6B7280]">sur {{ number_format((int)$obj->montant_cible, 0, ',', "\u{00A0}") }} FCFA</p>
                    </div>
                </div>

                {{-- Barre de progression --}}
                <div class="mb-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-semibold" style="color: {{ $obj->couleur }}">{{ $pct }}%</span>
                        @if(!$obj->atteint)
                            <span class="text-[#6B7280]">Reste : {{ number_format($restant, 0, ',', "\u{00A0}") }} FCFA</span>
                        @endif
                    </div>
                    <div class="h-3 bg-[#E5E7EB] rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700"
                             style="width: {{ $pct }}%; background: {{ $obj->couleur }}"></div>
                    </div>
                </div>

                @if($mensuelSugg)
                <p class="text-[11px] text-[#6B7280] mb-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm text-[#F59E0B]">lightbulb</span>
                    Pour atteindre l'objectif à temps : épargner environ
                    <strong class="text-[#002452]">{{ number_format($mensuelSugg, 0, ',', "\u{00A0}") }} FCFA/mois</strong>
                </p>
                @endif

                @if($obj->note)
                <p class="text-xs text-[#6B7280] italic mb-3 border-l-2 border-[#E5E7EB] pl-2">{{ $obj->note }}</p>
                @endif

                {{-- Actions --}}
                @if(!$obj->atteint)
                <div class="flex items-center gap-2 flex-wrap">
                    <button @click="verserOpen = !verserOpen"
                            class="flex items-center gap-1.5 text-xs bg-[#002452] text-white px-3 py-1.5 rounded-lg font-semibold hover:bg-[#003580] transition-colors">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Verser un montant
                    </button>
                    <form method="POST" action="{{ route('epargne.objectifs.destroy', $obj) }}"
                          onsubmit="return confirm('Supprimer cet objectif ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-[#EF4444] hover:underline font-semibold">Supprimer</button>
                    </form>
                </div>

                {{-- Formulaire versement --}}
                <div x-show="verserOpen" x-transition class="mt-3 p-3 bg-[#F8FAFC] border border-[#E5E7EB] rounded-lg">
                    <form method="POST" action="{{ route('epargne.objectifs.verser', $obj) }}"
                          class="flex gap-2 items-end">
                        @csrf
                        <div class="flex-1">
                            <label class="text-[10px] font-bold text-[#6B7280] uppercase mb-1 block">Montant à verser (FCFA)</label>
                            <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden bg-white focus-within:border-[#002452]">
                                <span class="pl-3 text-xs font-bold text-[#6B7280]">FCFA</span>
                                <input type="number" name="montant" min="1" step="1" inputmode="numeric"
                                       max="{{ $restant }}" placeholder="0"
                                       class="flex-1 pr-3 py-2 text-sm bg-transparent outline-none" required />
                            </div>
                        </div>
                        <button type="submit" class="btn-primary text-sm py-2 px-4">Valider</button>
                    </form>
                </div>
                @else
                <form method="POST" action="{{ route('epargne.objectifs.destroy', $obj) }}"
                      onsubmit="return confirm('Archiver cet objectif atteint ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-[#6B7280] hover:underline">Archiver</button>
                </form>
                @endif
            </div>
            @empty
            <div class="soft-card p-10 text-center">
                <span class="material-symbols-outlined text-5xl text-[#E5E7EB]">savings</span>
                <p class="text-sm text-[#6B7280] mt-3">Aucun objectif d'épargne défini.</p>
                <p class="text-xs text-[#9CA3AF] mt-1">Créez votre premier objectif dans le formulaire.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===== ONGLET 2 : SUIVI MENSUEL ===== --}}
<div x-show="onglet==='suivi'" x-transition>

    {{-- KPIs suivi mois courant --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="kpi-card col-span-2 lg:col-span-1">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined text-[#6B7280]">savings</span>
                <span class="badge-blue">{{ \Carbon\Carbon::createFromDate($annee, $mois, 1)->translatedFormat('M Y') }}</span>
            </div>
            <p class="kpi-label">Objectif du mois</p>
            <p class="kpi-value">{{ number_format($objMois, 0, ',', "\u{00A0}") }} FCFA</p>
        </div>
        <div class="kpi-card {{ $reelMois >= $objMois && $objMois > 0 ? 'border-[#006c49]/30 bg-[#006c49]/5' : '' }}">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined {{ $reelMois >= $objMois && $objMois > 0 ? 'text-[#006c49]' : 'text-[#6B7280]' }}">account_balance_wallet</span>
            </div>
            <p class="kpi-label {{ $reelMois >= $objMois && $objMois > 0 ? 'text-[#006c49]' : '' }}">Réel épargné</p>
            <p class="kpi-value {{ $reelMois >= $objMois && $objMois > 0 ? 'text-[#006c49]' : '' }}">{{ number_format($reelMois, 0, ',', "\u{00A0}") }} FCFA</p>
        </div>
        <div class="kpi-card {{ $defMois > 0 ? 'border-[#EF4444]/30 bg-[#EF4444]/5' : '' }}">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined {{ $defMois > 0 ? 'text-[#EF4444]' : 'text-[#6B7280]' }}">trending_down</span>
            </div>
            <p class="kpi-label {{ $defMois > 0 ? 'text-[#EF4444]' : '' }}">Déficit</p>
            <p class="kpi-value {{ $defMois > 0 ? 'text-[#EF4444]' : 'text-[#006c49]' }}">
                @if($defMois > 0)
                    − {{ number_format($defMois, 0, ',', "\u{00A0}") }} FCFA
                @else
                    <span class="flex items-center gap-1"><span class="material-symbols-outlined text-base">check_circle</span> Aucun</span>
                @endif
            </p>
        </div>
        <div class="kpi-card">
            <div class="flex justify-between items-start mb-3">
                <span class="material-symbols-outlined text-[#6B7280]">timeline</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $tauxRealisation >= 80 ? 'bg-[#d1fae5] text-[#065f46]' : ($tauxRealisation >= 50 ? 'bg-[#fef3c7] text-[#92400e]' : 'bg-[#fee2e2] text-[#991b1b]') }}">
                    12 mois
                </span>
            </div>
            <p class="kpi-label">Taux réalisation</p>
            <p class="kpi-value">{{ $tauxRealisation }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4">

        {{-- Formulaire saisie mensuelle --}}
        <div class="col-span-12 lg:col-span-4 space-y-4">
            <div class="flex items-center justify-between soft-card px-4 py-3">
                <a href="{{ route('epargne.index', ['mois'=>$mois==1?12:$mois-1,'annee'=>$mois==1?$annee-1:$annee,'onglet'=>'suivi']) }}"
                   class="p-1.5 rounded-lg border border-[#E5E7EB] hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[#002452]">chevron_left</span>
                </a>
                <span class="text-sm font-bold text-[#1F2937]">
                    {{ \Carbon\Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y') }}
                </span>
                <a href="{{ route('epargne.index', ['mois'=>$mois==12?1:$mois+1,'annee'=>$mois==12?$annee+1:$annee,'onglet'=>'suivi']) }}"
                   class="p-1.5 rounded-lg border border-[#E5E7EB] hover:bg-gray-50">
                    <span class="material-symbols-outlined text-[#002452]">chevron_right</span>
                </a>
            </div>

            <div class="soft-card p-5">
                <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#002452]">edit_note</span>
                    Saisir l'épargne du mois
                </h3>

                <form method="POST" action="{{ route('epargne.mensuel.update', $budget) }}" class="space-y-4"
                      x-data="{ objectif: {{ $objMois }}, reel: {{ $reelMois }} }">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Objectif mensuel (FCFA)</label>
                        <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] bg-white">
                            <span class="pl-3 text-xs font-bold text-[#6B7280]">FCFA</span>
                            <input type="number" name="objectif" min="0" step="1" inputmode="numeric"
                                   x-model="objectif"
                                   class="flex-1 pr-3 py-2.5 text-sm bg-transparent outline-none" placeholder="0" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Montant réellement épargné (FCFA)</label>
                        <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] bg-white">
                            <span class="pl-3 text-xs font-bold text-[#6B7280]">FCFA</span>
                            <input type="number" name="reel" min="0" step="1" inputmode="numeric"
                                   x-model="reel"
                                   class="flex-1 pr-3 py-2.5 text-sm bg-transparent outline-none" placeholder="0" />
                        </div>
                    </div>

                    {{-- Aperçu temps réel --}}
                    <div x-show="objectif > 0" class="p-3 rounded-lg bg-[#F8FAFC] border border-[#E5E7EB] space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-[#6B7280] font-semibold">Progression</span>
                            <span class="font-bold" :class="reel >= objectif ? 'text-[#006c49]' : 'text-[#F59E0B]'"
                                  x-text="objectif > 0 ? Math.min(100, Math.round(reel/objectif*100)) + '%' : '—'"></span>
                        </div>
                        <div class="h-2 bg-[#E5E7EB] rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all"
                                 :class="reel >= objectif ? 'bg-[#006c49]' : (reel/objectif >= 0.5 ? 'bg-[#F59E0B]' : 'bg-[#EF4444]')"
                                 :style="'width:' + (objectif > 0 ? Math.min(100, Math.round(reel/objectif*100)) : 0) + '%'">
                            </div>
                        </div>
                        <template x-if="objectif > reel">
                            <p class="text-[10px] text-[#EF4444] font-semibold"
                               x-text="'Déficit : − ' + Number(objectif - reel).toLocaleString('fr-FR') + ' FCFA'"></p>
                        </template>
                        <template x-if="reel >= objectif && objectif > 0">
                            <p class="text-[10px] text-[#006c49] font-semibold">Objectif atteint !</p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Note (optionnel)</label>
                        <textarea name="analyse" rows="2" placeholder="Ex: Moins épargné à cause des imprévus..."
                                  class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white resize-none">{{ $epargne?->analyse }}</textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">save</span>
                        Enregistrer
                    </button>
                </form>
            </div>
        </div>

        {{-- Graphique + tableau historique --}}
        <div class="col-span-12 lg:col-span-8 space-y-4">
            <div class="soft-card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-headline text-base font-semibold text-[#1F2937]">Historique 12 mois</h3>
                    <div class="flex items-center gap-4 text-xs text-[#6B7280]">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#002452]/20 inline-block"></span>Objectif</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#006c49] inline-block"></span>Réel</span>
                    </div>
                </div>
                <div class="relative h-52">
                    <canvas id="chartEpargne"></canvas>
                </div>
            </div>

            <div class="soft-card overflow-hidden mb-20 lg:mb-0">
                <div class="px-5 py-4 border-b border-[#E5E7EB] bg-white">
                    <h3 class="font-headline text-sm font-semibold text-[#1F2937]">Détail mensuel</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-[#F8FAFC] text-[10px] text-[#6B7280] border-b border-[#E5E7EB] uppercase font-semibold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Mois</th>
                                <th class="px-5 py-3 text-right">Objectif</th>
                                <th class="px-5 py-3 text-right">Réel</th>
                                <th class="px-5 py-3 text-right">Déficit</th>
                                <th class="px-5 py-3 text-center">Prog.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] text-sm">
                            @foreach(array_reverse($historique) as $h)
                            @php $hPct = $h['objectif'] > 0 ? min(100, round($h['reel']/$h['objectif']*100)) : 0; @endphp
                            <tr class="hover:bg-gray-50 {{ $h['actif'] ? 'bg-[#002452]/5' : '' }}">
                                <td class="px-5 py-3 font-semibold text-[#1F2937]">
                                    {{ $h['label'] }}
                                    @if($h['actif'])<span class="badge-blue ml-1">Actif</span>@endif
                                </td>
                                <td class="px-5 py-3 text-right text-[#6B7280]">
                                    {{ $h['objectif'] > 0 ? number_format($h['objectif'], 0, ',', "\u{00A0}") : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right font-semibold {{ $h['reel'] >= $h['objectif'] && $h['objectif'] > 0 ? 'text-[#006c49]' : 'text-[#1F2937]' }}">
                                    {{ $h['reel'] > 0 ? number_format($h['reel'], 0, ',', "\u{00A0}") : '—' }}
                                </td>
                                <td class="px-5 py-3 text-right {{ $h['deficit'] > 0 ? 'text-[#EF4444] font-semibold' : 'text-[#6B7280]' }}">
                                    {{ $h['deficit'] > 0 ? '− '.number_format($h['deficit'], 0, ',', "\u{00A0}") : '—' }}
                                </td>
                                <td class="px-5 py-3">
                                    @if($h['objectif'] > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-[#E5E7EB] rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $hPct>=100?'bg-[#006c49]':($hPct>=50?'bg-[#F59E0B]':'bg-[#EF4444]') }}"
                                                 style="width:{{ $hPct }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-[#6B7280] w-8 text-right">{{ $hPct }}%</span>
                                    </div>
                                    @else <span class="text-xs text-[#6B7280]">—</span> @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#F8FAFC] border-t-2 border-[#E5E7EB] text-sm font-bold">
                            <tr>
                                <td class="px-5 py-3 text-[#1F2937]">Total 12 mois</td>
                                <td class="px-5 py-3 text-right">{{ number_format((int)$totalObjectif, 0, ',', "\u{00A0}") }} FCFA</td>
                                <td class="px-5 py-3 text-right text-[#006c49]">{{ number_format((int)$totalReel, 0, ',', "\u{00A0}") }} FCFA</td>
                                <td class="px-5 py-3 text-right {{ $totalDeficit > 0 ? 'text-[#EF4444]' : 'text-[#006c49]' }}">
                                    {{ $totalDeficit > 0 ? '− '.number_format((int)$totalDeficit, 0, ',', "\u{00A0}") : '0' }} {{ $totalDeficit > 0 ? 'FCFA' : '' }}
                                </td>
                                <td class="px-5 py-3 text-center text-[#002452]">{{ $tauxRealisation }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartEpargne');
    if (!ctx) return;

    const labels    = @json(array_column($historique, 'label'));
    const objectifs = @json(array_column($historique, 'objectif'));
    const reels     = @json(array_column($historique, 'reel'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Objectif',
                    data: objectifs,
                    backgroundColor: 'rgba(0,36,82,0.12)',
                    borderColor: '#002452',
                    borderWidth: 1.5,
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Réel',
                    data: reels,
                    backgroundColor: 'rgba(0,108,73,0.7)',
                    borderColor: '#006c49',
                    borderWidth: 0,
                    borderRadius: 4,
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '  ' + ctx.dataset.label + ' : ' + ctx.parsed.y.toLocaleString('fr-FR') + ' FCFA'
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                y: { grid: { color: '#F3F4F6' }, ticks: {
                    font: { size: 10 }, color: '#9CA3AF',
                    callback: v => v === 0 ? '0' : (v / 1000).toFixed(0) + 'k'
                }}
            }
        }
    });
});
</script>
@endpush

</x-layouts.app>
