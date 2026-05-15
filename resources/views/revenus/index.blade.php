<x-layouts.app title="Revenus" pageTitle="Revenus" pageSubtitle="Gestion du salaire et des gains variables" monthSelector>

{{-- ===== SÉLECTEUR MOIS ===== --}}
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div class="flex items-center gap-2">
        <a href="{{ route('revenus.index', ['mois' => $mois == 1 ? 12 : $mois-1, 'annee' => $mois == 1 ? $annee-1 : $annee]) }}"
           class="p-1.5 rounded-lg border border-[#E5E7EB] hover:bg-gray-50 transition-colors">
            <span class="material-symbols-outlined text-[#002452]">chevron_left</span>
        </a>
        <span class="text-base font-bold text-[#1F2937] min-w-32 text-center">
            {{ \Carbon\Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y') }}
        </span>
        <a href="{{ route('revenus.index', ['mois' => $mois == 12 ? 1 : $mois+1, 'annee' => $mois == 12 ? $annee+1 : $annee]) }}"
           class="p-1.5 rounded-lg border border-[#E5E7EB] hover:bg-gray-50 transition-colors">
            <span class="material-symbols-outlined text-[#002452]">chevron_right</span>
        </a>
    </div>
    <a href="{{ route('revenus.index') }}" class="text-xs text-[#006c49] font-semibold hover:underline">
        Mois courant →
    </a>
</div>

{{-- ===== KPI REVENUS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- Salaire fixe --}}
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280] text-lg">wallet</span>
            @if($variationSalaire !== null)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $variationSalaire >= 0 ? 'bg-[#d1fae5] text-[#065f46]' : 'bg-[#fee2e2] text-[#991b1b]' }}">
                    {{ $variationSalaire >= 0 ? '+' : '' }}{{ $variationSalaire }}%
                </span>
            @endif
        </div>
        <p class="kpi-label">Salaire fixe</p>
        <p class="kpi-value">{{ number_format((int)$budget->salaire_fixe, 0, ',', ' ') }} FCFA</p>
        @if($epargnesSalairePct > 0)
        <p class="text-[10px] text-[#006c49] font-semibold mt-1 flex items-center gap-1">
            <span class="material-symbols-outlined text-xs">savings</span>
            {{ $epargnesSalairePct }}% épargné → {{ number_format((int)$epargneSalaire, 0, ',', ' ') }} FCFA/mois
        </p>
        @endif
    </div>

    {{-- Salaire disponible (après épargne programmée) --}}
    <div class="kpi-card border-[#006c49]/30 bg-[#006c49]/5">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#006c49] text-lg">payments</span>
            @if($epargnesSalairePct > 0)
            <span class="text-[10px] bg-[#d1fae5] text-[#065f46] font-bold px-2 py-0.5 rounded-full">-{{ $epargnesSalairePct }}% épargne</span>
            @endif
        </div>
        <p class="kpi-label text-[#006c49]">Salaire dépensable</p>
        <p class="kpi-value text-[#006c49]">{{ number_format((int)$salaireDisponible, 0, ',', ' ') }} FCFA</p>
    </div>

    {{-- Réserve bonus/extras --}}
    <div class="kpi-card border-[#6366F1]/20 bg-[#6366F1]/5">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6366F1] text-lg">lock</span>
            <span class="text-[10px] bg-[#ede9fe] text-[#5b21b6] font-bold px-2 py-0.5 rounded-full">Réserve bonus</span>
        </div>
        <p class="kpi-label text-[#5b21b6]">Réserve bonus / extras</p>
        <p class="kpi-value text-[#5b21b6]">{{ number_format((int)$totalReserve, 0, ',', ' ') }} FCFA</p>
    </div>

    {{-- Total épargne : salaire + bonus réserve --}}
    <div class="kpi-card {{ ($epargneSalaire + $totalReserve) > 0 ? 'border-[#006c49]/40 bg-[#006c49]/5' : '' }}">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#006c49] text-lg">savings</span>
            <span class="text-[10px] bg-[#d1fae5] text-[#065f46] font-bold px-2 py-0.5 rounded-full">Total réserve</span>
        </div>
        <p class="kpi-label text-[#006c49]">Épargne totale du mois</p>
        <p class="kpi-value text-[#006c49]">{{ number_format((int)($epargneSalaire + $totalReserve), 0, ',', ' ') }} FCFA</p>
        @if($epargneSalaire > 0 && $totalReserve > 0)
        <p class="text-[10px] text-[#6B7280] mt-1">Salaire {{ number_format((int)$epargneSalaire, 0, ',', ' ') }} + Bonus {{ number_format((int)$totalReserve, 0, ',', ' ') }}</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-12 gap-4">

    {{-- ===== COLONNE GAUCHE ===== --}}
    <div class="col-span-12 lg:col-span-7 space-y-4">

        {{-- Formulaire salaire fixe --}}
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#002452]">wallet</span>
                Salaire fixe du mois
            </h3>
            <form method="POST" action="{{ route('revenus.salaire.update', $budget) }}" class="flex gap-3 items-end flex-wrap">
                @csrf
                @method('POST')
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Montant (FCFA)</label>
                    <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] focus-within:ring-2 focus-within:ring-blue-100 bg-white">
                        <span class="pl-3 text-xs font-bold text-[#6B7280] whitespace-nowrap select-none">FCFA</span>
                        <input type="number" name="salaire_fixe" min="0" step="1"
                               value="{{ old('salaire_fixe', (int)$budget->salaire_fixe) }}"
                               inputmode="numeric" pattern="[0-9]*"
                               class="flex-1 pr-3 py-2.5 text-sm bg-transparent outline-none"
                               placeholder="0" />
                    </div>
                </div>
                <button type="submit" class="btn-primary flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">save</span>
                    Enregistrer
                </button>
            </form>
            @error('salaire_fixe')
                <p class="text-[#EF4444] text-xs mt-2">{{ $message }}</p>
            @enderror

            {{-- Épargne programmée sur salaire --}}
            @if($epargnesSalairePct > 0 && $budget->salaire_fixe > 0)
            <div class="mt-4 p-3 bg-[#f0fdf4] border border-[#6ee7b7] rounded-xl flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#006c49] text-lg" style="font-variation-settings:'FILL' 1;">savings</span>
                    <div>
                        <p class="text-xs font-bold text-[#065f46]">Épargne programmée sur salaire fixe</p>
                        <p class="text-[11px] text-[#6B7280]">{{ $epargnesSalairePct }}% de votre salaire est automatiquement mis en réserve</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-headline font-bold text-[#006c49] text-base">{{ number_format((int)$epargneSalaire, 0, ',', ' ') }} FCFA</p>
                    <p class="text-[10px] text-[#6B7280]">Disponible : {{ number_format((int)$salaireDisponible, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
            @elseif($epargnesSalairePct === 0)
            <div class="mt-4 p-3 bg-[#FFFBEB] border border-[#FDE68A] rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-[#D97706] text-base flex-shrink-0">info</span>
                <p class="text-[11px] text-[#92400E]">
                    Aucune épargne programmée sur le salaire fixe.
                    <a href="{{ route('profil.index') }}" class="font-bold underline">Configurer dans les Paramètres →</a>
                </p>
            </div>
            @endif
        </div>

        {{-- Formulaire ajout revenu variable --}}
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-1 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#006c49]">add_circle</span>
                Ajouter un revenu variable
            </h3>
            <p class="text-xs text-[#6B7280] mb-4">Bonus, prime, freelance, vente... Les <strong>70%</strong> sont mis en réserve, les <strong>30%</strong> restants sont dépensables ce mois.</p>

            <form method="POST" action="{{ route('revenus.store') }}" x-data="{ montant: '', quota: 0, dispo: 0 }" class="space-y-4">
                @csrf
                <input type="hidden" name="mois"  value="{{ $mois }}" />
                <input type="hidden" name="annee" value="{{ $annee }}" />

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Type</label>
                        <select name="type" class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10">
                            <option value="bonus">Bonus / Prime</option>
                            <option value="extra">Gain extra</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Date</label>
                        <input type="date" name="date" value="{{ now()->format('Y-m-d') }}"
                               class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Montant brut (FCFA)</label>
                    <div class="flex items-center gap-2 border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] focus-within:ring-2 focus-within:ring-blue-100 bg-white">
                        <span class="pl-3 text-xs font-bold text-[#6B7280] whitespace-nowrap select-none">FCFA</span>
                        <input type="number" name="montant_brut" min="1" step="1"
                               x-model="montant"
                               @input="montant = Math.floor(montant); quota = Math.round(montant * 0.30); dispo = montant - quota"
                               inputmode="numeric" pattern="[0-9]*"
                               class="flex-1 pr-3 py-2.5 text-sm bg-transparent outline-none"
                               placeholder="Ex: 150 000" />
                    </div>
                </div>

                {{-- Visualisation quota en temps réel --}}
                <div x-show="montant > 0" class="grid grid-cols-2 gap-3 p-3 bg-[#F8FAFC] rounded-lg border border-[#E5E7EB]">
                    <div class="text-center">
                        <p class="text-[10px] font-bold uppercase text-[#006c49] tracking-wide">Dépensable (30%)</p>
                        <p class="font-headline font-bold text-sm text-[#006c49]" x-text="Number(quota).toLocaleString('fr-FR') + ' FCFA'"></p>
                    </div>
                    <div class="text-center border-l border-[#E5E7EB]">
                        <p class="text-[10px] font-bold uppercase text-[#5b21b6] tracking-wide">Réserve (70%)</p>
                        <p class="font-headline font-bold text-sm text-[#5b21b6]" x-text="Number(dispo).toLocaleString('fr-FR') + ' FCFA'"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Description (optionnel)</label>
                    <input type="text" name="description" placeholder="Ex: Prime de performance Q1"
                           class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10" />
                </div>

                <button type="submit" class="btn-secondary w-full flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">add</span>
                    Ajouter ce revenu
                </button>
            </form>
        </div>
    </div>

    {{-- ===== COLONNE DROITE : Liste des revenus ===== --}}
    <div class="col-span-12 lg:col-span-5">
        <div class="soft-card overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E5E7EB] bg-white flex justify-between items-center">
                <h3 class="font-headline text-base font-semibold text-[#1F2937]">Revenus du mois</h3>
                <span class="badge-blue">{{ $revenus->count() }} entrée(s)</span>
            </div>

            @if($revenus->isEmpty())
            <div class="p-8 text-center">
                <span class="material-symbols-outlined text-4xl text-[#E5E7EB]">payments</span>
                <p class="text-sm text-[#6B7280] mt-2">Aucun revenu variable ce mois.</p>
            </div>
            @else
            <div class="divide-y divide-[#E5E7EB]">
                @foreach($revenus as $revenu)
                <div class="p-4" x-data="{ debloquerOpen: false }">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-9 h-9 rounded-lg {{ $revenu->type === 'bonus' ? 'bg-[#dbeafe]' : 'bg-[#fef3c7]' }} flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-base {{ $revenu->type === 'bonus' ? 'text-[#1d4ed8]' : 'text-[#92400e]' }}">
                                    {{ $revenu->type === 'bonus' ? 'star' : 'bolt' }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-[#1F2937] truncate">
                                    {{ $revenu->description ?: ucfirst($revenu->type) }}
                                </p>
                                <p class="text-xs text-[#6B7280]">{{ $revenu->date->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-sm text-[#006c49]">+{{ number_format((int)$revenu->montant_brut, 0, ',', ' ') }} FCFA</p>
                            <form method="POST" action="{{ route('revenus.destroy', $revenu) }}" onsubmit="return confirm('Supprimer ce revenu ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-[10px] text-[#EF4444] hover:underline mt-0.5">Supprimer</button>
                            </form>
                        </div>
                    </div>

                    @if($revenu->quota_applique)
                    <div class="mt-2 ml-12 flex items-center gap-3 flex-wrap">
                        <span class="text-[10px] bg-[#d1fae5] text-[#065f46] font-bold px-2 py-0.5 rounded-full">
                            Dépensable (30%) : {{ number_format((int)$revenu->montant_quota, 0, ',', ' ') }} FCFA
                        </span>
                        <span class="text-[10px] bg-[#ede9fe] text-[#5b21b6] font-bold px-2 py-0.5 rounded-full">
                            Réserve (70%) : {{ number_format((int)$revenu->montant_dispo, 0, ',', ' ') }} FCFA
                        </span>
                        @if(optional($revenu->quotaLog)->reserve_restante > 0)
                        <button @click="debloquerOpen = !debloquerOpen"
                                class="text-[10px] text-[#002452] font-bold underline hover:text-[#006c49]">
                            Débloquer ({{ number_format((int)optional($revenu->quotaLog)->reserve_restante, 0, ',', ' ') }} FCFA dispo) →
                        </button>
                        @endif
                    </div>

                    {{-- Formulaire déblocage --}}
                    <div x-show="debloquerOpen" x-transition class="mt-3 ml-12 p-3 bg-[#faf5ff] border border-[#ede9fe] rounded-lg">
                        <form method="POST" action="{{ route('revenus.debloquer', $revenu) }}" class="space-y-2">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] font-bold text-[#6B7280] uppercase">Montant à débloquer</label>
                                    <input type="number" name="montant" min="1" step="1"
                                           max="{{ (int)(optional($revenu->quotaLog)->reserve_restante ?? 0) }}"
                                           inputmode="numeric" pattern="[0-9]*"
                                           placeholder="Ex: 5 000"
                                           class="w-full px-2 py-1.5 border border-[#ede9fe] rounded text-xs focus:outline-none focus:border-[#6366F1]" />
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-[#6B7280] uppercase">Justification</label>
                                    <input type="text" name="justification" minlength="10" placeholder="Raison..."
                                           class="w-full px-2 py-1.5 border border-[#ede9fe] rounded text-xs focus:outline-none focus:border-[#6366F1]" />
                                </div>
                            </div>
                            <button type="submit" class="text-xs bg-[#6366F1] text-white px-3 py-1.5 rounded font-semibold hover:bg-[#4f46e5] transition-colors">
                                Confirmer le déblocage
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

</x-layouts.app>
