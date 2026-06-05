<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Dette;

class AlerteConseilsService
{
    /**
     * Top 3 catégories de dépenses (texte court pour message / e-mail).
     */
    public static function topCategoriesLine(Budget $budget): string
    {
        $top = $budget->depenses()
            ->with('categorie')
            ->get()
            ->groupBy('categorie_id')
            ->map(fn ($items) => [
                'nom'   => $items->first()->categorie?->nom ?? 'Autres',
                'total' => (float) $items->sum('montant'),
                'nb'    => $items->count(),
            ])
            ->sortByDesc('total')
            ->take(3)
            ->values();

        if ($top->isEmpty()) {
            return '';
        }

        $lignes = $top->map(fn ($cat) =>
            "{$cat['nom']} (" . number_format((int) $cat['total'], 0, ',', "\u{00A0}") . ' FCFA, ' . $cat['nb'] . ' op.)'
        );

        return 'Réduire en priorité : ' . $lignes->implode(' · ') . '.';
    }

    /**
     * Conseils actionnables selon le type d'alerte (in-app et e-mails).
     *
     * @return list<string>
     */
    public static function pourType(string $type, array $meta = [], ?Budget $budget = null): array
    {
        return match ($type) {
            'critique' => self::conseilsCritique($meta, $budget),
            'attention' => [
                'Notez chaque dépense avant de la faire, même les petites.',
                'Regroupez vos courses en une seule sortie pour limiter les achats impulsifs.',
                'Évitez les transports et sorties non planifiés jusqu\'à la fin du mois.',
            ],
            'plafond_80' => self::conseilsPlafond($meta, false),
            'plafond_depasse' => self::conseilsPlafond($meta, true),
            'epargne_deficit' => self::conseilsEpargne($meta),
            'echeance_proche' => self::conseilsEcheance($meta, 7),
            'echeance_j1' => self::conseilsEcheance($meta, 1),
            'echeance_depassee' => self::conseilsEcheance($meta, 0),
            'remboursement_partiel' => self::conseilsRemboursement($meta),
            'budget_sain' => [
                'Continuez à enregistrer vos dépenses pour garder une vision précise.',
                'Envisagez de verser le surplus vers votre épargne ou un objectif.',
            ],
            'quota_applique' => [
                'La part réservée de ce revenu est protégée : ne la débloquez qu\'en cas de besoin réel.',
                'Utilisez uniquement le montant « dépensable » affiché pour vos achats du mois.',
            ],
            default => [],
        };
    }

