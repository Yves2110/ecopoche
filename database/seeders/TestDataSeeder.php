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

        $categories = Categorie::where('user_id', $user->id)->get()->keyBy('nom');

        /*
         * Logique correcte :
         *   montant_quota = 30% du bonus  → DÉPENSABLE ce mois
         *   montant_dispo = 70% du bonus  → RÉSERVE bloquée
         *   Budget utilisable = salaire_fixe + montant_quota (30%)
         *
         * Scénario 1 : salaire 450k + bonus 150k → dépensable = 450k + 45k = 495k
         *   Dépenses ~290k → 59%  → SAIN
         * Scénario 2 : salaire 420k + extra 80k  → dépensable = 420k + 24k = 444k
         *   Dépenses ~340k → 77%  → ATTENTION (>70% mais solde positif)
         * Scénario 3 : salaire 400k, pas de bonus → dépensable = 400k
         *   Dépenses ~450k → 112% → CRITIQUE (solde négatif)
         */
        $scenarios = [
            // ── Mois courant : SAIN (59% du budget utilisable dépensé) ──────────
            // Budget utilisable = 450 000 + 45 000 (30% de 150k) = 495 000 FCFA
            // Dépenses total = 290 800 FCFA → 59%
            [
                'mois' => now()->month, 'annee' => now()->year,
                'salaire_fixe' => 450000, 'epargne_objectif' => 50000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 150000, 'description' => 'Prime de performance',
                     'date' => now()->startOfMonth()->addDays(4)],
                ],
                'depenses' => [
                    ['cat' => 'Alimentation',  'montant' => 35000,  'note' => 'Supermarché Casino',  'jours_avant' => 1],
                    ['cat' => 'Alimentation',  'montant' => 12500,  'note' => 'Marché central',       'jours_avant' => 3],
                    ['cat' => 'Transport',     'montant' => 25000,  'note' => 'Carburant',            'jours_avant' => 2],
                    ['cat' => 'Transport',     'montant' => 8000,   'note' => 'Taxi',                 'jours_avant' => 5],
                    ['cat' => 'Santé',         'montant' => 18000,  'note' => 'Pharmacie',            'jours_avant' => 4],
                    ['cat' => 'Factures',      'montant' => 45000,  'note' => 'Loyer',                'jours_avant' => 6],
                    ['cat' => 'Loisirs',       'montant' => 22000,  'note' => 'Sortie restaurant',    'jours_avant' => 7],
                    ['cat' => 'Alimentation',  'montant' => 9800,   'note' => 'Boulangerie',          'jours_avant' => 0],
                    ['cat' => 'Éducation',     'montant' => 30000,  'note' => 'Frais scolaires',      'jours_avant' => 8],
                    ['cat' => 'Habillement',   'montant' => 27500,  'note' => 'Vêtements enfants',    'jours_avant' => 9, 'imprevue' => true],
                    // total = 232 800 → 47% → SAIN
                ],
                'epargne' => ['objectif' => 50000, 'reel' => 35000],
            ],

            // ── Mois précédent : ATTENTION (77% du budget utilisable dépensé) ──
            // Budget utilisable = 420 000 + 24 000 (30% de 80k) = 444 000 FCFA
            // Dépenses total = 341 000 FCFA → 77%
            [
                'mois' => now()->subMonth()->month, 'annee' => now()->subMonth()->year,
                'salaire_fixe' => 420000, 'epargne_objectif' => 50000,
                'revenus' => [
                    ['type' => 'extra', 'montant_brut' => 80000, 'description' => 'Freelance site web',
                     'date' => now()->subMonth()->startOfMonth()->addDays(9)],
                ],
                'depenses' => [
                    ['cat' => 'Alimentation',  'montant' => 55000,  'note' => 'Courses mensuelles',   'jours_avant' => 0],
                    ['cat' => 'Transport',     'montant' => 38000,  'note' => 'Carburant + entretien', 'jours_avant' => 0],
                    ['cat' => 'Factures',      'montant' => 45000,  'note' => 'Loyer',                'jours_avant' => 0],
                    ['cat' => 'Santé',         'montant' => 32000,  'note' => 'Consultation + méd.',  'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Loisirs',       'montant' => 28000,  'note' => 'Cinéma + sorties',     'jours_avant' => 0],
                    ['cat' => 'Habillement',   'montant' => 45000,  'note' => 'Shopping',             'jours_avant' => 0],
                    ['cat' => 'Éducation',     'montant' => 30000,  'note' => 'Cours particuliers',   'jours_avant' => 0],
                    ['cat' => 'Alimentation',  'montant' => 18000,  'note' => "Restaurant d'affaires", 'jours_avant' => 0],
                    ['cat' => 'Maison',        'montant' => 25000,  'note' => 'Réparation fuite eau', 'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Transport',     'montant' => 25000,  'note' => 'Assurance véhicule',   'jours_avant' => 0],
                    // total = 341 000 → 341/444 = 77% → ATTENTION
                ],
                'epargne' => ['objectif' => 50000, 'reel' => 15000],
            ],

            // ── Il y a 2 mois : CRITIQUE (solde négatif, 112% dépensé) ──────────
            // Budget utilisable = 400 000 FCFA (pas de bonus)
            // Dépenses total = 450 000 FCFA → solde -50 000 → CRITIQUE
            [
                'mois' => now()->subMonths(2)->month, 'annee' => now()->subMonths(2)->year,
                'salaire_fixe' => 400000, 'epargne_objectif' => 40000,
                'revenus' => [],
                'depenses' => [
                    ['cat' => 'Alimentation',  'montant' => 55000,  'note' => 'Courses + restaurant', 'jours_avant' => 0],
                    ['cat' => 'Transport',     'montant' => 40000,  'note' => 'Réparation voiture',   'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Factures',      'montant' => 45000,  'note' => 'Loyer',                'jours_avant' => 0],
                    ['cat' => 'Santé',         'montant' => 65000,  'note' => 'Hospitalisation',      'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Loisirs',       'montant' => 35000,  'note' => 'Voyage week-end',      'jours_avant' => 0],
                    ['cat' => 'Habillement',   'montant' => 55000,  'note' => 'Shopping',             'jours_avant' => 0],
                    ['cat' => 'Éducation',     'montant' => 35000,  'note' => 'Frais scolaires',      'jours_avant' => 0],
                    ['cat' => 'Maison',        'montant' => 50000,  'note' => 'Travaux urgents',      'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Alimentation',  'montant' => 20000,  'note' => 'Fête anniversaire',    'jours_avant' => 0, 'imprevue' => true],
                    // total = 400 000 + 50 000 = 450 000 → solde -50 000 → CRITIQUE
                ],
                'epargne' => ['objectif' => 40000, 'reel' => 0],
            ],
        ];

        foreach ($scenarios as $sc) {
            $budget = Budget::updateOrCreate(
                ['user_id' => $user->id, 'mois' => $sc['mois'], 'annee' => $sc['annee']],
                ['salaire_fixe' => $sc['salaire_fixe'], 'epargne_objectif' => $sc['epargne_objectif']]
            );

            // Supprimer les vieilles données de test si re-seed
            $budget->revenus()->delete();
            $budget->depenses()->delete();
            Epargne::where('budget_id', $budget->id)->delete();

            // Revenus variables
            foreach ($sc['revenus'] as $r) {
                $revenu = $budget->revenus()->create([
                    'type'         => $r['type'],
                    'montant_brut' => $r['montant_brut'],
                    'description'  => $r['description'],
                    'date'         => $r['date'],
                    'quota_applique' => false,
                ]);
                // 30% = dépensable ce mois (montant_quota) | 70% = réserve bloquée (montant_dispo)
                $quota = (int) round($r['montant_brut'] * 0.30); // dépensable
                $dispo = $r['montant_brut'] - $quota;             // réserve 70%
                $revenu->update([
                    'montant_quota'  => $quota,
                    'montant_dispo'  => $dispo,
                    'quota_applique' => true,
                ]);
                QuotaLog::create([
                    'revenu_id'       => $revenu->id,
                    'montant_brut'    => $r['montant_brut'],
                    'montant_quota'   => $quota,
                    'montant_dispo'   => $dispo,
                    'taux'            => 30,
                    'debloquer'               => 0,
                    'justification_deblocage' => null,
                ]);
            }

            // Dépenses
            foreach ($sc['depenses'] as $d) {
                $cat = $categories->get($d['cat']);
                if (!$cat) continue;

                $jours = $d['jours_avant'] ?? 0;
                $date = $sc['mois'] == now()->month && $sc['annee'] == now()->year
                    ? now()->subDays($jours)->format('Y-m-d')
                    : Carbon::createFromDate($sc['annee'], $sc['mois'], rand(5, 25))->format('Y-m-d');

                $budget->depenses()->create([
                    'categorie_id' => $cat->id,
                    'montant'      => $d['montant'],
                    'date'         => $date,
                    'note'         => $d['note'],
                    'imprevue'     => $d['imprevue'] ?? false,
                ]);
            }

            // Épargne
            Epargne::create([
                'budget_id' => $budget->id,
                'objectif'  => $sc['epargne']['objectif'],
                'reel'      => $sc['epargne']['reel'],
                'deficit'   => max(0, $sc['epargne']['objectif'] - $sc['epargne']['reel']),
                'analyse'   => null,
            ]);
        }

        $this->command->info('✅ Données de test créées pour 3 mois (sain / alerte / critique)');
    }
}
