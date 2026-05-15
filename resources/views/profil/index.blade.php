<x-layouts.app title="Paramètres" pageTitle="Paramètres" pageSubtitle="Profil & préférences">

@php
    $quota     = (int) ($user->quota_taux           ?? 30);
    $seuilAtt  = (int) ($user->seuil_attention       ?? 70);
    $seuilCrit = (int) ($user->seuil_critique        ?? 90);
    $seuilPlaf = (int) ($user->seuil_plafond_cat     ?? 80);
    $objEpPct  = (int) ($user->objectif_epargne_pct  ?? 10);
    $jourBilan = (int) ($user->jour_bilan_email       ?? 1);
@endphp

{{-- ======= BANDEAU HAUT : identité + succès ======= --}}
<div class="flex items-center gap-4 soft-card p-4 mb-5">
    <div class="w-12 h-12 rounded-full bg-[#002452] flex items-center justify-center text-white text-xl font-bold flex-shrink-0">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-headline font-bold text-[#1F2937] text-sm truncate">{{ $user->name }}</p>
        <p class="text-xs text-[#6B7280] truncate">{{ $user->email }}</p>
    </div>
    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full flex-shrink-0
        {{ $user->role === 'super_admin' ? 'bg-[#002452] text-white' : ($user->role === 'admin' ? 'bg-[#d1fae5] text-[#065f46]' : 'bg-[#F3F4F6] text-[#6B7280]') }}">
        {{ match($user->role) { 'super_admin' => 'Super Admin', 'admin' => 'Admin', default => 'Utilisateur' } }}
    </span>
    <p class="text-[10px] text-[#9CA3AF] flex-shrink-0 hidden sm:block">Membre depuis {{ $user->created_at->translatedFormat('M Y') }}</p>
</div>

@foreach(['success_infos'=>'Informations mises à jour.','success_mdp'=>'Mot de passe modifié.','success_prefs'=>'Préférences enregistrées.','success_cats'=>null] as $key => $msg)
@if(session($key))
<div class="mb-4 p-3 rounded-lg bg-[#d1fae5] border border-[#006c49]/20 text-[#065f46] text-sm font-medium flex items-center gap-2">
    <span class="material-symbols-outlined text-base">check_circle</span>{{ session($key) ?? $msg }}
</div>
@endif
@endforeach
@if($errors->has('cats'))
<div class="mb-4 p-3 rounded-lg bg-[#fee2e2] border border-[#EF4444]/20 text-[#991b1b] text-sm font-medium flex items-center gap-2">
    <span class="material-symbols-outlined text-base">error</span>{{ $errors->first('cats') }}
</div>
@endif

<div class="grid grid-cols-12 gap-5 pb-24 lg:pb-4"
     x-data="{
        quota: {{ $quota }},
        seuilAtt: {{ $seuilAtt }},
        seuilCrit: {{ $seuilCrit }},
        seuilPlaf: {{ $seuilPlaf }},
        objEp: {{ $objEpPct }},
        epS: {{ (int)($user->epargne_salaire_pct ?? 0) }},
        discret: {{ $user->mode_discret ? 'true' : 'false' }},
        notifs: {{ $user->notifs_email ? 'true' : 'false' }},
     }">

    {{-- ====================== COL GAUCHE ====================== --}}
    <div class="col-span-12 lg:col-span-5 space-y-4">

        {{-- ── Section 1 : Compte ── --}}
        <div class="soft-card p-5">
            <h4 class="font-headline text-sm font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#002452] text-base">manage_accounts</span>
                Compte
            </h4>
            <form method="POST" action="{{ route('profil.update.infos') }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2.5 border @error('name') border-[#EF4444] @else border-[#E5E7EB] @enderror rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                    @error('name')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3 py-2.5 border @error('email') border-[#EF4444] @else border-[#E5E7EB] @enderror rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                    @error('email')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-base">save</span> Enregistrer
                </button>
            </form>
        </div>

        {{-- ── Section 2 : Mot de passe ── --}}
        <div class="soft-card p-5">
            <h4 class="font-headline text-sm font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#002452] text-base">lock</span>
                Sécurité
            </h4>
            <form method="POST" action="{{ route('profil.update.password') }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Mot de passe actuel</label>
                    <input type="password" name="current_password" required
                           class="w-full px-3 py-2.5 border @error('current_password') border-[#EF4444] @else border-[#E5E7EB] @enderror rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                    @error('current_password')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Nouveau mot de passe</label>
                    <input type="password" name="password" required
                           class="w-full px-3 py-2.5 border @error('password') border-[#EF4444] @else border-[#E5E7EB] @enderror rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                    @error('password')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Confirmer</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                </div>
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-[#002452] text-[#002452] text-sm font-semibold hover:bg-[#002452]/5 transition-colors">
                    <span class="material-symbols-outlined text-base">key</span> Modifier
                </button>
            </form>
        </div>

        {{-- ── Section 3 : Catégories de dépenses ── --}}
        <div class="soft-card p-5" x-data="{ showForm: false, editId: null, editNom: '', editCouleur: '#6B7280' }">
            <h4 class="font-headline text-sm font-semibold text-[#1F2937] mb-1 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#002452] text-base">category</span>
                Catégories de dépenses
            </h4>
            <p class="text-xs text-[#9CA3AF] mb-4">Personnalisez vos dénominations de dépenses. Les catégories par défaut ne peuvent pas être supprimées.</p>

            {{-- Liste des catégories existantes --}}
            <div class="space-y-2 mb-4">
                @foreach($categories as $cat)
                <div class="flex items-center gap-2 p-2.5 border border-[#E5E7EB] rounded-xl bg-[#F8FAFC]"
                     x-data="{ editing: false, nom: '{{ addslashes($cat->nom) }}', couleur: '{{ $cat->couleur }}' }">

                    {{-- Pastille couleur --}}
                    <div class="w-3 h-3 rounded-full flex-shrink-0" :style="'background:' + couleur"></div>

                    {{-- Nom (lecture) --}}
                    <span class="flex-1 text-sm text-[#1F2937] font-medium truncate" x-show="!editing">
                        {{ $cat->nom }}
                        @if($cat->is_default)
                        <span class="text-[9px] text-[#9CA3AF] ml-1">(défaut)</span>
                        @endif
                    </span>

                    {{-- Formulaire d'édition inline --}}
                    <template x-if="editing">
                        <form method="POST" action="{{ route('profil.categories.update', $cat) }}" class="flex-1 flex items-center gap-1.5">
                            @csrf @method('PUT')
                            <input type="color" name="couleur" x-model="couleur"
                                   class="w-7 h-7 rounded cursor-pointer border border-[#E5E7EB] p-0.5 flex-shrink-0" />
                            <input type="text" name="nom" x-model="nom" required maxlength="100"
                                   class="flex-1 min-w-0 px-2 py-1 text-xs border border-[#002452] rounded-lg focus:outline-none" />
                            <button type="submit" class="text-[#006c49] hover:text-[#004d33]">
                                <span class="material-symbols-outlined text-base">check</span>
                            </button>
                            <button type="button" @click="editing = false" class="text-[#9CA3AF] hover:text-[#6B7280]">
                                <span class="material-symbols-outlined text-base">close</span>
                            </button>
                        </form>
                    </template>

                    {{-- Actions (lecture) --}}
                    <div class="flex items-center gap-1 flex-shrink-0" x-show="!editing">
                        <button type="button" @click="editing = true"
                                class="p-1 text-[#6B7280] hover:text-[#002452] rounded-lg hover:bg-[#002452]/5 transition-colors">
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </button>
                        @if(!$cat->is_default)
                        <form method="POST" action="{{ route('profil.categories.destroy', $cat) }}"
                              onsubmit="return confirm('Supprimer « {{ $cat->nom }} » ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1 text-[#9CA3AF] hover:text-[#EF4444] rounded-lg hover:bg-[#EF4444]/5 transition-colors">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </form>
                        @else
                        <span class="p-1 text-[#E5E7EB]">
                            <span class="material-symbols-outlined text-sm">lock</span>
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Bouton + Formulaire ajout --}}
            <button type="button" @click="showForm = !showForm"
                    class="w-full flex items-center justify-center gap-2 py-2 border border-dashed border-[#002452]/30 rounded-xl text-xs text-[#002452] font-semibold hover:bg-[#002452]/5 transition-colors">
                <span class="material-symbols-outlined text-sm">add</span>
                <span x-text="showForm ? 'Annuler' : 'Nouvelle catégorie'"></span>
            </button>

            <form x-show="showForm" x-cloak method="POST" action="{{ route('profil.categories.store') }}"
                  class="mt-3 p-3 bg-[#F0F4FF] border border-[#002452]/15 rounded-xl space-y-2">
                @csrf
                @error('nom')<p class="text-[#EF4444] text-xs font-medium">{{ $message }}</p>@enderror
                <div class="flex items-center gap-2">
                    <div class="flex-shrink-0">
                        <label class="block text-[10px] font-semibold text-[#6B7280] uppercase mb-1">Couleur</label>
                        <input type="color" name="couleur" value="{{ old('couleur', '#6B7280') }}"
                               class="w-10 h-9 rounded-lg cursor-pointer border border-[#E5E7EB] p-0.5" />
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-semibold text-[#6B7280] uppercase mb-1">Nom de la catégorie</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required maxlength="100"
                               placeholder="ex: Santé, Abonnements, Épicerie…"
                               class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white" />
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2 text-xs py-2">
                    <span class="material-symbols-outlined text-sm">add</span> Créer la catégorie
                </button>
            </form>
        </div>

    </div>

    {{-- ====================== COL DROITE ====================== --}}
    <div class="col-span-12 lg:col-span-7">
        <form method="POST" action="{{ route('profil.update.preferences') }}" class="space-y-4">
            @csrf @method('PUT')

            {{-- ── Section 3 : Règles budgétaires ── --}}
            <div class="soft-card p-5">
                <h4 class="font-headline text-sm font-semibold text-[#1F2937] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#002452] text-base">account_balance_wallet</span>
                    Règles budgétaires
                </h4>
                <p class="text-xs text-[#9CA3AF] mb-4">Ces paramètres s'appliquent immédiatement à tous vos calculs et alertes.</p>

                {{-- Quota dépensable sur bonus --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <label class="text-xs font-semibold text-[#1F2937] uppercase tracking-wide">Part dépensable des bonus/extras</label>
                            <p class="text-[10px] text-[#9CA3AF] mt-0.5">Le reste est mis en réserve automatiquement</p>
                        </div>
                        <div class="text-right">
                            <span class="text-base font-headline font-bold text-[#002452]" x-text="quota + '%'"></span>
                            <p class="text-[10px] text-[#9CA3AF]">Réserve : <span x-text="(100-quota) + '%'"></span></p>
                        </div>
                    </div>
                    <input type="range" name="quota_taux" min="0" max="100" step="5" x-model="quota"
                           class="w-full h-2 bg-[#E5E7EB] rounded-full appearance-none cursor-pointer accent-[#002452]" />
                    <div class="flex justify-between text-[10px] text-[#9CA3AF] mt-0.5">
                        <span>0% — tout en réserve</span><span>50%</span><span>100% — tout dispo</span>
                    </div>
                    {{-- Simulation live --}}
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="p-2.5 bg-[#f0fdf4] border border-[#006c49]/20 rounded-lg text-center">
                            <p class="text-[9px] text-[#006c49] font-bold uppercase mb-0.5">Dépensable (<span x-text="quota"></span>%)</p>
                            <p class="font-headline font-bold text-[#006c49] text-xs" x-text="Math.round(200000 * quota / 100).toLocaleString('fr-FR') + ' F'"></p>
                            <p class="text-[8px] text-[#9CA3AF]">sur 200 000 F</p>
                        </div>
                        <div class="p-2.5 bg-[#EFF6FF] border border-[#002452]/20 rounded-lg text-center">
                            <p class="text-[9px] text-[#002452] font-bold uppercase mb-0.5">Réserve (<span x-text="100-quota"></span>%)</p>
                            <p class="font-headline font-bold text-[#002452] text-xs" x-text="Math.round(200000 * (100-quota) / 100).toLocaleString('fr-FR') + ' F'"></p>
                            <p class="text-[8px] text-[#9CA3AF]">bloquée</p>
                        </div>
                        <div class="p-2.5 bg-[#F8FAFC] border border-[#E5E7EB] rounded-lg text-center">
                            <p class="text-[9px] text-[#6B7280] font-bold uppercase mb-0.5">Exemple bonus</p>
                            <p class="font-headline font-bold text-[#1F2937] text-xs">200 000 F</p>
                            <p class="text-[8px] text-[#9CA3AF]">brut saisi</p>
                        </div>
                    </div>
                    @error('quota_taux')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Objectif épargne mensuel --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <label class="text-xs font-semibold text-[#1F2937] uppercase tracking-wide">Objectif d'épargne mensuel</label>
                            <p class="text-[10px] text-[#9CA3AF] mt-0.5">% de ton revenu total à épargner chaque mois</p>
                        </div>
                        <span class="text-base font-headline font-bold text-[#006c49]" x-text="objEp + '%'"></span>
                    </div>
                    <input type="range" name="objectif_epargne_pct" min="0" max="80" step="5" x-model="objEp"
                           class="w-full h-2 bg-[#E5E7EB] rounded-full appearance-none cursor-pointer accent-[#006c49]" />
                    <div class="flex justify-between text-[10px] text-[#9CA3AF] mt-0.5">
                        <span>0% — pas d'objectif</span><span>40%</span><span>80% max</span>
                    </div>
                    <p class="text-[10px] text-[#9CA3AF] mt-1 italic">
                        Utilisé comme référence dans le suivi épargne mensuel et les rapports.
                    </p>
                    @error('objectif_epargne_pct')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Épargne programmée sur salaire fixe --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <label class="text-xs font-semibold text-[#1F2937] uppercase tracking-wide flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm text-[#006c49]">savings</span>
                                Épargne sur salaire fixe
                            </label>
                            <p class="text-[10px] text-[#9CA3AF] mt-0.5">% du salaire fixe mis en réserve automatiquement chaque mois</p>
                        </div>
                        <span class="text-base font-headline font-bold text-[#006c49]" x-text="epS + '%'"></span>
                    </div>
                    <input type="range" name="epargne_salaire_pct" min="0" max="50" step="1" x-model="epS"
                           class="w-full h-2 bg-[#E5E7EB] rounded-full appearance-none cursor-pointer accent-[#006c49]" />
                    <div class="flex justify-between text-[10px] text-[#9CA3AF] mt-0.5">
                        <span>0% — désactivé</span><span>10%</span><span>50% max</span>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-2" x-show="epS > 0">
                        <div class="p-2.5 bg-[#f0fdf4] border border-[#006c49]/20 rounded-lg text-center">
                            <p class="text-[9px] text-[#006c49] font-bold uppercase mb-0.5">Épargne (<span x-text="epS"></span>%)</p>
                            <p class="font-headline font-bold text-[#006c49] text-xs" x-text="Math.round(200000 * epS / 100).toLocaleString('fr-FR') + ' F'"></p>
                            <p class="text-[8px] text-[#9CA3AF]">sur 200 000 F</p>
                        </div>
                        <div class="p-2.5 bg-[#F8FAFC] border border-[#E5E7EB] rounded-lg text-center">
                            <p class="text-[9px] text-[#6B7280] font-bold uppercase mb-0.5">Disponible</p>
                            <p class="font-headline font-bold text-[#1F2937] text-xs" x-text="Math.round(200000 * (100-epS) / 100).toLocaleString('fr-FR') + ' F'"></p>
                            <p class="text-[8px] text-[#9CA3AF]">dépensable</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-[#9CA3AF] mt-1 italic">
                        0% = pas d'épargne sur salaire. Ajustez entre 1% et 50% selon votre discipline financière.
                    </p>
                    @error('epargne_salaire_pct')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Devise --}}
                <div>
                    <label class="block text-xs font-semibold text-[#1F2937] mb-1.5 uppercase tracking-wide">Devise d'affichage</label>
                    @php
                        $devises = [
                            'FCFA'=>'FCFA — Franc CFA (XOF)','EUR'=>'EUR — Euro (€)',
                            'USD'=>'USD — Dollar ($)','GBP'=>'GBP — Livre (£)',
                            'MAD'=>'MAD — Dirham marocain','DZD'=>'DZD — Dinar algérien',
                            'TND'=>'TND — Dinar tunisien','NGN'=>'NGN — Naira nigérian','GHS'=>'GHS — Cedi ghanéen',
                        ];
                    @endphp
                    <select name="devise" class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white">
                        @foreach($devises as $code => $label)
                        <option value="{{ $code }}" {{ ($user->devise ?? 'FCFA') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('devise')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ── Section 4 : Seuils d'alerte ── --}}
            <div class="soft-card p-5">
                <h4 class="font-headline text-sm font-semibold text-[#1F2937] mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#F59E0B] text-base">notifications_active</span>
                    Seuils d'alerte
                </h4>
                <p class="text-xs text-[#9CA3AF] mb-5">Détermine quand EcoPoche déclenche les avertissements visuels et emails.</p>

                {{-- Seuil Attention --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <label class="text-xs font-semibold text-[#F59E0B] uppercase tracking-wide flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">warning</span> Alerte Attention
                            </label>
                            <p class="text-[10px] text-[#9CA3AF] mt-0.5">Zone orange : surveille tes dépenses</p>
                        </div>
                        <span class="text-sm font-headline font-bold text-[#F59E0B]" x-text="seuilAtt + '%'"></span>
                    </div>
                    <input type="range" name="seuil_attention" min="40" max="95" step="5" x-model="seuilAtt"
                           class="w-full h-2 bg-[#E5E7EB] rounded-full appearance-none cursor-pointer accent-[#F59E0B]" />
                    <div class="flex justify-between text-[10px] text-[#9CA3AF] mt-0.5">
                        <span>40%</span><span>70% (défaut)</span><span>95%</span>
                    </div>
                    @error('seuil_attention')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Seuil Critique --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <label class="text-xs font-semibold text-[#EF4444] uppercase tracking-wide flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">error</span> Alerte Critique
                            </label>
                            <p class="text-[10px] text-[#9CA3AF] mt-0.5">Zone rouge : alerte email envoyée</p>
                        </div>
                        <span class="text-sm font-headline font-bold text-[#EF4444]" x-text="seuilCrit + '%'"></span>
                    </div>
                    <input type="range" name="seuil_critique" min="50" max="100" step="5" x-model="seuilCrit"
                           class="w-full h-2 bg-[#E5E7EB] rounded-full appearance-none cursor-pointer accent-[#EF4444]" />
                    <div class="flex justify-between text-[10px] text-[#9CA3AF] mt-0.5">
                        <span>50%</span><span>90% (défaut)</span><span>100%</span>
                    </div>
                    @error('seuil_critique')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror

                    {{-- Visualisation des 3 zones --}}
                    <div class="mt-3 relative h-5 rounded-full overflow-hidden border border-[#E5E7EB]">
                        <div class="absolute inset-y-0 left-0 bg-[#d1fae5] transition-all"
                             :style="'width:' + seuilAtt + '%'"></div>
                        <div class="absolute inset-y-0 bg-[#fef9c3] transition-all"
                             :style="'left:' + seuilAtt + '%; width:' + (seuilCrit - seuilAtt) + '%'"></div>
                        <div class="absolute inset-y-0 right-0 bg-[#fee2e2] transition-all"
                             :style="'width:' + (100 - seuilCrit) + '%'"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-[#6B7280] pointer-events-none">
                            Sain &nbsp;·&nbsp; Attention &nbsp;·&nbsp; Critique
                        </div>
                    </div>
                </div>

                {{-- Seuil plafond catégorie --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <label class="text-xs font-semibold text-[#6B7280] uppercase tracking-wide">Alerte plafond catégorie</label>
                            <p class="text-[10px] text-[#9CA3AF] mt-0.5">% du plafond mensuel d'une catégorie avant avertissement</p>
                        </div>
                        <span class="text-sm font-headline font-bold text-[#6B7280]" x-text="seuilPlaf + '%'"></span>
                    </div>
                    <input type="range" name="seuil_plafond_cat" min="50" max="100" step="5" x-model="seuilPlaf"
                           class="w-full h-2 bg-[#E5E7EB] rounded-full appearance-none cursor-pointer accent-[#6B7280]" />
                    <div class="flex justify-between text-[10px] text-[#9CA3AF] mt-0.5">
                        <span>50%</span><span>80% (défaut)</span><span>100%</span>
                    </div>
                    @error('seuil_plafond_cat')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ── Section 5 : Notifications & Affichage ── --}}
            <div class="soft-card p-5 space-y-3">
                <h4 class="font-headline text-sm font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#002452] text-base">tune</span>
                    Notifications & Affichage
                </h4>

                {{-- Notifications email --}}
                <div class="flex items-center justify-between p-3 bg-[#F8FAFC] border border-[#E5E7EB] rounded-xl">
                    <div>
                        <p class="text-sm font-semibold text-[#1F2937]">Notifications par email</p>
                        <p class="text-xs text-[#9CA3AF]">Alertes critiques + récapitulatif mensuel</p>
                    </div>
                    <div>
                        <input type="hidden" name="notifs_email" :value="notifs ? '1' : '0'" />
                        <div @click="notifs = !notifs"
                             :class="notifs ? 'bg-[#006c49]' : 'bg-[#D1D5DB]'"
                             class="w-11 h-6 rounded-full transition-colors duration-200 relative cursor-pointer">
                            <div :class="notifs ? 'translate-x-5' : 'translate-x-1'"
                                 class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></div>
                        </div>
                    </div>
                </div>

                {{-- Jour du bilan email --}}
                <div x-show="notifs" class="p-3 bg-[#F8FAFC] border border-[#E5E7EB] rounded-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[#1F2937]">Jour du récapitulatif mensuel</p>
                            <p class="text-xs text-[#9CA3AF]">Email envoyé ce jour du mois</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" name="jour_bilan_email"
                                   value="{{ old('jour_bilan_email', $jourBilan) }}"
                                   min="1" max="28"
                                   class="w-16 px-2 py-1.5 text-center border border-[#E5E7EB] rounded-lg text-sm font-bold text-[#002452] focus:outline-none focus:border-[#002452]" />
                            <span class="text-xs text-[#6B7280]">du mois</span>
                        </div>
                    </div>
                    @error('jour_bilan_email')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Mode discret --}}
                <div class="flex items-center justify-between p-3 bg-[#F8FAFC] border border-[#E5E7EB] rounded-xl">
                    <div>
                        <p class="text-sm font-semibold text-[#1F2937]">Mode discret</p>
                        <p class="text-xs text-[#9CA3AF]">Masque les montants — utile en public</p>
                    </div>
                    <div>
                        <input type="hidden" name="mode_discret" :value="discret ? '1' : '0'" />
                        <div @click="discret = !discret"
                             :class="discret ? 'bg-[#002452]' : 'bg-[#D1D5DB]'"
                             class="w-11 h-6 rounded-full transition-colors duration-200 relative cursor-pointer">
                            <div :class="discret ? 'translate-x-5' : 'translate-x-1'"
                                 class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base">save</span>
                Enregistrer toutes les préférences
            </button>

        </form>
    </div>
</div>

</x-layouts.app>