    /** @return list<string> */
    private static function conseilsCritique(array $meta, ?Budget $budget): array
    {
        $lines = [
            'Gelez les dépenses non essentielles jusqu\'à la fin du mois.',
            'Envisagez de débloquer une partie de votre réserve bonus si elle est disponible.',
            'Réduisez sorties, loisirs et achats en ligne.',
        ];

        $fromMeta = $meta['suggestions'] ?? null;
        if (is_string($fromMeta) && $fromMeta !== '') {
            $lines[] = $fromMeta;
        } elseif ($budget) {
            $line = self::topCategoriesLine($budget);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /** @return list<string> */
    private static function conseilsPlafond(array $meta, bool $depasse): array
    {
        $cat = $meta['categorie'] ?? 'cette catégorie';
        $restant = isset($meta['restant']) ? (int) $meta['restant'] : null;
        $depasseMont = isset($meta['depasse']) ? (int) $meta['depasse'] : 0;

        if ($depasse) {
            return [
                "N'ajoutez plus de dépenses dans « {$cat} » ce mois-ci.",
                'Ajustez le plafond le mois prochain si ce montant est récurrent (Paramètres → Récurrences).',
                $depasseMont > 0
                    ? 'Compensez en réduisant une autre catégorie d\'environ ' . number_format($depasseMont, 0, ',', "\u{00A0}") . ' FCFA.'
                    : 'Compensez en réduisant une autre catégorie de dépenses.',
            ];
        }

        $resteTxt = $restant !== null
            ? 'Il reste ' . number_format($restant, 0, ',', "\u{00A0}") . ' FCFA sur ce plafond.'
            : 'Vous approchez de la limite de ce plafond.';

        return [
            "Catégorie « {$cat} » : {$resteTxt}",
            'Priorisez les achats indispensables dans cette catégorie.',
            'Reportez les dépenses optionnelles à un autre mois ou une autre catégorie.',
        ];
    }

    /** @return list<string> */
    private static function conseilsEpargne(array $meta): array
    {
        $deficit = (int) ($meta['deficit'] ?? 0);
        $objectif = (int) ($meta['objectif'] ?? 0);

        return [
            $deficit > 0
                ? 'Réduisez loisirs et sorties d\'environ ' . number_format((int) round($deficit / 2), 0, ',', "\u{00A0}") . ' FCFA.'
                : 'Réduisez les dépenses de loisirs pour libérer de l\'épargne.',
            'Versez le déficit manuellement dans votre objectif d\'épargne si possible.',
            $objectif > 0
                ? 'Le mois prochain, un objectif à ' . number_format((int) round($objectif * 0.8), 0, ',', "\u{00A0}") . ' FCFA peut être plus réaliste.'
                : 'Revoyez votre objectif d\'épargne dans les paramètres.',
        ];
    }

    /** @return list<string> */
    private static function conseilsEcheance(array $meta, int $joursRestants): array
    {
        $partie = $meta['partie'] ?? 'la contrepartie';
        $restant = isset($meta['restant']) ? (int) $meta['restant'] : null;
        $detteId = $meta['dette_id'] ?? null;
        $type = null;

        if ($detteId) {
            $dette = Dette::find($detteId);
            $type = $dette?->type;
        }

        $restantTxt = $restant !== null
            ? number_format($restant, 0, ',', "\u{00A0}") . ' FCFA restants'
            : 'le solde restant';

        if ($joursRestants <= 0) {
            if ($type === 'pret') {
                return [
                    "Relancez {$partie} pour récupérer les {$restantTxt}.",
                    'Enregistrez tout remboursement reçu dans Emprunts & Prêts.',
                    'Mettez à jour la date d\'échéance si un accord de report a été trouvé.',
                ];
            }

            return [
                "Planifiez un remboursement pour couvrir les {$restantTxt}.",
                'Enregistrez le paiement dans Emprunts & Prêts dès qu\'il est effectué.',
                'Si besoin, renégociez l\'échéance et mettez à jour la date dans l\'application.',
            ];
        }

        if ($joursRestants === 1) {
            return [
                'Échéance demain : préparez le montant ou confirmez le virement.',
                $type === 'pret'
                    ? "Contactez {$partie} pour confirmer le remboursement prévu."
                    : "Vérifiez votre trésorerie pour honorer {$restantTxt}.",
                'Après paiement, saisissez le remboursement dans EcoPoche.',
            ];
        }

        return [
            'Échéance dans une semaine : anticipez le montant à prévoir.',
            $type === 'pret'
                ? "Rappelez à {$partie} la date limite convenue."
                : 'Bloquez cette somme sur votre budget du mois.',
            'Consultez la fiche dette pour l\'historique des remboursements.',
        ];
    }

    /** @return list<string> */
    private static function conseilsRemboursement(array $meta): array
    {
        $restant = isset($meta['restant']) ? (int) $meta['restant'] : null;

        $lines = [
            'Bon rythme : continuez les remboursements réguliers.',
        ];

        if ($restant !== null && $restant > 0) {
            $lines[] = 'Reste à solder : ' . number_format($restant, 0, ',', "\u{00A0}") . ' FCFA.';
        } else {
            $lines[] = 'Vous approchez du solde complet : vérifiez le statut de la dette.';
        }

        $lines[] = 'Les échéances proches génèrent des rappels automatiques.';

        return $lines;
    }
}
