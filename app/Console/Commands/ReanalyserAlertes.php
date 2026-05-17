<?php

namespace App\Console\Commands;

use App\Models\Alerte;
use App\Models\Budget;
use App\Models\User;
use App\Services\AlerteService;
use Illuminate\Console\Command;

class ReanalyserAlertes extends Command
{
    protected $signature = 'alertes:reanalyser {--mois=} {--annee=}';
    protected $description = 'Supprime les alertes du mois courant et les regénère avec les calculs à jour';

    public function handle(): void
    {
        $mois  = (int) ($this->option('mois')  ?? now()->month);
        $annee = (int) ($this->option('annee') ?? now()->year);

        $this->info("Réanalyse des alertes pour {$mois}/{$annee}...");

        // 1. Supprimer les alertes du mois (sauf info quota_applique qui sont historiques)
        $deleted = Alerte::whereJsonContains('meta->mois', $mois)
            ->whereJsonContains('meta->annee', $annee)
            ->whereNotIn('type', ['quota_applique'])
            ->delete();

        $this->info("→ {$deleted} alerte(s) supprimée(s)");

        // 2. Réanalyser tous les budgets actifs
        $users = User::where('is_active', true)->get();
        $count = 0;

        foreach ($users as $user) {
            $budget = Budget::where('user_id', $user->id)
                ->where('mois', $mois)
                ->where('annee', $annee)
                ->first();

            if (!$budget) continue;

            AlerteService::analyserBudget($user, $budget);
            $count++;
        }

        $this->info("→ {$count} budget(s) réanalysé(s)");
        $this->info('Terminé.');
    }
}
