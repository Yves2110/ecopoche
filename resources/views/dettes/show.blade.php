<x-layouts.app title="Détail {{ ucfirst($dette->type) }}" pageTitle="{{ ucfirst($dette->type) }} - {{ $dette->partie }}" pageSubtitle="Suivi détaillé de l'opération">

@php
    $isEmprunt = $dette->type === 'emprunt';
    $couleurMain = $isEmprunt ? '#DC2626' : '#7C3AED';
    $statutBadge = match($dette->statut) {
        'solde'     => 'bg-[#006c49]/10 text-[#006c49]',
        'en_retard' => 'bg-[#EF4444]/10 text-[#EF4444]',
        default     => 'bg-[#3B82F6]/10 text-[#3B82F6]',
    };
    $statutLabel = match($dette->statut) {
        'solde'     => 'Soldé',
        'en_retard' => 'En retard',
        default     => 'Actif',
    };
@endphp

{{-- Bouton retour --}}
<div class="mb-4">
    <a href="{{ route('dettes.index', ['type' => $dette->type]) }}"
       class="inline-flex items-center gap-1 text-xs text-[#6B7280] hover:text-[#1F2937]">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        Retour aux {{ $isEmprunt ? 'emprunts' : 'prêts' }}
    </a>
</div>

<div class="grid grid-cols-12 gap-4">

    {{-- ===== INFOS GÉNÉRALES ===== --}}
    <div class="col-span-12 lg:col-span-8 space-y-4">
        <div class="soft-card p-5">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-xl font-bold text-[#1F2937]">{{ $dette->partie }}</h2>
                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full {{ $statutBadge }}">{{ $statutLabel }}</span>
                    </div>
                    <p class="text-xs text-[#6B7280]">
                        {{ $isEmprunt ? 'Je dois rembourser' : 'Cette personne doit me rembourser' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('dettes.destroy', $dette) }}"
                      onsubmit="return confirm('Supprimer cette opération et tous ses paiements ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 rounded-lg text-[#EF4444] hover:bg-[#EF4444]/10">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                </form>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="p-3 bg-[#F8FAFC] rounded-lg">
                    <p class="text-[10px] font-semibold uppercase text-[#6B7280]">Initial</p>
                    <p class="text-base font-bold text-[#1F2937]">{{ number_format((int)$dette->montant_initial, 0, ',', "\u{00A0}") }}</p>
                </div>
                <div class="p-3 bg-[#006c49]/5 rounded-lg">
                    <p class="text-[10px] font-semibold uppercase text-[#006c49]">Remboursé</p>
                    <p class="text-base font-bold text-[#006c49]">{{ number_format((int)$dette->montant_rembourse, 0, ',', "\u{00A0}") }}</p>
                </div>
                <div class="p-3 rounded-lg" style="background-color: {{ $couleurMain }}10">
                    <p class="text-[10px] font-semibold uppercase" style="color: {{ $couleurMain }}">Restant</p>
                    <p class="text-base font-bold" style="color: {{ $couleurMain }}">{{ number_format((int)$dette->montant_restant, 0, ',', "\u{00A0}") }}</p>
                </div>
            </div>

            <div class="w-full bg-[#F3F4F6] rounded-full h-2 overflow-hidden mb-1">
                <div class="h-full rounded-full" style="width: {{ $dette->pct_rembourse }}%; background-color: {{ $couleurMain }}"></div>
            </div>
            <p class="text-xs text-[#6B7280]">{{ $dette->pct_rembourse }}% remboursé</p>

            <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-[#E5E7EB]">
                <div>
                    <p class="text-[10px] font-semibold uppercase text-[#6B7280]">Date opération</p>
                    <p class="text-sm text-[#1F2937]">{{ $dette->date_operation->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase text-[#6B7280]">Échéance</p>
                    <p class="text-sm text-[#1F2937]">
                        {{ $dette->date_echeance ? $dette->date_echeance->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
                @if($dette->interet_pct)
                <div>
                    <p class="text-[10px] font-semibold uppercase text-[#6B7280]">Intérêts</p>
                    <p class="text-sm text-[#1F2937]">{{ $dette->interet_pct }}%</p>
                </div>
                @endif
                <div>
                    <p class="text-[10px] font-semibold uppercase text-[#6B7280]">Affecte le budget</p>
                    <p class="text-sm text-[#1F2937]">{{ $dette->affecte_budget ? 'Oui' : 'Non' }}</p>
                </div>
            </div>

            @if($dette->note)
            <div class="mt-4 p-3 bg-[#FEF3C7] rounded-lg">
                <p class="text-[10px] font-semibold uppercase text-[#92400E] mb-1">Note</p>
                <p class="text-sm text-[#1F2937]">{{ $dette->note }}</p>
            </div>
            @endif
        </div>

        {{-- ===== HISTORIQUE PAIEMENTS ===== --}}
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined">history</span>
                Historique des paiements ({{ $dette->remboursements->count() }})
            </h3>

            @forelse($dette->remboursements as $rb)
                <div class="flex items-center justify-between py-3 border-b border-[#E5E7EB] last:border-0">
                    <div>
                        <p class="text-sm font-semibold text-[#1F2937]">{{ number_format((int)$rb->montant, 0, ',', "\u{00A0}") }} FCFA</p>
                        <p class="text-xs text-[#6B7280]">{{ $rb->date->translatedFormat('d M Y') }}@if($rb->affecte_budget) · <span class="text-[#006c49]">Budget affecté</span>@endif</p>
                        @if($rb->note)<p class="text-xs text-[#6B7280] italic mt-0.5">{{ $rb->note }}</p>@endif
                    </div>
                    <form method="POST" action="{{ route('dettes.remboursement.destroy', $rb) }}"
                          onsubmit="return confirm('Supprimer ce paiement ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded text-[#EF4444] hover:bg-[#EF4444]/10">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-[#6B7280] text-center py-6">Aucun paiement enregistré.</p>
            @endforelse
        </div>
    </div>

    {{-- ===== FORMULAIRE PAIEMENT ===== --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="soft-card p-5 sticky top-4">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined" style="color: {{ $couleurMain }}">payments</span>
                Enregistrer un paiement
            </h3>

            @if($dette->statut === 'solde')
                <div class="p-4 bg-[#006c49]/5 rounded-lg text-center">
                    <span class="material-symbols-outlined text-3xl text-[#006c49] mb-1">check_circle</span>
                    <p class="text-sm font-semibold text-[#006c49]">Opération soldée</p>
                </div>
            @else
                <form method="POST" action="{{ route('dettes.remboursement.store', $dette) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1">Montant (max {{ number_format((int)$dette->montant_restant, 0, ',', "\u{00A0}") }} FCFA)</label>
                        <input type="number" name="montant" required min="1" max="{{ $dette->montant_restant }}" step="1"
                               value="{{ old('montant') }}"
                               class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">
                        @error('montant') <p class="text-xs text-[#EF4444] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1">Date</label>
                        <input type="date" name="date" required
                               value="{{ old('date', now()->format('Y-m-d')) }}"
                               class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1">Note</label>
                        <input type="text" name="note" maxlength="255"
                               class="w-full border border-[#E5E7EB] rounded-lg px-3 py-2 text-sm">
                    </div>
                    <label class="flex items-start gap-2 cursor-pointer p-3 bg-[#F8FAFC] rounded-lg border border-[#E5E7EB]">
                        <input type="checkbox" name="affecte_budget" value="1" class="mt-0.5 rounded">
                        <div>
                            <p class="text-xs font-semibold text-[#1F2937]">Affecter mon budget</p>
                            <p class="text-[11px] text-[#6B7280]">
                                @if($isEmprunt)
                                    Crée une dépense (je paie).
                                @else
                                    Crée un revenu (je reçois).
                                @endif
                            </p>
                        </div>
                    </label>
                    <button type="submit"
                            class="w-full py-2.5 rounded-lg text-white font-semibold text-sm"
                            style="background-color: {{ $couleurMain }}">
                        Enregistrer le paiement
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

</x-layouts.app>
