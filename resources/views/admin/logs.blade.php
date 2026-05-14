<x-layouts.app titre="Logs — {{ $user->name }}">

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.index') }}" class="p-2 rounded-lg hover:bg-[#F3F4F6] transition-colors text-[#6B7280]">
        <span class="material-symbols-outlined text-base">arrow_back</span>
    </a>
    <div>
        <h1 class="font-headline text-2xl font-bold text-[#002452]">Logs d'activité</h1>
        <p class="text-sm text-[#6B7280]">{{ $user->name }} &bull; {{ $user->email }}</p>
    </div>
</div>

<div class="soft-card overflow-hidden">
    <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
        <p class="text-sm font-semibold text-[#1F2937]">{{ $logs->total() }} événement(s)</p>
    </div>

    @php
    $actionConfig = [
        'connexion'           => ['login',        '#006c49', '#d1fae5'],
        'deconnexion'         => ['logout',        '#6B7280', '#F3F4F6'],
        'compte_cree'         => ['person_add',    '#6366F1', '#EEF2FF'],
        'compte_reactive'     => ['check_circle',  '#006c49', '#d1fae5'],
        'compte_suspendu'     => ['block',         '#DC2626', '#fee2e2'],
        'impersonnification'  => ['switch_account','#D97706', '#fffbeb'],
        'depense_ajoutee'     => ['add_shopping_cart','#002452','#EEF2FF'],
        'depense_supprimee'   => ['remove_shopping_cart','#DC2626','#fee2e2'],
        'salaire_modifie'     => ['edit',          '#D97706', '#fffbeb'],
        'revenu_ajoute'       => ['payments',      '#006c49', '#d1fae5'],
    ];
    @endphp

    <div class="divide-y divide-[#F3F4F6]">
        @forelse($logs as $log)
        @php [$icon, $color, $bg] = $actionConfig[$log->action] ?? ['info', '#6B7280', '#F3F4F6']; @endphp
        <div class="flex items-start gap-4 px-5 py-3.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                 style="background:{{ $bg }}">
                <span class="material-symbols-outlined text-sm" style="color:{{ $color }}">{{ $icon }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1F2937]">{{ $log->description }}</p>
                <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" style="background:{{ $bg }};color:{{ $color }}">
                        {{ $log->action }}
                    </span>
                    @if($log->ip_address)
                    <span class="text-[10px] text-[#9CA3AF]">IP : {{ $log->ip_address }}</span>
                    @endif
                    <span class="text-[10px] text-[#9CA3AF]">{{ $log->created_at->translatedFormat('d M Y à H:i') }}</span>
                </div>
            </div>
            <span class="text-[11px] text-[#9CA3AF] flex-shrink-0">{{ $log->created_at->diffForHumans() }}</span>
        </div>
        @empty
        <div class="px-5 py-12 text-center">
            <span class="material-symbols-outlined text-4xl text-[#E5E7EB]">history_toggle_off</span>
            <p class="text-sm text-[#6B7280] mt-2">Aucune activité enregistrée pour ce compte.</p>
        </div>
        @endforelse
    </div>

    @if($logs->hasPages())
    <div class="px-5 py-3 border-t border-[#E5E7EB]">
        {{ $logs->links() }}
    </div>
    @endif
</div>

</x-layouts.app>
