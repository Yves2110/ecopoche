<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AlerteService;
use Illuminate\Console\Command;

class VerifierEcheancesDettes extends Command
{
    protected $signature   = 'ecopoche:verifier-echeances-dettes';
    protected $description = 'Analyse les échéances des emprunts & prêts et notifie les utilisateurs concernés';

    public function handle(): void
    {
        $users = User::where('is_active', true)->get();
        $totalAlertes = 0;

        foreach ($users as $user) {
            $nb = AlerteService::analyserDettes($user);
            $totalAlertes += $nb;
        }

        $this->info("Vérification terminée. {$totalAlertes} alerte(s) créée(s) sur " . $users->count() . " utilisateur(s).");
    }
}
