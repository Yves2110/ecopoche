<x-layouts.app titre="Administration — Comptes">

{{-- Bannière impersonnification active --}}
@if(session('impersonnation_id'))
<div class="bg-[#D97706] text-white text-sm px-4 py-2.5 flex items-center justify-between mb-5 rounded-xl border border-[#D97706]/50">
    <span class="flex items-center gap-2 font-medium">
        <span class="material-symbols-outlined text-base">switch_account</span>
        Navigation en tant que <strong>{{ auth()->user()->name }}</strong>
    </span>
    <form method="POST" action="{{ route('admin.stop_impersonner') }}">
        @csrf
        <button type="submit" class="flex items-center gap-1 text-xs font-bold bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg transition-colors">
            <span class="material-symbols-outlined text-sm">logout</span> Revenir à mon compte
        </button>
    </form>
</div>
@endif

{{-- Flash --}}
@if(session('success'))
<div class="flex items-start gap-2 bg-[#d1fae5] text-[#065f46] text-sm px-4 py-3 rounded-xl mb-4 border border-[#6ee7b7]">
    <span class="material-symbols-outlined text-base flex-shrink-0 mt-0.5">check_circle</span>
    <div>
        @php $msg = session('success'); @endphp
        {{-- Afficher le mot de passe réinitialisé en évidence s'il est présent --}}
        @if(str_contains($msg, 'réinitialisé :'))
            @php [$texte, $mdp] = explode(' : ', $msg, 2); @endphp
            <p class="font-semibold">{{ $texte }}</p>
            <div class="mt-1 flex items-center gap-2">
                <span class="text-xs text-[#065f46]">Nouveau mot de passe :</span>
                <code class="bg-[#002452] text-[#6ffbbe] font-mono font-bold px-3 py-1 rounded-lg text-sm tracking-widest">{{ $mdp }}</code>
                <span class="text-[10px] text-[#065f46] italic">(Notez-le ou envoyez-le à l'utilisateur)</span>
            </div>
        @else
            {{ $msg }}
        @endif
    </div>
</div>
@endif
@if(session('error'))
<div class="flex items-center gap-2 bg-[#fee2e2] text-[#991b1b] text-sm px-4 py-3 rounded-xl mb-4 border border-[#fca5a5]">
    <span class="material-symbols-outlined text-base">error</span>{{ session('error') }}
</div>
@endif

{{-- Header + KPI + bouton sur une seule ligne --}}
<div x-data="{ showCreate: {{ $errors->any() ? 'true' : 'false' }} }">
<div class="flex items-center gap-3 mb-4">

    {{-- Titre --}}
    <div class="flex-shrink-0">
        <h1 class="font-headline text-xl font-bold text-[#002452] leading-tight">Comptes</h1>
        <p class="text-[11px] text-[#6B7280]">Gestion des utilisateurs</p>
    </div>

    <div class="w-px h-8 bg-[#E5E7EB] flex-shrink-0"></div>

    {{-- KPI en ligne --}}
    <div class="flex items-center gap-2 flex-1 overflow-x-auto">
        @foreach([
            ['icon'=>'group',              'label'=>'Total',        'val'=>$stats['total'],        'color'=>'#002452', 'bg'=>'transparent',    'border'=>'#E5E7EB'],
            ['icon'=>'verified_user',      'label'=>'Actifs',       'val'=>$stats['actifs'],       'color'=>'#006c49', 'bg'=>'#f0fdf4',        'border'=>'#6ee7b7'],
            ['icon'=>'person',             'label'=>'Utilisateurs', 'val'=>$stats['utilisateurs'], 'color'=>'#374151', 'bg'=>'#F9FAFB',        'border'=>'#E5E7EB'],
            ['icon'=>'admin_panel_settings','label'=>'Admins',      'val'=>$stats['admins'],       'color'=>'#4f46e5', 'bg'=>'#EEF2FF',        'border'=>'#c7d2fe'],
            ['icon'=>'block',              'label'=>'Suspendus',    'val'=>$stats['suspendus'],    'color'=>'#DC2626', 'bg'=>'#fef2f2',        'border'=>'#fecaca'],
        ] as $kpi)
        <div class="flex items-center gap-2 px-3 py-2 rounded-xl border flex-shrink-0"
             style="background:{{ $kpi['bg'] }};border-color:{{ $kpi['border'] }}">
            <span class="material-symbols-outlined text-base" style="color:{{ $kpi['color'] }}">{{ $kpi['icon'] }}</span>
            <div>
                <p class="text-[9px] font-semibold uppercase tracking-wide leading-none mb-0.5" style="color:{{ $kpi['color'] }}">{{ $kpi['label'] }}</p>
                <p class="text-base font-bold leading-none" style="color:{{ $kpi['color'] }}">{{ $kpi['val'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bouton créer --}}
    <button @click="showCreate = !showCreate" class="btn-primary flex items-center gap-1.5 flex-shrink-0">
        <span class="material-symbols-outlined text-base">person_add</span>
        Nouveau compte
    </button>
</div>

    {{-- Drawer création (slide-down) --}}
    <div x-show="showCreate" x-transition class="mb-4">
        <div class="soft-card p-5">
            <h3 class="font-headline text-base font-semibold text-[#1F2937] mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-[#002452] text-lg">person_add</span>
                Créer un nouveau compte
            </h3>
            <form method="POST" action="{{ route('admin.comptes.store') }}">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Nom complet</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10"
                               placeholder="Jean Dupont" />
                        @error('name')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10"
                               placeholder="jean@exemple.com" />
                        @error('email')<p class="text-[#EF4444] text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Rôle</label>
                        <select name="role" class="w-full px-3 py-2.5 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white">
                            <option value="user">Utilisateur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-[11px] text-[#6B7280] flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">info</span>
                        Un mot de passe provisoire sera généré et envoyé par email.
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm text-[#6B7280] hover:text-[#1F2937] border border-[#E5E7EB] rounded-lg transition-colors">Annuler</button>
                        <button type="submit" class="btn-primary flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base">send</span>
                            Créer et envoyer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Tableau des comptes --}}
<div class="soft-card overflow-x-auto" x-data="{ editId: null, editName: '', editEmail: '', editRole: '' }">

    {{-- Barre outils --}}
    <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center gap-3 flex-wrap bg-white">
        <h3 class="font-headline text-sm font-semibold text-[#1F2937] flex-1">Comptes utilisateurs</h3>
        <form method="GET" action="{{ route('admin.index') }}" class="flex items-center gap-2">
            <div class="flex items-center border border-[#E5E7EB] rounded-lg overflow-hidden focus-within:border-[#002452] bg-white">
                <span class="pl-2.5 material-symbols-outlined text-sm text-[#9CA3AF]">search</span>
                <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher..."
                       class="px-2 py-2 text-xs bg-transparent outline-none w-36" />
            </div>
            <select name="statut" onchange="this.form.submit()"
                    class="px-2.5 py-2 border border-[#E5E7EB] rounded-lg text-xs bg-white outline-none focus:border-[#002452]">
                <option value="tous" {{ $statut==='tous'?'selected':'' }}>Tous</option>
                <option value="actif" {{ $statut==='actif'?'selected':'' }}>Actifs</option>
                <option value="inactif" {{ $statut==='inactif'?'selected':'' }}>Inactifs</option>
            </select>
        </form>
    </div>

    {{-- Contenu tableau --}}
    <div class="min-w-[680px]">

    {{-- En-têtes --}}
    <div class="flex px-4 py-2 bg-[#F8FAFC] border-b border-[#E5E7EB] text-[10px] font-bold uppercase tracking-wide text-[#6B7280]">
        <div class="flex-1 min-w-0">Utilisateur</div>
        <div class="w-32 text-center flex-shrink-0">Rôle</div>
        <div class="w-24 text-center flex-shrink-0">Statut</div>
        <div class="w-44 text-center flex-shrink-0">Activité</div>
        <div class="w-32 text-right flex-shrink-0 pr-1">Actions</div>
    </div>

    <div class="divide-y divide-[#F3F4F6]">
        @forelse($users as $u)
        @php
            $roleColor = match($u->role) {
                'super_admin' => ['bg' => '#002452', 'text' => '#fff', 'label' => 'Super Admin'],
                'admin'       => ['bg' => '#EEF2FF', 'text' => '#4f46e5', 'label' => 'Admin'],
                default       => ['bg' => '#F3F4F6', 'text' => '#6B7280', 'label' => 'Utilisateur'],
            };
            $initials = strtoupper(substr($u->name, 0, 1));
            $colors   = ['#6366F1','#006c49','#D97706','#DC2626','#002452'];
            $avatarBg = $colors[$u->id % count($colors)];
        @endphp

        <div class="px-4 py-2 hover:bg-[#F8FAFC] transition-colors">
            {{-- Ligne principale --}}
            <div class="flex items-center gap-2">
                {{-- Utilisateur --}}
                <div class="flex-1 flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-[10px]"
                         style="background:{{ $avatarBg }}">{{ $initials }}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-[#1F2937] truncate">{{ $u->name }}</p>
                        <p class="text-[10px] text-[#6B7280] truncate">{{ $u->email }}</p>
                    </div>
                </div>

                {{-- Rôle --}}
                <div class="w-32 flex justify-center flex-shrink-0">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap"
                          style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['text'] }}">
                        {{ $roleColor['label'] }}
                    </span>
                </div>

                {{-- Statut --}}
                <div class="w-24 flex justify-center flex-shrink-0">
                    @if($u->is_active)
                        <span class="flex items-center gap-1 text-[10px] font-bold text-[#065f46] bg-[#d1fae5] px-2 py-0.5 rounded-full whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#006c49] inline-block"></span>Actif
                        </span>
                    @else
                        <span class="flex items-center gap-1 text-[10px] font-bold text-[#991b1b] bg-[#fee2e2] px-2 py-0.5 rounded-full whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#DC2626] inline-block"></span>Suspendu
                        </span>
                    @endif
                </div>

                {{-- Activité --}}
                <div class="w-44 text-center flex-shrink-0">
                    <p class="text-[10px] text-[#6B7280] whitespace-nowrap">{{ $u->budgets_count }} bdg. &bull; {{ $u->created_at->diffForHumans() }}</p>
                </div>

                {{-- Actions --}}
                <div class="w-32 flex items-center justify-end gap-1 flex-shrink-0">
                    @if($u->id !== auth()->id())

                    {{-- Éditer --}}
                    <button @click="editId === {{ $u->id }} ? editId = null : (editId = {{ $u->id }}, editName = '{{ addslashes($u->name) }}', editEmail = '{{ $u->email }}', editRole = '{{ $u->role }}')"
                            class="p-1.5 rounded-lg text-[#6B7280] hover:text-[#002452] hover:bg-[#EEF2FF] transition-colors" title="Modifier">
                        <span class="material-symbols-outlined text-base">edit</span>
                    </button>

                    {{-- Logs --}}
                    <a href="{{ route('admin.comptes.logs', $u) }}"
                       class="p-1.5 rounded-lg text-[#6B7280] hover:text-[#6366F1] hover:bg-[#EEF2FF] transition-colors" title="Historique">
                        <span class="material-symbols-outlined text-base">history</span>
                    </a>

                    {{-- Réinitialiser mot de passe --}}
                    <form method="POST" action="{{ route('admin.comptes.reset_password', $u) }}"
                          onsubmit="return confirm('Réinitialiser le mot de passe de {{ addslashes($u->name) }} ? Un nouveau sera généré.')">
                        @csrf
                        <button type="submit" title="Réinitialiser le mot de passe"
                                class="p-1.5 rounded-lg text-[#6B7280] hover:text-[#4f46e5] hover:bg-[#EEF2FF] transition-colors">
                            <span class="material-symbols-outlined text-base">lock_reset</span>
                        </button>
                    </form>

                    {{-- Impersonnifier --}}
                    @if($u->is_active)
                    <form method="POST" action="{{ route('admin.comptes.impersonner', $u) }}">
                        @csrf
                        <button type="submit" title="Accéder à ce compte"
                                class="p-1.5 rounded-lg text-[#6B7280] hover:text-[#D97706] hover:bg-[#fffbeb] transition-colors">
                            <span class="material-symbols-outlined text-base">switch_account</span>
                        </button>
                    </form>
                    @endif

                    {{-- Toggle suspend --}}
                    @if($u->role !== 'super_admin')
                    <form method="POST" action="{{ route('admin.comptes.toggle', $u) }}">
                        @csrf
                        <button type="submit"
                                title="{{ $u->is_active ? 'Suspendre' : 'Réactiver' }}"
                                class="p-1.5 rounded-lg transition-colors {{ $u->is_active ? 'text-[#6B7280] hover:text-[#DC2626] hover:bg-[#fee2e2]' : 'text-[#6B7280] hover:text-[#006c49] hover:bg-[#d1fae5]' }}">
                            <span class="material-symbols-outlined text-base">{{ $u->is_active ? 'block' : 'check_circle' }}</span>
                        </button>
                    </form>
                    @endif

                    @else
                    <span class="text-[10px] text-[#9CA3AF] italic px-2">Vous</span>
                    @endif
                </div>
            </div> {{-- fin flex ligne --}}

            {{-- Formulaire d'édition inline (slide-down) --}}
            <div x-show="editId === {{ $u->id }}" x-transition class="mt-2 border-t border-[#E5E7EB] pt-3">
                <form method="POST" action="{{ route('admin.comptes.update', $u) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase tracking-wide">Nom</label>
                            <input type="text" name="name" x-model="editName" required
                                   class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase tracking-wide">Email</label>
                            <input type="email" name="email" x-model="editEmail" required
                                   class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase tracking-wide">Rôle</label>
                            <select name="role" x-model="editRole"
                                    class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#002452] bg-white">
                                <option value="user">Utilisateur</option>
                                <option value="admin">Administrateur</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    {{-- Définir un nouveau mot de passe manuellement (optionnel) --}}
                    <div class="mb-3 p-3 bg-[#FFFBEB] border border-[#FDE68A] rounded-xl">
                        <p class="text-[10px] font-bold text-[#D97706] uppercase tracking-wide mb-2 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">lock_reset</span>
                            Définir un nouveau mot de passe (optionnel)
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase tracking-wide">Nouveau mot de passe</label>
                                <input type="password" name="new_password" minlength="8"
                                       placeholder="Laisser vide = pas de changement"
                                       class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/10 bg-white" />
                                <p class="text-[10px] text-[#9CA3AF] mt-0.5">Min. 8 caractères</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#6B7280] mb-1 uppercase tracking-wide">Confirmer</label>
                                <input type="password" name="new_password_confirmation"
                                       placeholder="Répéter le mot de passe"
                                       class="w-full px-3 py-2 border border-[#E5E7EB] rounded-lg text-sm focus:outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/10 bg-white" />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 justify-end">
                        <button type="button" @click="editId = null"
                                class="px-3 py-1.5 text-xs text-[#6B7280] border border-[#E5E7EB] rounded-lg hover:bg-[#F3F4F6] transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="btn-primary text-xs px-3 py-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">save</span> Enregistrer
                        </button>
                    </div>
                </form>
            </div>

        </div>
        @empty
        <div class="px-5 py-14 text-center">
            <span class="material-symbols-outlined text-5xl text-[#E5E7EB]">group_off</span>
            <p class="text-sm text-[#6B7280] mt-3">Aucun compte trouvé.</p>
        </div>
        @endforelse
    </div>
    </div>{{-- fin min-w --}}

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-[#E5E7EB] flex justify-between items-center text-xs text-[#6B7280]">
        <span>{{ $users->total() }} compte(s)</span>
        {{ $users->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
