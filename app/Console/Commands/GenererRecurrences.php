<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RecurrenceService;
use Illuminate\Console\Command;

class GenererRecurrences extends Command
{
    protected $signature = 'ecopoche:generer-recurrences';

    protected $description = 'Génère les dépenses et revenus récurrents du mois en cours';

    public function handle(): int
    {
        $total = 0;

        User::where('is_active', true)->each(function (User $user) use (&$total) {
            $total += RecurrenceService::genererPourUtilisateur($user);
        });

        $this->info("Récurrences générées : {$total} opération(s).");

        return self::SUCCESS;
    }
}
