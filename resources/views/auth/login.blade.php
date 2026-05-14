<x-layouts.guest title="Connexion">
<div class="w-full max-w-md">

    {{-- Card principale --}}
    <div class="soft-card p-8">

        {{-- Logo --}}
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-[#002452] flex items-center justify-center">
                <span class="material-symbols-outlined text-[#6ffbbe] text-xl" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span>
            </div>
            <div>
                <h1 class="font-headline text-xl font-bold text-[#002452]">EcoPoche</h1>
                <p class="text-xs text-[#6B7280]">Gestion budgétaire personnelle</p>
            </div>
        </div>

        <h2 class="font-headline text-2xl font-bold text-[#1F2937] mb-1">Bienvenue</h2>
        <p class="text-sm text-[#6B7280] mb-6">Connectez-vous à votre compte pour continuer.</p>

        {{-- Erreurs --}}
        @if($errors->any())
        <div class="alert-banner alert-red mb-5">
            <span class="material-symbols-outlined text-[#991b1b] text-xl flex-shrink-0" style="font-variation-settings:'FILL' 1;">error</span>
            <div>
                @foreach($errors->all() as $error)
                <p class="text-sm text-[#991b1b]">{{ $error }}</p>
                @endforeach
            </div>
        </div>
        @endif

        @if(session('status'))
        <div class="alert-banner alert-green mb-5">
            <span class="material-symbols-outlined text-[#065f46] text-xl" style="font-variation-settings:'FILL' 1;">check_circle</span>
            <p class="text-sm text-[#065f46]">{{ session('status') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-[#1F2937] mb-1.5">
                    Adresse email
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6B7280] text-lg">email</span>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="votre@email.com"
                        class="w-full pl-10 pr-4 py-2.5 border border-[#E5E7EB] rounded-lg text-sm text-[#1F2937] bg-white focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10 transition-all {{ $errors->has('email') ? 'border-[#EF4444]' : '' }}"
                    />
                </div>
            </div>

            {{-- Mot de passe --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-[#1F2937] mb-1.5">
                    Mot de passe
                </label>
                <div class="relative" x-data="{ show: false }">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#6B7280] text-lg">lock</span>
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-10 py-2.5 border border-[#E5E7EB] rounded-lg text-sm text-[#1F2937] bg-white focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10 transition-all {{ $errors->has('password') ? 'border-[#EF4444]' : '' }}"
                    />
                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#6B7280] hover:text-[#002452] transition-colors">
                        <span class="material-symbols-outlined text-lg" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
            </div>

            {{-- Remember + Forgot --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-[#E5E7EB] text-[#002452] focus:ring-[#002452]/20" />
                    <span class="text-sm text-[#6B7280]">Se souvenir de moi</span>
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-primary w-full py-3 flex items-center justify-center gap-2 mt-2">
                <span class="material-symbols-outlined text-lg">login</span>
                Se connecter
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-[#6B7280] mt-6">
        EcoPoche &copy; {{ date('Y') }} — Votre budget, votre contrôle.
    </p>
</div>
</x-layouts.guest>
