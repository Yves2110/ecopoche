<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Models\Epargne;
use App\Models\User;
use App\Services\AlerteService;
use App\Services\BudgetPeriodService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RattacherBudgetsPeriode extends Command
{
    protected $signature = 'ecopoche:rattacher-budgets-periode
                            {email? : E-mail utilisateur (vide = tous)}
                            {--dry-run : Afficher sans modifier}';

    protected $description = 'Rattache revenus/dépenses au budget correspondant à la date (période budgétaire)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $query  = User::query()->where('is_active', true);

        if ($email = $this->argument('email')) {
            $query->where('email', $email);
        }

        $users = $query->get();
        if ($users->isEmpty()) {
            $this->error('Aucun utilisateur trouvé.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            $this->info("Utilisateur : {$user->email} (jour début = " . (BudgetPeriodService::jourDebut($user)) . ')');

            $movedRev = $movedDep = $movedSal = 0;

            foreach ($user->budgets()->with(['revenus', 'depenses'])->get() as $budget) {
                foreach ($budget->revenus as $revenu) {
                    $periode = BudgetPeriodService::resolvePeriodePourDate($user, Carbon::parse($revenu->date));
                    $dest    = $this->budgetPour($user, $periode['mois'], $periode['annee'], $dryRun);
                    if ($dest && $revenu->budget_id !== $dest->id) {
                        if (! $dryRun) {
                            $revenu->update(['budget_id' => $dest->id]);
                        }
                        $movedRev++;
                    }
                }

                foreach ($budget->depenses as $depense) {
                    $periode = BudgetPeriodService::resolvePeriodePourDate($user, Carbon::parse($depense->date));
                    $dest    = $this->budgetPour($user, $periode['mois'], $periode['annee'], $dryRun);
                    if ($dest && $depense->budget_id !== $dest->id) {
                        if (! $dryRun) {
                            $depense->update(['budget_id' => $dest->id]);
                        }
                        $movedDep++;
                    }
                }
            }

            // Salaire orphelin sur mauvaise clé : copier vers période courante si vide
            $courant = BudgetPeriodService::resolvePeriode($user);
            $budgetCourant = $this->budgetPour($user, $courant['mois'], $courant['annee'], $dryRun);

            foreach ($user->budgets as $budget) {
                if ($budget->id === $budgetCourant?->id) {
                    continue;
                }
                if ((float) $budget->salaire_fixe <= 0) {
                    continue;
                }
                if ($budgetCourant && (float) $budgetCourant->salaire_fixe <= 0) {
                    $hasOps = $budget->revenus()->exists() || $budget->depenses()->exists();
                    if (! $hasOps) {
                        if (! $dryRun) {
                            $budgetCourant->update([
                                'salaire_fixe'     => $budget->salaire_fixe,
                                'epargne_objectif' => $budget->epargne_objectif,
                            ]);
                            $budget->update(['salaire_fixe' => 0, 'epargne_objectif' => 0]);
                        }
                        $movedSal++;
                    }
                }
            }

            if (! $dryRun) {
                AlerteService::cloturerAlertesPeriodesExpirees($user);
                if ($budgetCourant) {
                    AlerteService::analyserBudget($user, $budgetCourant->fresh());
                }
            }

            $this->line("  → {$movedRev} revenu(s), {$movedDep} dépense(s), {$movedSal} salaire(s) ajusté(s)");
        }

        $this->info($dryRun ? 'Simulation terminée.' : 'Rattachement terminé.');

        return self::SUCCESS;
    }

    private function budgetPour(User $user, int $mois, int $annee, bool $dryRun): ?Budget
    {
        if ($dryRun) {
            return Budget::where('user_id', $user->id)->where('mois', $mois)->where('annee', $annee)->first()
                ?? new Budget(['user_id' => $user->id, 'mois' => $mois, 'annee' => $annee]);
        }

        return Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );
    }
}
