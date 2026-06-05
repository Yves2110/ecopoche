<x-layouts.app title="Emprunts & Prêts" pageTitle="Emprunts & Prêts"
    pageSubtitle="Suivi des sommes dues et à recevoir — période budget : {{ $periodeLabelCourante ?? '' }}">

<div class="soft-card p-3 mb-4 flex items-start gap-2 border-[#E0E7FF] bg-[#F0F4FF]">
    <span class="material-symbols-outlined text-[#002452] text-lg">info</span>
    <p class="text-xs text-[#374151] leading-relaxed">
        Les opérations cochées « affecter au budget » sont rattachées à la <strong>période budgétaire</strong> de leur date
        (période en cours : <strong>{{ $periodeLabelCourante }}</strong>), et non au mois calendaire seul.
    </p>
</div>

@php
    $isEmprunt = $type === 'emprunt';
    $statsType = $isEmprunt ? $stats['emprunts'] : $stats['prets'];
    $libelle     = $isEmprunt ? 'emprunt' : 'pret';
    $libelleLong = $isEmprunt ? "Argent que je dois rembourser" : "Argent que l'on me doit";
    $couleurMain = $isEmprunt ? '#DC2626' : '#7C3AED';
@endphp

{{-- ===== ONGLETS TYPE ===== --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex bg-white border border-[#E5E7EB] rounded-lg overflow-hidden">
        <a href="{{ route('dettes.index', ['type' => 'emprunt']) }}"
           class="px-4 py-2 text-xs font-semibold transition-colors flex items-center gap-2 {{ $type === 'emprunt' ? 'bg-[#DC2626] text-white' : 'text-[#6B7280] hover:bg-gray-50' }}">
            <span class="material-symbols-outlined text-base">arrow_downward</span>
            Emprunts
        </a>
        <a href="{{ route('dettes.index', ['type' => 'pret']) }}"
           class="px-4 py-2 text-xs font-semibold transition-colors flex items-center gap-2 {{ $type === 'pret' ? 'bg-[#7C3AED] text-white' : 'text-[#6B7280] hover:bg-gray-50' }}">
            <span class="material-symbols-outlined text-base">arrow_upward</span>
            Prêts
        </a>
    </div>

    <div class="flex items-center gap-2">
        <select onchange="window.location.href=this.value"
                class="text-xs border border-[#E5E7EB] rounded-lg px-3 py-2 bg-white">
            <option value="{{ route('dettes.index', ['type' => $type]) }}" @if(!$statutFiltre) selected @endif>Tous les statuts</option>
            <option value="{{ route('dettes.index', ['type' => $type, 'statut' => 'actif']) }}" @if($statutFiltre==='actif') selected @endif>Actifs</option>
            <option value="{{ route('dettes.index', ['type' => $type, 'statut' => 'en_retard']) }}" @if($statutFiltre==='en_retard') selected @endif>En retard</option>
            <option value="{{ route('dettes.index', ['type' => $type, 'statut' => 'solde']) }}" @if($statutFiltre==='solde') selected @endif>Soldés</option>
        </select>
    </div>
</div>

{{-- ===== KPI ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined" style="color: {{ $couleurMain }}">{{ $isEmprunt ? 'arrow_downward' : 'arrow_upward' }}</span>
        </div>
        <p class="kpi-label">{{ $libelleLong }}</p>
        <p class="kpi-value" style="color: {{ $couleurMain }}">{{ number_format((int)$statsType['restant'], 0, ',', "\u{00A0}") }} FCFA</p>
    </div>
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">list_alt</span>
        </div>
        <p class="kpi-label">Opérations actives</p>
        <p class="kpi-value">{{ $statsType['count'] }}</p>
    </div>
    <div class="kpi-card {{ $statsType['retard'] > 0 ? 'border-[#EF4444]/30 bg-[#EF4444]/5' : '' }}">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined {{ $statsType['retard'] > 0 ? 'text-[#EF4444]' : 'text-[#6B7280]' }}">schedule</span>
        </div>
        <p class="kpi-label {{ $statsType['retard'] > 0 ? 'text-[#EF4444]' : '' }}">En retard</p>
        <p class="kpi-value {{ $statsType['retard'] > 0 ? 'text-[#EF4444]' : '' }}">{{ $statsType['retard'] }}</p>
    </div>
    <div class="kpi-card">
        <div class="flex justify-between items-start mb-3">
            <span class="material-symbols-outlined text-[#6B7280]">paid</span>
        </div>
        <p class="kpi-label">Total initial</p>
        <p class="kpi-value">{{ number_format((int)$statsType['total'], 0, ',', "\u{00A0}") }} FCFA</p>
    </div>
</div>

<div class="grid grid-cols-12 gap-4">

    {{-- ===== FORMULAIRE CRÉATION ===== --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color: {{ $couleurMain }}">add_circle</span>
                Nouveau {{ $libelle }}
            </h3>
            <form method="POST" action="{{ route('dettes.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}" />

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1">{{ $isEmprunt ? 'Prêteur' : 'Bénéficiaire' }}</label>
                    <input type="text" name="partie" required maxlength="120"
                           placeholder="Nom de la personne"
                           value="{{ old('partie') }}"
                           class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002452]/30">
                    @error('partie') <p class="text-xs text-[#EF4444] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1">Montant (FCFA)</label>
                    <input type="number" name="montant_initial" required min="1" step="1"
                           value="{{ old('montant_initial') }}"
                           class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002452]/30">
                    @error('montant_initial') <p class="text-xs text-[#EF4444] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1">Date opération</label>
                        <input type="date" name="date_operation" required
                               value="{{ old('date_operation', now()->format('Y-m-d')) }}"
                               class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1">Échéance</label>
                        <input type="date" name="date_echeance"
                               value="{{ old('date_echeance') }}"
                               class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1">Intérêts (% optionnel)</label>
                    <input type="number" name="interet_pct" min="0" max="100" step="0.01"
                           value="{{ old('interet_pct') }}"
                           class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1">Note</label>
                    <textarea name="note" rows="2" maxlength="500"
                              class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">{{ old('note') }}</textarea>
                </div>

                <label class="flex items-start gap-2 cursor-pointer p-3 bg-[#F8FAFC] rounded-lg border border-[#E5E7EB]">
                    <input type="checkbox" name="affecte_budget" value="1" {{ old('affecte_budget') ? 'checked' : '' }}
                           class="mt-0.5 rounded border-[#D1D5DB]">
                    <div>
                        <p class="text-xs font-semibold text-[#1F2937]">Affecter mon budget</p>
                        <p class="text-[11px] text-[#6B7280]">
                            @if($isEmprunt)
                                Crée un revenu dans la période budgétaire de la date d'opération.
                            @else
                                Crée une dépense dans la période budgétaire de la date d'opération.
                            @endif
                        </p>
                    </div>
                </label>

                <button type="submit"
                        class="w-full py-2.5 rounded-lg text-white font-semibold text-sm flex items-center justify-center gap-2"
                        style="background-color: {{ $couleurMain }}">
                    <span class="material-symbols-outlined text-base">save</span>
                    Enregistrer
                </button>
            </form>
        </div>
    </div>

    {{-- ===== LISTE ===== --}}
    <div class="col-span-12 lg:col-span-8 space-y-3">
        @forelse($dettes as $d)
            @php
                $statutBadge = match($d->statut) {
                    'solde'     => 'bg-[#006c49]/10 text-[#006c49]',
                    'en_retard' => 'bg-[#EF4444]/10 text-[#EF4444]',
                    default     => 'bg-[#3B82F6]/10 text-[#3B82F6]',
                };
                $statutLabel = match($d->statut) {
                    'solde'     => 'Soldé',
                    'en_retard' => 'En retard',
                    default     => 'Actif',
                };
            @endphp
            <div class="soft-card p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <a href="{{ route('dettes.show', $d) }}" class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-[#1F2937] truncate">{{ $d->partie }}</h4>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $statutBadge }}">{{ $statutLabel }}</span>
                        </div>
                        <p class="text-xs text-[#6B7280]">
                            {{ $d->date_operation->translatedFormat('d M Y') }}
                            @if($d->date_echeance)
                                · Échéance {{ $d->date_echeance->translatedFormat('d M Y') }}
                            @endif
                        </p>
                        @if($d->note)
                            <p class="text-xs text-[#6B7280] mt-1 italic line-clamp-1">{{ $d->note }}</p>
                        @endif
                    </a>
                    <div class="text-right shrink-0">
                        <p class="text-[10px] text-[#6B7280] uppercase font-semibold">Restant</p>
                        <p class="text-base font-bold" style="color: {{ $couleurMain }}">
                            {{ number_format((int)$d->montant_restant, 0, ',', "\u{00A0}") }}
                        </p>
                        <p class="text-[10px] text-[#9CA3AF]">/ {{ number_format((int)$d->montant_initial, 0, ',', "\u{00A0}") }} FCFA</p>
                    </div>
                </div>

                {{-- Barre de progression --}}
                <div class="w-full bg-[#F3F4F6] rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full transition-all"
                         style="width: {{ $d->pct_rembourse }}%; background-color: {{ $couleurMain }}"></div>
                </div>
                <p class="text-[10px] text-[#6B7280] mt-1">{{ $d->pct_rembourse }}% remboursé</p>

                {{-- Actions rapides --}}
                @if($d->statut !== 'solde')
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-[#F3F4F6]">
                    <button type="button"
                            onclick="ouvrirPaiement({{ $d->id }}, '{{ addslashes($d->partie) }}', {{ $d->montant_restant }}, '{{ $isEmprunt ? 'emprunt' : 'pret' }}')"
                            class="flex-1 py-2 rounded-lg text-white text-xs font-semibold flex items-center justify-center gap-1"
                            style="background-color: {{ $couleurMain }}">
                        <span class="material-symbols-outlined text-base">payments</span>
                        {{ $isEmprunt ? 'Rembourser' : 'Marquer reçu' }}
                    </button>
                    <a href="{{ route('dettes.show', $d) }}"
                       class="px-3 py-2 rounded-lg border border-[#E5E7EB] text-xs font-semibold text-[#6B7280] hover:bg-gray-50 flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        Détails
                    </a>
                </div>
                @else
                <div class="mt-3 pt-3 border-t border-[#F3F4F6] flex items-center justify-between">
                    <span class="text-xs text-[#006c49] font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span> Soldé
                    </span>
                    <a href="{{ route('dettes.show', $d) }}" class="text-xs text-[#6B7280] hover:underline">Voir détails</a>
                </div>
                @endif
            </div>
        @empty
            <div class="soft-card p-10 text-center">
                <span class="material-symbols-outlined text-5xl text-[#D1D5DB] mb-2">handshake</span>
                <p class="text-sm text-[#6B7280]">
                    Aucun {{ $libelle }} {{ $statutFiltre ? '('.$statutFiltre.')' : '' }} pour le moment.
                </p>
            </div>
        @endforelse
    </div>
</div>

@push('modals')
{{-- Modal au niveau body : voile sombre + panneau cyan/gris bien distinct du formulaire « Nouveau emprunt » --}}
<div id="modalPaiement" class="hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitre" aria-hidden="true">
    <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 z-0" style="background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(6px);" onclick="fermerPaiement()" aria-hidden="true"></div>

        <div id="modalPaiementPanel"
             class="relative z-10 w-full max-w-md rounded-2xl max-h-[min(90vh,640px)] flex flex-col shadow-[0_25px_60px_-12px_rgba(8,145,178,0.35)]"
             style="background-color: #E0F2FE; border: 2px solid #0891B2;"
             onclick="event.stopPropagation()">
            <div id="modalBandeau" class="px-3 py-2 text-center text-[11px] font-bold uppercase tracking-wide text-white shrink-0 rounded-t-2xl"
                 style="background-color: #0891B2;">
                Formulaire de remboursement
            </div>

            <div class="p-5 flex items-center justify-between shrink-0 border-b border-[#0891B2]/30"
                 style="background: linear-gradient(90deg, #BAE6FD 0%, #E2E8F0 100%);" id="modalHeader">
                <div class="flex items-center gap-3 min-w-0">
                    <span id="modalIconWrap" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-[#FEE2E2]">
                        <span class="material-symbols-outlined text-[#DC2626]" id="modalIcon">payments</span>
                    </span>
                    <div class="min-w-0">
                        <h3 class="font-headline font-bold text-[#0C4A6E] truncate" id="modalTitre">Enregistrer un paiement</h3>
                        <p class="text-xs text-[#475569] truncate" id="modalSubtitle"></p>
                    </div>
                </div>
                <button type="button" onclick="fermerPaiement()" class="p-2 rounded-lg hover:bg-white/60 text-[#0C4A6E] shrink-0" aria-label="Fermer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="formPaiement" method="POST" class="flex flex-col min-h-0 flex-1">
                @csrf

                <div class="p-5 space-y-3 overflow-y-auto min-h-0 flex-1" style="background-color: #CBD5E1;">
                    <div class="flex items-start gap-2 p-3 rounded-xl border border-[#0891B2]/25 text-[11px] text-[#334155] leading-snug"
                         style="background-color: #F0F9FF;">
                        <span class="material-symbols-outlined text-[#0891B2] text-base shrink-0">info</span>
                        <p id="modalBudgetTxt">Les montants sont enregistrés pour la dette sélectionnée. Cochez « Affecter mon budget » pour lier l'opération à la période budgétaire de la date.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0C4A6E] mb-1">Montant (FCFA)</label>
                        <input type="number" name="montant" id="modalMontant" required min="1" step="1"
                               class="w-full rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0891B2]/35"
                               style="border: 1px solid #0891B2;">
                        <p class="text-[11px] text-[#475569] mt-1">Maximum : <span id="modalRestant" class="font-semibold text-[#0C4A6E]"></span> FCFA</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="document.getElementById('modalMontant').value=Math.floor(document.getElementById('modalMontant').max/2)"
                                class="py-2 rounded-lg text-xs font-semibold text-[#334155] bg-white hover:bg-[#F0F9FF]"
                                style="border: 1px solid #94A3B8;">50 %</button>
                        <button type="button" onclick="document.getElementById('modalMontant').value=document.getElementById('modalMontant').max"
                                class="py-2 rounded-lg text-xs font-semibold text-[#334155] bg-white hover:bg-[#F0F9FF]"
                                style="border: 1px solid #94A3B8;">Tout solder</button>
                    </div>

                    <div class="relative z-20">
                        <label for="modalDate" class="block text-xs font-semibold text-[#0C4A6E] mb-1">Date du paiement</label>
                        <input type="date" name="date" id="modalDate" required value="{{ now()->format('Y-m-d') }}"
                               class="w-full rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0891B2]/35 cursor-pointer"
                               style="border: 1px solid #0891B2;">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#0C4A6E] mb-1">Note (optionnel)</label>
                        <input type="text" name="note" maxlength="255"
                               class="w-full rounded-lg px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#0891B2]/35"
                               style="border: 1px solid #0891B2;">
                    </div>

                    <label class="flex items-start gap-2 cursor-pointer p-3 rounded-xl bg-white shadow-sm"
                           style="border: 1px solid #0891B2;">
                        <input type="checkbox" name="affecte_budget" value="1" class="mt-0.5 rounded border-[#94A3B8]">
                        <div>
                            <p class="text-xs font-semibold text-[#0C4A6E]">Affecter mon budget</p>
                            <p class="text-[11px] text-[#64748B] leading-snug" id="modalBudgetHint"></p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-2 p-5 shrink-0 border-t border-[#94A3B8] rounded-b-2xl"
                     style="background-color: #CBD5E1;">
                    <button type="button" onclick="fermerPaiement()"
                            class="flex-1 py-2.5 rounded-lg text-sm font-semibold text-[#334155] bg-white hover:bg-[#F8FAFC]"
                            style="border: 1px solid #94A3B8;">Annuler</button>
                    <button type="submit" id="modalSubmit"
                            class="flex-1 py-2.5 rounded-lg text-white font-semibold text-sm shadow-md">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let ecopocheScrollY = 0;

function ouvrirPaiement(detteId, partie, restant, type) {
    const modal = document.getElementById('modalPaiement');
    const form  = document.getElementById('formPaiement');
    const submit = document.getElementById('modalSubmit');
    const panel = document.getElementById('modalPaiementPanel');
    const bandeau = document.getElementById('modalBandeau');
    const isEmprunt = type === 'emprunt';
    const couleur = isEmprunt ? '#DC2626' : '#7C3AED';
    const couleurBg = isEmprunt ? '#FEE2E2' : '#EDE9FE';
    const couleurBordure = isEmprunt ? '#0891B2' : '#7C3AED';

    form.action = '/dettes/' + detteId + '/remboursement';
    document.getElementById('modalTitre').textContent = (isEmprunt ? 'Rembourser' : 'Marquer reçu');
    document.getElementById('modalSubtitle').textContent = partie + ' · Restant ' + new Intl.NumberFormat('fr-FR').format(restant) + ' FCFA';
    document.getElementById('modalRestant').textContent = new Intl.NumberFormat('fr-FR').format(restant);
    document.getElementById('modalMontant').max = restant;
    document.getElementById('modalMontant').value = '';
    const dateInput = document.getElementById('modalDate');
    if (dateInput) {
        dateInput.value = new Date().toISOString().slice(0, 10);
        dateInput.disabled = false;
    }
    if (bandeau) {
        bandeau.textContent = isEmprunt ? 'Formulaire de remboursement' : 'Formulaire de paiement reçu';
        bandeau.style.backgroundColor = couleurBordure;
    }
    if (panel) {
        panel.style.borderColor = couleurBordure;
        panel.style.backgroundColor = isEmprunt ? '#E0F2FE' : '#F5F3FF';
    }
    const hint = document.getElementById('modalBudgetHint');
    if (hint) {
        hint.textContent = isEmprunt
            ? 'Crée une dépense dans la période budgétaire de la date.'
            : 'Crée un revenu dans la période budgétaire de la date.';
    }
    submit.style.backgroundColor = couleur;
    document.getElementById('modalIconWrap').style.backgroundColor = couleurBg;
    document.getElementById('modalIcon').style.color = couleur;

    ecopocheScrollY = window.scrollY;
    document.body.classList.add('ecopoche-modal-open');
    document.body.style.position = 'fixed';
    document.body.style.top = '-' + ecopocheScrollY + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';

    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    setTimeout(() => document.getElementById('modalMontant').focus(), 50);
}
function fermerPaiement() {
    const modal = document.getElementById('modalPaiement');
    if (!modal || modal.classList.contains('hidden')) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('ecopoche-modal-open');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    window.scrollTo(0, ecopocheScrollY);
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fermerPaiement();
});
</script>
@endpush

</x-layouts.app>
