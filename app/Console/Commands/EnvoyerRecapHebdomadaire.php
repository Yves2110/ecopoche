<?php

namespace App\Console\Commands;

use App\Mail\RecapHebdomadaire;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnvoyerRecapHebdomadaire extends Command
{
    protected $signature   = 'ecopoche:recap-hebdomadaire';
    protected $description = 'Envoie le récapitulatif budgétaire hebdomadaire à tous les utilisateurs actifs';

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

            $topCategories = $budget->depenses()
                ->with('categorie')
                ->get()
                ->groupBy('categorie_id')
                ->map(fn($items) => [
                    'nom'   => $items->first()->categorie?->nom ?? 'Autres',
                    'total' => (float) $items->sum('montant'),
                    'nb'    => $items->count(),
                ])
                ->sortByDesc('total')
                ->take(5)
                ->values()
                ->toArray();

            Mail::to($user->email)->send(new RecapHebdomadaire(
                user:          $user,
                budget:        $budget,
                totalDepenses: $totalDepenses,
                budgetTotal:   $budgetTotal,
                solde:         $solde,
                ratio:         $ratio,
                topCategories: $topCategories,
            ));

            $count++;
        }

        $this->info("Récapitulatif envoyé à {$count} utilisateur(s).");
    }
}
