<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\Depense;
use App\Models\Epargne;
use App\Models\QuotaLog;
use App\Models\Revenu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@ecopoche.com')->firstOrFail();

        // Épargne sur salaire : 8% du salaire fixe mis en réserve automatiquement
        $epargnesSalairePct = 8;
        $quotaTaux          = 30; // 30% des bonus/extras → dépensable ce mois

        // Mise à jour ciblée : ne toucher qu'au paramètre épargne salaire
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['epargne_salaire_pct' => $epargnesSalairePct]);

        $categories = Categorie::where('user_id', $user->id)->get()->keyBy('nom');

        /*
         * Logique épargne totale :
         *   epargne_salaire = salaire_fixe * epargne_salaire_pct / 100
         *   epargne_bonus   = sum(montant_dispo) des bonus/extras (70% bloqué en réserve)
         *   epargne_reel    = epargne_salaire + epargne_bonus
         *
         * Logique quota bonus/extra :
         *   montant_quota = quotaTaux% du brut  → dépensable ce mois
         *   montant_dispo = reste              → réserve (= epargne_bonus)
         */

        // ─────────────────────────────────────────────────────────────────────
        // 12 mois de 2025 : Jan → Déc
        // Salaire de base : 450 000 FCFA | Epargne salaire : 8% = 36 000/mois
        // ─────────────────────────────────────────────────────────────────────
        // Noms exacts des catégories créées par CategorieSeeder
        // 'Essence / Transport' | 'Nourriture' | 'Extras / Loisirs' | 'Imprévus' | 'Famille' | 'Autres'
        $T = 'Essence / Transport';
        $N = 'Nourriture';
        $L = 'Extras / Loisirs';
        $I = 'Imprévus';
        $F = 'Famille';
        $A = 'Autres';

        $scenarios = [

            // Jan 2025 — SAIN : bonus performance + bonne maîtrise
            [
                'mois' => 1, 'annee' => 2025,
                'salaire_fixe' => 450000, 'epargne_objectif' => 60000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 120000, 'description' => 'Prime fin décembre', 'jour' => 5],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 42000, 'note' => 'Courses janvier'],
                    ['cat' => $T, 'montant' => 22000, 'note' => 'Carburant'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $I, 'montant' => 12000, 'note' => 'Pharmacie'],
                    ['cat' => $L, 'montant' => 15000, 'note' => 'Sortie'],
                    ['cat' => $F, 'montant' => 30000, 'note' => 'Frais scolaires T1'],
                ],
            ],

            // Fév 2025 — SAIN : extra freelance
            [
                'mois' => 2, 'annee' => 2025,
                'salaire_fixe' => 450000, 'epargne_objectif' => 60000,
                'revenus' => [
                    ['type' => 'extra', 'montant_brut' => 80000, 'description' => 'Mission freelance', 'jour' => 10],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 48000, 'note' => 'Courses + marché'],
                    ['cat' => $T, 'montant' => 18000, 'note' => 'Carburant'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 20000, 'note' => 'Saint-Valentin'],
                    ['cat' => $F, 'montant' => 25000, 'note' => 'Vêtements famille'],
                ],
            ],

            // Mar 2025 — ATTENTION : dépenses élevées, pas de bonus
            [
                'mois' => 3, 'annee' => 2025,
                'salaire_fixe' => 450000, 'epargne_objectif' => 60000,
                'revenus' => [],
                'depenses' => [
                    ['cat' => $N, 'montant' => 55000, 'note' => 'Courses mars'],
                    ['cat' => $T, 'montant' => 35000, 'note' => 'Révision voiture', 'imprevue' => true],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $I, 'montant' => 28000, 'note' => 'Consultation spécialiste', 'imprevue' => true],
                    ['cat' => $F, 'montant' => 30000, 'note' => 'Frais scolaires T2'],
                    ['cat' => $A, 'montant' => 22000, 'note' => 'Ménage + entretien'],
                    ['cat' => $L, 'montant' => 18000, 'note' => 'Sorties'],
                    ['cat' => $F, 'montant' => 30000, 'note' => 'Garde-robe famille'],
                ],
            ],

            // Avr 2025 — SAIN : bonus de trimestre
            [
                'mois' => 4, 'annee' => 2025,
                'salaire_fixe' => 450000, 'epargne_objectif' => 60000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 200000, 'description' => 'Prime trimestrielle Q1', 'jour' => 7],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 50000, 'note' => 'Courses avril'],
                    ['cat' => $T, 'montant' => 20000, 'note' => 'Carburant'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 35000, 'note' => 'Vacances Pâques'],
                    ['cat' => $I, 'montant' => 15000, 'note' => 'Bilan médical'],
                ],
            ],

            // Mai 2025 — SAIN : extra consulting
            [
                'mois' => 5, 'annee' => 2025,
                'salaire_fixe' => 450000, 'epargne_objectif' => 60000,
                'revenus' => [
                    ['type' => 'extra', 'montant_brut' => 60000, 'description' => 'Consulting weekend', 'jour' => 12],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 46000, 'note' => 'Courses mai'],
                    ['cat' => $T, 'montant' => 22000, 'note' => 'Carburant + taxi'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $F, 'montant' => 30000, 'note' => 'Frais scolaires T3'],
                    ['cat' => $L, 'montant' => 18000, 'note' => 'Fête du travail'],
                ],
            ],

            // Jun 2025 — CRITIQUE : hospitalisation imprévue
            [
                'mois' => 6, 'annee' => 2025,
                'salaire_fixe' => 450000, 'epargne_objectif' => 60000,
                'revenus' => [],
                'depenses' => [
                    ['cat' => $N,  'montant' => 48000,  'note' => 'Courses juin'],
                    ['cat' => $T,  'montant' => 25000,  'note' => 'Carburant'],
                    ['cat' => $A,  'montant' => 45000,  'note' => 'Loyer'],
                    ['cat' => $I,  'montant' => 120000, 'note' => 'Hospitalisation', 'imprevue' => true],
                    ['cat' => $I,  'montant' => 35000,  'note' => 'Réparation fuite', 'imprevue' => true],
                    ['cat' => $F,  'montant' => 22000,  'note' => 'Repas famille'],
                    ['cat' => $L,  'montant' => 15000,  'note' => 'Sorties'],
                ],
            ],

            // Juil 2025 — SAIN : bonus mi-année
            [
                'mois' => 7, 'annee' => 2025,
                'salaire_fixe' => 460000, 'epargne_objectif' => 65000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 180000, 'description' => 'Prime mi-année',  'jour' => 3],
                    ['type' => 'extra', 'montant_brut' => 50000,  'description' => 'Vente matériel',  'jour' => 15],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 52000, 'note' => 'Courses juillet'],
                    ['cat' => $T, 'montant' => 28000, 'note' => 'Carburant vacances'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 55000, 'note' => 'Vacances juillet'],
                    ['cat' => $F, 'montant' => 20000, 'note' => 'Maillots + accessoires famille'],
                ],
            ],

            // Août 2025 — ATTENTION : vacances coûteuses
            [
                'mois' => 8, 'annee' => 2025,
                'salaire_fixe' => 460000, 'epargne_objectif' => 65000,
                'revenus' => [
                    ['type' => 'extra', 'montant_brut' => 40000, 'description' => 'Remboursement frais', 'jour' => 20],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 45000, 'note' => 'Courses août'],
                    ['cat' => $T, 'montant' => 55000, 'note' => 'Vol + train vacances'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 75000, 'note' => 'Hôtel + activités vacances'],
                    ['cat' => $N, 'montant' => 30000, 'note' => 'Restaurants vacances'],
                    ['cat' => $F, 'montant' => 25000, 'note' => 'Achats voyage famille'],
                ],
            ],

            // Sep 2025 — SAIN : rentrée maîtrisée
            [
                'mois' => 9, 'annee' => 2025,
                'salaire_fixe' => 460000, 'epargne_objectif' => 65000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 90000, 'description' => 'Bonus rentrée client', 'jour' => 8],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 50000, 'note' => 'Courses septembre'],
                    ['cat' => $T, 'montant' => 22000, 'note' => 'Carburant rentrée'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $F, 'montant' => 45000, 'note' => 'Fournitures scolaires rentrée'],
                    ['cat' => $I, 'montant' => 18000, 'note' => 'Visite médicale rentrée'],
                    ['cat' => $F, 'montant' => 28000, 'note' => 'Vêtements rentrée enfants'],
                ],
            ],

            // Oct 2025 — SAIN : mois calme
            [
                'mois' => 10, 'annee' => 2025,
                'salaire_fixe' => 460000, 'epargne_objectif' => 65000,
                'revenus' => [],
                'depenses' => [
                    ['cat' => $N, 'montant' => 48000, 'note' => 'Courses octobre'],
                    ['cat' => $T, 'montant' => 20000, 'note' => 'Carburant'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 22000, 'note' => 'Halloween + sorties'],
                    ['cat' => $A, 'montant' => 18000, 'note' => 'Entretien maison'],
                ],
            ],

            // Nov 2025 — ATTENTION : dépenses fin d'année anticipées
            [
                'mois' => 11, 'annee' => 2025,
                'salaire_fixe' => 460000, 'epargne_objectif' => 65000,
                'revenus' => [
                    ['type' => 'extra', 'montant_brut' => 70000, 'description' => 'Revenu locatif exceptionnel', 'jour' => 6],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 52000, 'note' => 'Courses novembre'],
                    ['cat' => $T, 'montant' => 24000, 'note' => 'Carburant'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 65000, 'note' => 'Black Friday shopping'],
                    ['cat' => $L, 'montant' => 30000, 'note' => 'Sorties novembre'],
                    ['cat' => $F, 'montant' => 30000, 'note' => 'Frais scolaires T2'],
                    ['cat' => $A, 'montant' => 25000, 'note' => 'Déco Noël'],
                ],
            ],

            // Déc 2025 — SAIN : prime fin d'année + gestion sage
            [
                'mois' => 12, 'annee' => 2025,
                'salaire_fixe' => 460000, 'epargne_objectif' => 65000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 250000, 'description' => "Prime annuelle de fin d'année", 'jour' => 5],
                ],
                'depenses' => [
                    ['cat' => $N, 'montant' => 65000, 'note' => "Fêtes de fin d'année"],
                    ['cat' => $T, 'montant' => 25000, 'note' => 'Carburant fêtes'],
                    ['cat' => $A, 'montant' => 45000, 'note' => 'Loyer'],
                    ['cat' => $L, 'montant' => 50000, 'note' => 'Réveillon + sorties'],
                    ['cat' => $F, 'montant' => 40000, 'note' => 'Cadeaux famille'],
                    ['cat' => $A, 'montant' => 20000, 'note' => 'Déco maison'],
                ],
            ],
        ];

        foreach ($scenarios as $sc) {
            $salaire = $sc['salaire_fixe'];

            // Épargne sur salaire fixe
            $epargneSalaire = (int) round($salaire * $epargnesSalairePct / 100);

            // Calcul épargne bonus : sum des montant_dispo (partie réserve des bonus/extras)
            $epargneBonus = 0;
            foreach ($sc['revenus'] as $r) {
                $epargneBonus += (int) round($r['montant_brut'] * (1 - $quotaTaux / 100));
            }

            // Épargne réelle = épargne salaire + épargne bonus
            $epargneReel = $epargneSalaire + $epargneBonus;

            $budget = Budget::updateOrCreate(
                ['user_id' => $user->id, 'mois' => $sc['mois'], 'annee' => $sc['annee']],
                ['salaire_fixe' => $salaire, 'epargne_objectif' => $sc['epargne_objectif']]
            );

            // Nettoyage données précédentes
            $budget->revenus()->delete();
            $budget->depenses()->delete();
            Epargne::where('budget_id', $budget->id)->delete();

            // Revenus variables (bonus / extras)
            foreach ($sc['revenus'] as $r) {
                $quota = (int) round($r['montant_brut'] * $quotaTaux / 100);  // dépensable ce mois
                $dispo = $r['montant_brut'] - $quota;                          // réserve (épargne bonus)

                $revenu = $budget->revenus()->create([
                    'type'           => $r['type'],
                    'montant_brut'   => $r['montant_brut'],
                    'montant_quota'  => $quota,
                    'montant_dispo'  => $dispo,
                    'quota_applique' => true,
                    'description'    => $r['description'],
                    'date'           => Carbon::createFromDate($sc['annee'], $sc['mois'], $r['jour'])->format('Y-m-d'),
                ]);

                QuotaLog::create([
                    'revenu_id'               => $revenu->id,
                    'montant_brut'            => $r['montant_brut'],
                    'montant_quota'           => $quota,
                    'montant_dispo'           => $dispo,
                    'taux'                    => $quotaTaux,
                    'debloquer'               => 0,
                    'justification_deblocage' => null,
                ]);
            }

            // Dépenses
            foreach ($sc['depenses'] as $d) {
                $cat = $categories->get($d['cat']);
                if (!$cat) continue;

                $budget->depenses()->create([
                    'categorie_id' => $cat->id,
                    'montant'      => $d['montant'],
                    'date'         => Carbon::createFromDate($sc['annee'], $sc['mois'], rand(5, 25))->format('Y-m-d'),
                    'note'         => $d['note'],
                    'imprevue'     => $d['imprevue'] ?? false,
                ]);
            }

            // Épargne enregistrée = salaire_pct + bonus_réserve
            Epargne::create([
                'budget_id' => $budget->id,
                'objectif'  => $sc['epargne_objectif'],
                'reel'      => $epargneReel,
                'deficit'   => max(0, $sc['epargne_objectif'] - $epargneReel),
                'analyse'   => null,
            ]);

            $this->command->line(
                "  {$sc['annee']}-" . str_pad($sc['mois'], 2, '0', STR_PAD_LEFT) .
                " | Salaire {$salaire} | Épargne salaire {$epargneSalaire} | Épargne bonus {$epargneBonus} | Épargne reel {$epargneReel}"
            );
        }

        $this->command->info('✅ 12 mois 2025 seedés — épargne = salaire(' . $epargnesSalairePct . '%) + bonus(réserve ' . (100 - $quotaTaux) . '%)');
    }
}
