<x-layouts.guest title="Nouveau mot de passe">
<div class="w-full max-w-sm">

    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-[#002452] rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <span class="material-symbols-outlined text-[#6ffbbe] text-3xl" style="font-variation-settings:'FILL' 1;">lock_reset</span>
        </div>
        <h1 class="font-headline text-2xl font-bold text-[#1F2937]">Nouveau mot de passe</h1>
        <p class="text-sm text-[#6B7280] mt-1">Choisissez un mot de passe sécurisé</p>
    </div>

    @error('token')
    <div class="mb-4 p-3.5 rounded-xl bg-[#fee2e2] border border-[#fca5a5] text-[#991b1b] text-sm font-medium flex items-center gap-2">
        <span class="material-symbols-outlined text-base flex-shrink-0">error</span>
        {{ $message }}
        <a href="{{ route('password.request') }}" class="ml-auto underline font-bold whitespace-nowrap">Nouveau lien</a>
    </div>
    @enderror

    <div class="soft-card p-6">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />

            <div>
                <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required readonly
                       class="w-full px-3.5 py-2.5 border border-[#E5E7EB] rounded-xl text-sm bg-[#F8FAFC] text-[#6B7280] cursor-not-allowed" />
            </div>

            <x-password-input
                name="password"
                label="Nouveau mot de passe"
                placeholder="Minimum 8 caractères"
                :required="true"
                :autofocus="true"
                autocomplete="new-password"
                minlength="8"
                inputClass="rounded-xl"
            />

            <x-password-input
                name="password_confirmation"
                label="Confirmer le mot de passe"
                placeholder="Répéter le mot de passe"
                :required="true"
                autocomplete="new-password"
                inputClass="rounded-xl"
            />

            <button type="submit"
                    class="w-full py-2.5 bg-[#002452] text-white font-semibold rounded-xl text-sm hover:bg-[#1B3A6B] transition-colors flex items-center justify-center gap-2 shadow">
                <span class="material-symbols-outlined text-base">lock_reset</span>
                Réinitialiser le mot de passe
            </button>
        </form>
    </div>

    <div class="text-center mt-5">
        <a href="{{ route('login') }}" class="text-sm text-[#6B7280] hover:text-[#002452] transition-colors flex items-center justify-center gap-1">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Retour à la connexion
        </a>
    </div>
</div>
</x-layouts.guest>
