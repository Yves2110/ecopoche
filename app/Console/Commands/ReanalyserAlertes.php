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

        // 1. Récupérer les IDs des alertes du mois (filtrage PHP - compatible MySQL sans JSON_CONTAINS)
        $idsToDelete = Alerte::whereNotIn('type', ['quota_applique'])
            ->get(['id', 'meta'])
            ->filter(function ($a) use ($mois, $annee) {
                $meta = is_array($a->meta) ? $a->meta : (json_decode($a->meta ?? '[]', true) ?: []);
                return ($meta['mois'] ?? null) == $mois && ($meta['annee'] ?? null) == $annee;
            })
            ->pluck('id');

        $deleted = $idsToDelete->isNotEmpty()
            ? Alerte::whereIn('id', $idsToDelete)->delete()
            : 0;

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
