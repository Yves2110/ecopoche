<?php

namespace App\Services;

use App\Mail\AlerteSoldeRouge;
use App\Models\Alerte;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AlerteService
{
    /**
     * Analyse le budget du mois courant et génère les alertes nécessaires.
     * Evite les doublons : une seule alerte par type par budget par mois.
     */
    public static function analyserBudget(User $user, Budget $budget): void
    {
        $revenus = $budget->revenus()->get();
        $totalDepensable = (float) $revenus->where('quota_applique', true)->sum('montant_quota');
        $totalDepenses   = (float) $budget->depenses()->sum('montant');
        $salaire         = (float) $budget->salaire_fixe;
        $budgetTotal     = $salaire + $totalDepensable;
        $solde           = $budgetTotal - $totalDepenses;

        if ($budgetTotal <= 0) return;

        $ratio = $totalDepenses / $budgetTotal;

        // Seuils configurables par l'utilisateur (défauts : 70% / 90%)
        $seuilAttention = ($user->seuil_attention ?? 70) / 100;
        $seuilCritique  = ($user->seuil_critique  ?? 90) / 100;
        $seuilPlafond   = ($user->seuil_plafond_cat ?? 80) / 100;

        // ── Santé budgétaire globale ──────────────────────────────────────────
        $ratioFmt  = number_format($ratio * 100, 0);
        $soldeFmt  = self::fmt(abs($solde));
        $budgetFmt = self::fmt($budgetTotal);

        if ($solde < 0) {
            $depassement = abs($solde);
            $suggestions = self::suggestionsReduction($budget, $depassement);
            $estNouveau  = self::creer($user, 'critique', 'danger',
                "Budget dépassé de {$soldeFmt} FCFA ({$ratioFmt}% utilisé sur {$budgetFmt} FCFA)." .
                ($suggestions ? " Pistes : {$suggestions}" : ' Réduisez vos dépenses immédiatement.'),
                ['mois' => $budget->mois, 'annee' => $budget->annee, 'solde' => $solde,
                 'ratio' => $ratio * 100, 'suggestions' => $suggestions],
                $budget
            );
            if ($estNouveau) {
                try {
                    Mail::to($user->email)->send(new AlerteSoldeRouge($user, $budget, $solde, $ratio, $budgetTotal));
                } catch (\Throwable) {}
            }
        } elseif ($ratio >= $seuilCritique) {
            $estNouveau = self::creer($user, 'attention', 'warning',
                "Seuil critique : {$ratioFmt}% du budget consommé. Il ne reste que {$soldeFmt} FCFA sur {$budgetFmt} FCFA. Évitez toute dépense non essentielle.",
                ['mois' => $budget->mois, 'annee' => $budget->annee, 'ratio' => $ratio * 100, 'solde' => $solde],
                $budget
            );
            if ($estNouveau) {
                try {
                    Mail::to($user->email)->send(new AlerteSoldeRouge($user, $budget, $solde, $ratio, $budgetTotal));
                } catch (\Throwable) {}
            }
        } elseif ($ratio >= $seuilAttention) {
            self::creer($user, 'attention', 'warning',
                "{$ratioFmt}% du budget utilisé. Restant : {$soldeFmt} FCFA. Surveillez vos dépenses jusqu'à la fin du mois.",
                ['mois' => $budget->mois, 'annee' => $budget->annee, 'ratio' => $ratio * 100, 'solde' => $solde],
                $budget
            );
        } elseif ($ratio < $seuilAttention && $totalDepenses > 0) {
            self::creer($user, 'budget_sain', 'info',
                "Budget sain ce mois — {$ratioFmt}% utilisé. Solde restant : {$soldeFmt} FCFA.",
                ['mois' => $budget->mois, 'annee' => $budget->annee],
                $budget
            );
        }

        // ── Plafonds par catégorie ────────────────────────────────────────────
        $depensesParCat = $budget->depenses()
            ->with('categorie')
            ->get()
            ->groupBy('categorie_id');

        foreach ($depensesParCat as $catId => $items) {
            $cat = $items->first()->categorie;
            if (!$cat || !$cat->plafond_mensuel || $cat->plafond_mensuel <= 0) continue;

            $total   = $items->sum('montant');
            $pct     = $total / $cat->plafond_mensuel;
            $pctFmt  = number_format($pct * 100, 0);
            $restant = max(0, $cat->plafond_mensuel - $total);

            if ($pct >= 1.0) {
                $depasse = $total - $cat->plafond_mensuel;
                self::creer($user, 'plafond_depasse', 'danger',
                    "Plafond {$cat->nom} dépassé de " . self::fmt($depasse) . " FCFA ({$pctFmt}% — " . self::fmt($total) . ' / ' . self::fmt($cat->plafond_mensuel) . ' FCFA). Bloquez toute nouvelle dépense dans cette catégorie.',
                    ['categorie_id' => $catId, 'categorie' => $cat->nom, 'total' => $total,
                     'plafond' => $cat->plafond_mensuel, 'depasse' => $depasse, 'pct' => $pctFmt],
                    $budget
                );
            } elseif ($pct >= $seuilPlafond) {
                self::creer($user, 'plafond_80', 'warning',
                    "{$cat->nom} à {$pctFmt}% du plafond. Reste : " . self::fmt($restant) . ' FCFA sur ' . self::fmt($cat->plafond_mensuel) . ' FCFA. Soyez vigilant.',
                    ['categorie_id' => $catId, 'categorie' => $cat->nom, 'pct' => $pctFmt,
                     'restant' => $restant, 'total' => $total, 'plafond' => $cat->plafond_mensuel],
                    $budget
                );
            }
        }

        // ── Épargne ─────────────────────────────────────────────────────────────────────────────────
        $epargne = $budget->epargne;
        if ($epargne && $epargne->objectif > 0 && $epargne->deficit > 0) {
            $pctEpargne = $epargne->objectif > 0
                ? number_format($epargne->reel / $epargne->objectif * 100, 0)
                : 0;
            self::creer($user, 'epargne_deficit', 'warning',
                'Objectif épargne non atteint : ' . self::fmt($epargne->reel) . ' / ' . self::fmt($epargne->objectif) .
                ' FCFA (' . $pctEpargne . '%). Déficit : ' . self::fmt($epargne->deficit) .
                ' FCFA. Conseil : réduire les dépenses de loisirs ou imprévus pour atteindre l\'objectif.',
                ['deficit' => $epargne->deficit, 'objectif' => $epargne->objectif,
                 'reel' => $epargne->reel, 'pct' => $pctEpargne],
                $budget
            );
        }
    }

    /**
     * Analyse les catégories les plus dépensées et propose des pistes de réduction.
     */
    private static function suggestionsReduction(Budget $budget, float $depassement): string
    {
        $top = $budget->depenses()
            ->with('categorie')
            ->get()
            ->groupBy('categorie_id')
            ->map(fn($items) => [
                'nom'   => $items->first()->categorie?->nom ?? 'Autres',
                'total' => (float) $items->sum('montant'),
                'nb'    => $items->count(),
            ])
            ->sortByDesc('total')
            ->take(3)
            ->values();

        if ($top->isEmpty()) return '';

        $lignes = $top->map(fn($cat) =>
            "{$cat['nom']} (" . self::fmt($cat['total']) . ' FCFA, ' . $cat['nb'] . ' op.)'
        );

        return 'Réduire : ' . $lignes->implode(' · ') . '.';
    }

    /**
     * Alerte ponctuelle : quota 30% appliqué sur un revenu variable.
     */
    public static function quotaApplique(User $user, float $brut, float $depensable, float $reserve): void
    {
        $taux = $user->quota_taux ?? 30;
        $tauxReserve = 100 - $taux;
        Alerte::create([
            'user_id' => $user->id,
            'type'    => 'quota_applique',
            'gravite' => 'info',
            'message' => "Revenu variable enregistré. Dépensable : " . self::fmt($depensable) . " FCFA ({$taux}%). Réserve bloquée : " . self::fmt($reserve) . " FCFA ({$tauxReserve}%).",
            'meta'    => ['brut' => $brut, 'depensable' => $depensable, 'reserve' => $reserve, 'taux' => $taux],
        ]);
    }

    /**
     * Crée une alerte en évitant les doublons (même type + même budget_id dans meta).
     */
    private static function creer(User $user, string $type, string $gravite, string $message, array $meta, Budget $budget): bool
    {
        $exists = Alerte::where('user_id', $user->id)
            ->where('type', $type)
            ->whereJsonContains('meta->mois', $budget->mois)
            ->whereJsonContains('meta->annee', $budget->annee)
            ->whereNull('lu_at')
            ->exists();

        if (!$exists) {
            Alerte::create([
                'user_id' => $user->id,
                'type'    => $type,
                'gravite' => $gravite,
                'message' => $message,
                'meta'    => array_merge($meta, ['mois' => $budget->mois, 'annee' => $budget->annee]),
            ]);
            return true;
        }

        return false;
    }

    private static function fmt(float $n): string
    {
        return number_format((int) $n, 0, ',', "\u{00A0}");
    }
}
