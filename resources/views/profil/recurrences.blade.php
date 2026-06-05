<x-layouts.app title="Récurrences" pageTitle="Opérations récurrentes" pageSubtitle="Dépenses et revenus automatiques chaque mois">

<div class="mb-4 flex flex-wrap items-center gap-3">
    <a href="{{ route('profil.index') }}" class="inline-flex items-center gap-1 text-sm text-[#6B7280] hover:text-[#002452]">
        <span class="material-symbols-outlined text-base">arrow_back</span> Retour aux paramètres
    </a>
</div>

@if(session('success_rec'))
<div class="mb-4 p-3 rounded-lg bg-[#d1fae5] border border-[#006c49]/20 text-[#065f46] text-sm">{{ session('success_rec') }}</div>
@endif
@if(session('error_rec'))
<div class="mb-4 p-3 rounded-lg bg-[#fee2e2] border border-[#EF4444]/20 text-[#991b1b] text-sm">{{ session('error_rec') }}</div>
@endif

<div class="grid grid-cols-12 gap-5" x-data="{ type: '{{ in_array(request('type'), ['depense', 'revenu'], true) ? request('type') : 'depense' }}' }">
    <div class="col-span-12 lg:col-span-4">
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-1">Nouvelle récurrence</h3>
            <p class="text-xs text-[#6B7280] mb-4">Créée automatiquement chaque mois au jour choisi (loyer, abonnements…).</p>
            <form method="POST" action="{{ route('profil.recurrences.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase">Type</label>
                    <select name="type" x-model="type" required class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm bg-white">
                        <option value="depense">Dépense</option>
                        <option value="revenu">Revenu (bonus / extra)</option>
                    </select>
                </div>
                <div x-show="type === 'depense'">
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase">Catégorie</label>
                    <select name="categorie_id" class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm bg-white">
                        <option value="">-- Choisir --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                    @error('categorie_id')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div x-show="type === 'revenu'" x-cloak>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase">Type de revenu</label>
                    <select name="revenu_type" class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm bg-white">
                        <option value="extra">Extra</option>
                        <option value="bonus">Bonus</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase">Montant (FCFA)</label>
                    <input type="number" name="montant" min="1" required class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm" />
                    @error('montant')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase">Jour du mois (1-28)</label>
                    <input type="number" name="jour_du_mois" min="1" max="28" value="1" required class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase">Libellé</label>
                    <input type="text" name="libelle" maxlength="255" placeholder="Ex: Loyer, Abonnement…" class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm" />
                </div>
                <label class="flex items-center gap-2" x-show="type === 'depense'">
                    <input type="checkbox" name="imprevue" value="1" class="w-4 h-4 rounded" />
                    <span class="text-sm text-[#6B7280]">Marquer comme imprévue</span>
                </label>
                <button type="submit" class="btn-primary w-full">Enregistrer</button>
            </form>
            <p class="text-[10px] text-[#9CA3AF] mt-3">Génération automatique chaque jour après le jour choisi (cron quotidien).</p>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-8">
        <div class="soft-card overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E5E7EB]">
                <h3 class="font-headline text-base font-semibold text-[#1F2937]">Vos récurrences</h3>
            </div>
            <div class="divide-y divide-[#F3F4F6]">
                @forelse($recurrences as $rec)
                <div class="px-5 py-3 flex flex-wrap items-center gap-3 {{ !$rec->is_active ? 'opacity-60' : '' }}">
                    <span class="material-symbols-outlined text-[#002452]">{{ $rec->isDepense() ? 'receipt_long' : 'payments' }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-[#1F2937]">
                            {{ $rec->libelle ?: ($rec->isDepense() ? ($rec->categorie?->nom ?? 'Dépense') : ucfirst($rec->revenu_type ?? 'revenu')) }}
                        </p>
                        <p class="text-xs text-[#6B7280]">
                            {{ $rec->isDepense() ? 'Dépense' : 'Revenu' }}
                            &bull; jour {{ $rec->jour_du_mois }}
                            &bull; {{ number_format($rec->montant, 0, ',', "\u{00A0}") }} FCFA
                            @if($rec->last_generated_ym) &bull; dernier : {{ $rec->last_generated_ym }} @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('profil.recurrences.toggle', $rec) }}">@csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded border border-[#E5E7EB] hover:bg-gray-50">{{ $rec->is_active ? 'Pause' : 'Activer' }}</button>
                        </form>
                        <form method="POST" action="{{ route('profil.recurrences.generer', $rec) }}">@csrf
                            <button type="submit" class="text-xs px-2 py-1 rounded bg-[#002452] text-white">Ce mois</button>
                        </form>
                        <form method="POST" action="{{ route('profil.recurrences.destroy', $rec) }}" onsubmit="return confirm('Supprimer cette récurrence ?')">@csrf @method('DELETE')
                            <button type="submit" class="text-xs px-2 py-1 rounded text-[#EF4444] border border-[#FECACA]">Suppr.</button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="px-5 py-10 text-center text-sm text-[#6B7280]">Aucune récurrence. Créez-en une à gauche.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

</x-layouts.app>
