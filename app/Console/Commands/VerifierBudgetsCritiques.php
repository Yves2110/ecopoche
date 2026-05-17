<?php

namespace App\Console\Commands;

use App\Mail\AlerteSoldeRouge;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class VerifierBudgetsCritiques extends Command
{
    protected $signature   = 'ecopoche:verifier-budgets-critiques';
    protected $description = 'Envoie un email d\'alerte aux utilisateurs dont le budget est en zone critique (>= 90%)';

    public function handle(): void
    {
        $mois  = now()->month;
        $annee = now()->year;

        $users = User::where('is_active', true)->where('notifs_email', true)->get();
        $count = 0;

        foreach ($users as $user) {
            $budget = Budget::where('user_id', $user->id)
                ->where('mois', $mois)
                ->where('annee', $annee)
                ->first();

            if (!$budget) continue;

            $revenus         = $budget->revenus()->get();
            $totalDepensable = (float) $revenus->where('quota_applique', true)->sum('montant_quota');
            $budgetTotal     = (float) $budget->salaire_fixe + $totalDepensable;
            $totalDepenses   = (float) $budget->depenses()->sum('montant');
            $solde           = $budgetTotal - $totalDepenses;
            $ratio           = $budgetTotal > 0 ? $totalDepenses / $budgetTotal : 0;

            // Seuil : solde négatif OU ratio >= 90%
            if ($solde >= 0 && $ratio < 0.90) continue;

            Mail::to($user->email)->send(new AlerteSoldeRouge(
                user:        $user,
                budget:      $budget,
                solde:       $solde,
                ratio:       $ratio,
                budgetTotal: $budgetTotal,
            ));

            $count++;
        }

        $this->info("Alertes critiques envoyées à {$count} utilisateur(s).");
    }
}
