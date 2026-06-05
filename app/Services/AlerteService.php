<?php

namespace App\Services;

use App\Mail\AlerteSoldeRouge;
use App\Mail\EcheanceDette;
use App\Mail\RemboursementDette;
use App\Models\Alerte;
use App\Models\Budget;
use App\Models\Dette;
use App\Models\Revenu;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlerteService
{
    /** Alertes liées à un budget (mois/année dans meta). */
    public const TYPES_BUDGET_PERIODE = [
        'critique', 'attention', 'budget_sain', 'plafond_80', 'plafond_depasse',
        'epargne_deficit', 'quota_applique', 'reajustement',
    ];

    /** Alertes indépendantes du mois budgétaire. */
    public const TYPES_DETTE = [
        'echeance_proche', 'echeance_j1', 'echeance_depassee', 'remboursement_partiel',
    ];

    /**
     * Marque comme lues les alertes budget des périodes passées.
     */
    public static function cloturerAlertesPeriodesExpirees(User $user): int
    {
        $courant = BudgetPeriodService::resolvePeriode($user);
        $count   = 0;

        Alerte::where('user_id', $user->id)
            ->whereNull('lu_at')
            ->whereIn('type', self::TYPES_BUDGET_PERIODE)
            ->get()
            ->each(function (Alerte $alerte) use ($courant, &$count) {
                $meta = is_array($alerte->meta) ? $alerte->meta : [];
                $m    = $meta['mois'] ?? null;
                $a    = $meta['annee'] ?? null;
                if ($m === null || ($m == $courant['mois'] && $a == $courant['annee'])) {
                    return;
                }
                $alerte->update(['lu_at' => now()]);
                $count++;
            });

        return $count;
    }

    public static function alerteVisiblePourPeriode(Alerte $alerte, int $mois, int $annee): bool
    {
        if (in_array($alerte->type, self::TYPES_DETTE, true)) {
            return true;
        }

        $meta = is_array($alerte->meta) ? $alerte->meta : [];

        return ($meta['mois'] ?? null) == $mois && ($meta['annee'] ?? null) == $annee;
    }

    public static function compterNonLues(User $user): int
    {
        $courant = BudgetPeriodService::resolvePeriode($user);

        return Alerte::where('user_id', $user->id)
            ->whereNull('lu_at')
            ->get()
            ->filter(fn (Alerte $a) => self::alerteVisiblePourPeriode($a, $courant['mois'], $courant['annee']))
            ->count();
    }

    /**
     * Analyse le budget du mois courant et génère les alertes nécessaires.
     * Evite les doublons : une seule alerte par type par budget par mois.
     */
    public static function analyserBudget(User $user, Budget $budget): void
    {
        $revenus = $budget->revenus()->get();
        $totalDepensable = Revenu::sumDepensable($revenus);
        $totalDepenses   = (float) $budget->depenses()->sum('montant');
        $salaire         = (float) $budget->salaire_fixe;
        $epargneSalaire  = $salaire * (($user->epargne_salaire_pct ?? 0) / 100);
        $budgetTotal     = $salaire - $epargneSalaire + $totalDepensable;
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
            $suggestions = AlerteConseilsService::topCategoriesLine($budget);
            $estNouveau  = self::creer($user, 'critique', 'danger',
                "Budget dépassé de {$soldeFmt} FCFA ({$ratioFmt}% utilisé sur {$budgetFmt} FCFA)." .
                ($suggestions ? " Pistes : {$suggestions}" : ' Réduisez vos dépenses immédiatement.'),
                ['mois' => $budget->mois, 'annee' => $budget->annee, 'solde' => $solde,
                 'ratio' => $ratio * 100, 'suggestions' => $suggestions],
                $budget
            );
            if ($estNouveau && $user->notifs_email) {
                try {
                    Mail::to($user->email)->send(new AlerteSoldeRouge(
                        $user, $budget, $solde, $ratio, $budgetTotal, $suggestions ?: null
                    ));
                } catch (\Throwable $e) {
                    Log::error('AlerteService mail solde négatif: ' . $e->getMessage());
                }
            }
        } elseif ($ratio >= $seuilCritique) {
            $suggestionsMail = AlerteConseilsService::topCategoriesLine($budget);
            $estNouveau = self::creer($user, 'attention', 'warning',
                "Seuil critique : {$ratioFmt}% du budget consommé. Il ne reste que {$soldeFmt} FCFA sur {$budgetFmt} FCFA. Évitez toute dépense non essentielle.",
                ['mois' => $budget->mois, 'annee' => $budget->annee, 'ratio' => $ratio * 100, 'solde' => $solde,
                 'suggestions' => $suggestionsMail],
                $budget
            );
            if ($estNouveau && $user->notifs_email) {
                try {
                    Mail::to($user->email)->send(new AlerteSoldeRouge(
                        $user, $budget, $solde, $ratio, $budgetTotal, $suggestionsMail ?: null
                    ));
                } catch (\Throwable $e) {
                    Log::error('AlerteService mail seuil critique: ' . $e->getMessage());
                }
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
     * Analyse les échéances de dettes (emprunts & prêts) et génère les alertes.
     * Règle :
     *  - Si échéance dans ≤ 7 jours → alerte 'echeance_proche' (1x par dette)
     *  - Si échéance dépassée → alerte 'echeance_depassee' + statut dette = en_retard
     * Mail envoyé uniquement à la 1ère création de l'alerte (et si notifs_email).
     */
    public static function analyserDettes(User $user): int
    {
        $count = 0;
        $today = now()->startOfDay();

        $dettes = Dette::where('user_id', $user->id)
            ->where('statut', '!=', 'solde')
            ->whereNotNull('date_echeance')
            ->get();

        foreach ($dettes as $dette) {
            $jours = (int) $today->diffInDays($dette->date_echeance, false);

            if ($jours < 0) {
                if ($dette->statut !== 'en_retard') {
                    $dette->update(['statut' => 'en_retard']);
                }
                $libelle = $dette->type === 'emprunt' ? 'Emprunt' : 'Prêt';
                $message = "{$libelle} de {$dette->partie} en retard de " . abs($jours) . " jour(s). Restant : "
                    . self::fmt($dette->montant_restant) . ' FCFA.';
                $nouveau = self::creerAlerteDette($user, 'echeance_depassee', 'danger', $message, $dette);
                if ($nouveau && $user->notifs_email) {
                    try {
                        Mail::to($user->email)->send(new EcheanceDette($user, $dette, 'depassee', $jours));
                    } catch (\Throwable $e) {
                        Log::error('AlerteService mail échéance dépassée: ' . $e->getMessage());
                    }
                }
                if ($nouveau) $count++;
            } elseif ($jours === 1) {
                $libelle = $dette->type === 'emprunt' ? 'Emprunt' : 'Prêt';
                $message = "{$libelle} de {$dette->partie} : échéance demain. Restant : "
                    . self::fmt($dette->montant_restant) . ' FCFA.';
                $nouveau = self::creerAlerteDette($user, 'echeance_j1', 'danger', $message, $dette);
                if ($nouveau && $user->notifs_email) {
                    try {
                        Mail::to($user->email)->send(new EcheanceDette($user, $dette, 'proche', $jours));
                    } catch (\Throwable $e) {
                        Log::error('AlerteService mail échéance J-1: ' . $e->getMessage());
                    }
                }
                if ($nouveau) $count++;
            } elseif ($jours === 7) {
                $libelle = $dette->type === 'emprunt' ? 'Emprunt' : 'Prêt';
                $message = "{$libelle} de {$dette->partie} : échéance dans 7 jours. Restant : "
                    . self::fmt($dette->montant_restant) . ' FCFA.';
                $nouveau = self::creerAlerteDette($user, 'echeance_proche', 'warning', $message, $dette);
                if ($nouveau && $user->notifs_email) {
                    try {
                        Mail::to($user->email)->send(new EcheanceDette($user, $dette, 'proche', $jours));
                    } catch (\Throwable $e) {
                        Log::error('AlerteService mail échéance J-7: ' . $e->getMessage());
                    }
                }
                if ($nouveau) $count++;
            }
        }

        return $count;
    }

    /**
     * Crée une alerte d'échéance en évitant les doublons par dette_id (compat MySQL sans JSON_CONTAINS).
     */
    private static function creerAlerteDette(User $user, string $type, string $gravite, string $message, Dette $dette): bool
    {
        $exists = Alerte::where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('lu_at')
            ->get(['id', 'meta'])
            ->contains(function ($a) use ($dette) {
                $m = is_array($a->meta) ? $a->meta : (json_decode($a->meta ?? '[]', true) ?: []);
                return ($m['dette_id'] ?? null) == $dette->id;
            });

        if (!$exists) {
            Alerte::create([
                'user_id' => $user->id,
                'type'    => $type,
                'gravite' => $gravite,
                'message' => $message,
                'meta'    => ['dette_id' => $dette->id, 'partie' => $dette->partie, 'restant' => (float) $dette->montant_restant],
            ]);
            return true;
        }
        return false;
    }

    /**
     * Notification quand un remboursement partiel est enregistré.
     */
    public static function remboursementPartiel(User $user, Dette $dette, float $montant): void
    {
        $libelle = $dette->type === 'emprunt' ? 'emprunt' : 'prêt';
        $verbe   = $dette->type === 'emprunt' ? 'remboursé' : 'reçu';
        $message = "Vous avez {$verbe} " . self::fmt($montant) . " FCFA sur l'{$libelle} de {$dette->partie}. Restant : "
            . self::fmt($dette->montant_restant) . ' FCFA (' . $dette->pct_rembourse . '%).';

        Alerte::create([
            'user_id' => $user->id,
            'type'    => 'remboursement_partiel',
            'gravite' => 'info',
            'message' => $message,
            'meta'    => ['dette_id' => $dette->id, 'montant' => $montant, 'restant' => (float) $dette->montant_restant],
        ]);

        // Envoi email
        if ($user->notifs_email) {
            try {
                Mail::to($user->email)->send(new RemboursementDette($user, $dette, $montant, false));
            } catch (\Throwable $e) {
                Log::error('AlerteService mail remboursement partiel: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notification quand une dette est entièrement soldée.
     * Marque aussi les alertes 'echeance_proche' et 'echeance_depassee' de cette dette comme lues.
     */
    public static function detteSoldee(User $user, Dette $dette): void
    {
        $libelle = $dette->type === 'emprunt' ? 'emprunt' : 'prêt';
        $message = "🎉 Bravo ! L'{$libelle} de {$dette->partie} (" . self::fmt($dette->montant_initial) . " FCFA) est entièrement soldé.";

        Alerte::create([
            'user_id' => $user->id,
            'type'    => 'dette_soldee',
            'gravite' => 'info',
            'message' => $message,
            'meta'    => ['dette_id' => $dette->id, 'montant_total' => (float) $dette->montant_initial],
        ]);

        // Envoi email
        if ($user->notifs_email) {
            try {
                Mail::to($user->email)->send(new RemboursementDette($user, $dette, (float) $dette->montant_initial, true));
            } catch (\Throwable $e) {
                Log::error('AlerteService mail dette soldée: ' . $e->getMessage());
            }
        }

        // Marquer comme lues les alertes d'échéance liées à cette dette
        Alerte::where('user_id', $user->id)
            ->whereIn('type', ['echeance_proche', 'echeance_depassee'])
            ->whereNull('lu_at')
            ->get(['id', 'meta'])
            ->each(function ($a) use ($dette) {
                $m = is_array($a->meta) ? $a->meta : (json_decode($a->meta ?? '[]', true) ?: []);
                if (($m['dette_id'] ?? null) == $dette->id) {
                    $a->update(['lu_at' => now()]);
                }
            });
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
        // Filtrage PHP au lieu de JSON_CONTAINS (compat MySQL sans privilège JSON)
        $exists = Alerte::where('user_id', $user->id)
            ->where('type', $type)
            ->whereNull('lu_at')
            ->get(['id', 'meta'])
            ->contains(function ($a) use ($budget) {
                $m = is_array($a->meta) ? $a->meta : (json_decode($a->meta ?? '[]', true) ?: []);
                return ($m['mois'] ?? null) == $budget->mois && ($m['annee'] ?? null) == $budget->annee;
            });

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
