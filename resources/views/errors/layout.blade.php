<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $code }} — EcoPoche</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center p-6">
<div class="text-center max-w-md mx-auto">
    <div class="w-20 h-20 rounded-2xl mx-auto mb-6 flex items-center justify-center {{ $iconBg }}">
        <span class="material-symbols-outlined text-4xl {{ $iconColor }}" style="font-variation-settings:'FILL' 1;">{{ $icon }}</span>
    </div>
    <p class="text-6xl font-headline font-bold text-[#002452] mb-2">{{ $code }}</p>
    <h1 class="text-xl font-headline font-bold text-[#1F2937] mb-3">{{ $titre }}</h1>
    <p class="text-sm text-[#6B7280] mb-8 leading-relaxed">{{ $message }}</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        @auth
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#002452] text-white rounded-xl text-sm font-semibold hover:bg-[#1B3A6B] transition-colors">
            <span class="material-symbols-outlined text-base">dashboard</span>
            Retour au tableau de bord
        </a>
        @else
        <a href="{{ route('login') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#002452] text-white rounded-xl text-sm font-semibold hover:bg-[#1B3A6B] transition-colors">
            <span class="material-symbols-outlined text-base">login</span>
            Se connecter
        </a>
        @endauth
        <a href="javascript:history.back()"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-[#E5E7EB] text-[#6B7280] rounded-xl text-sm font-semibold hover:bg-[#F3F4F6] transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Page précédente
        </a>
    </div>
    <div class="mt-10 flex items-center justify-center gap-2 text-[#002452]">
        <span class="material-symbols-outlined text-lg" style="font-variation-settings:'FILL' 1;">account_balance_wallet</span>
        <span class="font-headline font-bold text-sm">EcoPoche</span>
    </div>
</div>
</body>
</html>
