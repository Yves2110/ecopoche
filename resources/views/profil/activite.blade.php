<x-layouts.app title="Activité" pageTitle="Historique d'activité" pageSubtitle="Connexions et événements récents">

<div class="mb-4">
    <a href="{{ route('profil.index') }}" class="inline-flex items-center gap-1 text-sm text-[#6B7280] hover:text-[#002452]">
        <span class="material-symbols-outlined text-base">arrow_back</span> Retour aux paramètres
    </a>
</div>

<div class="soft-card overflow-hidden">
    <div class="px-5 py-4 border-b border-[#E5E7EB]">
        <h3 class="font-headline text-base font-semibold text-[#1F2937]">Vos événements</h3>
        <p class="text-xs text-[#6B7280] mt-1">Journal en lecture seule ({{ $logs->total() }} entrée(s))</p>
    </div>
    <div class="divide-y divide-[#F3F4F6]">
        @forelse($logs as $log)
        <div class="px-5 py-3 flex items-start gap-3">
            <span class="material-symbols-outlined text-[#6B7280] text-lg mt-0.5">history</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-[#1F2937]">{{ $log->description }}</p>
                <p class="text-[10px] text-[#9CA3AF] mt-0.5">
                    {{ $log->created_at->translatedFormat('d M Y H:i') }}
                    @if($log->ip_address) &bull; {{ $log->ip_address }} @endif
                </p>
            </div>
            <span class="text-[10px] font-bold uppercase text-[#6B7280] bg-[#F3F4F6] px-2 py-0.5 rounded">{{ $log->action }}</span>
        </div>
        @empty
        <p class="px-5 py-10 text-center text-sm text-[#6B7280]">Aucune activité enregistrée.</p>
        @endforelse
    </div>
    @if($logs->hasPages())
    <div class="px-5 py-3 border-t border-[#E5E7EB]">{{ $logs->links() }}</div>
    @endif
</div>

</x-layouts.app>
