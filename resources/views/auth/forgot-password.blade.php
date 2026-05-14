<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mot de passe oublié — EcoPoche</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-[#002452] rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <span class="material-symbols-outlined text-[#6ffbbe] text-3xl" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span>
        </div>
        <h1 class="font-headline text-2xl font-bold text-[#1F2937]">Mot de passe oublié</h1>
        <p class="text-sm text-[#6B7280] mt-1">Saisissez votre email pour recevoir un lien de réinitialisation</p>
    </div>

    {{-- Status --}}
    @if(session('status'))
    <div class="mb-4 p-3.5 rounded-xl bg-[#d1fae5] border border-[#6ee7b7] text-[#065f46] text-sm font-medium flex items-center gap-2">
        <span class="material-symbols-outlined text-base flex-shrink-0">check_circle</span>
        {{ session('status') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-[#E5E7EB] p-6">
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-[#6B7280] mb-1.5 uppercase tracking-wide">Adresse email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-3.5 py-2.5 border @error('email') border-[#EF4444] @else border-[#E5E7EB] @enderror rounded-xl text-sm focus:outline-none focus:border-[#002452] focus:ring-2 focus:ring-[#002452]/10 transition-colors"
                       placeholder="votre@email.com" />
                @error('email')
                <p class="text-[#EF4444] text-xs mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">error</span>{{ $message }}
                </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-[#002452] text-white font-semibold rounded-xl text-sm hover:bg-[#1B3A6B] transition-colors flex items-center justify-center gap-2 shadow">
                <span class="material-symbols-outlined text-base">send</span>
                Envoyer le lien
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

</body>
</html>
