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

        // ------ Budgets : 3 mois de données ------
        $scenarios = [
            // mois courant : budget sain (60% dépensé)
            [
                'mois' => now()->month, 'annee' => now()->year,
                'salaire_fixe' => 450000, 'epargne_objectif' => 50000,
                'revenus' => [
                    ['type' => 'bonus', 'montant_brut' => 150000, 'description' => 'Prime de performance', 'date' => now()->startOfMonth()->addDays(4)],
                ],
                'depenses' => [
                    ['cat' => 'Alimentation',   'montant' => 35000,  'note' => 'Supermarché Casino',   'jours_avant' => 1],
                    ['cat' => 'Alimentation',   'montant' => 12500,  'note' => 'Marché central',        'jours_avant' => 3],
                    ['cat' => 'Transport',      'montant' => 25000,  'note' => 'Carburant',             'jours_avant' => 2],
                    ['cat' => 'Transport',      'montant' => 8000,   'note' => 'Taxi',                  'jours_avant' => 5],
                    ['cat' => 'Santé',          'montant' => 18000,  'note' => 'Pharmacie',             'jours_avant' => 4],
                    ['cat' => 'Factures',       'montant' => 45000,  'note' => 'Loyer',                 'jours_avant' => 6],
                    ['cat' => 'Loisirs',        'montant' => 22000,  'note' => 'Sortie restaurant',     'jours_avant' => 7],
                    ['cat' => 'Alimentation',   'montant' => 9800,   'note' => 'Boulangerie',           'jours_avant' => 0, 'imprevue' => false],
                    ['cat' => 'Éducation',      'montant' => 30000,  'note' => 'Frais scolaires',       'jours_avant' => 8],
                    ['cat' => 'Habillement',    'montant' => 27500,  'note' => 'Vêtements enfants',     'jours_avant' => 9, 'imprevue' => true],
                ],
                'epargne' => ['objectif' => 50000, 'reel' => 35000],
            ],
            // mois précédent : budget en alerte (78% dépensé)
            [
                'mois' => now()->subMonth()->month, 'annee' => now()->subMonth()->year,
                'salaire_fixe' => 420000, 'epargne_objectif' => 50000,
                'revenus' => [
                    ['type' => 'extra', 'montant_brut' => 80000, 'description' => 'Freelance site web', 'date' => now()->subMonth()->startOfMonth()->addDays(9)],
                ],
                'depenses' => [
                    ['cat' => 'Alimentation',   'montant' => 48000,  'note' => 'Courses mensuelles',    'jours_avant' => 0],
                    ['cat' => 'Transport',      'montant' => 35000,  'note' => 'Carburant + entretien',  'jours_avant' => 0],
                    ['cat' => 'Factures',       'montant' => 45000,  'note' => 'Loyer',                 'jours_avant' => 0],
                    ['cat' => 'Santé',          'montant' => 35000,  'note' => 'Consultation + méd.',   'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Loisirs',        'montant' => 18000,  'note' => 'Cinéma + sorties',      'jours_avant' => 0],
                    ['cat' => 'Habillement',    'montant' => 42000,  'note' => 'Shopping',              'jours_avant' => 0],
                    ['cat' => 'Éducation',      'montant' => 30000,  'note' => 'Cours particuliers',    'jours_avant' => 0],
                    ['cat' => 'Alimentation',   'montant' => 15000,  'note' => 'Restaurant d\'affaires','jours_avant' => 0],
                    ['cat' => 'Maison',         'montant' => 25000,  'note' => 'Réparation fuite eau',  'jours_avant' => 0, 'imprevue' => true],
                ],
                'epargne' => ['objectif' => 50000, 'reel' => 15000],
            ],
            // il y a 2 mois : budget critique (dépassé)
            [
                'mois' => now()->subMonths(2)->month, 'annee' => now()->subMonths(2)->year,
                'salaire_fixe' => 400000, 'epargne_objectif' => 40000,
                'revenus' => [],
                'depenses' => [
                    ['cat' => 'Alimentation',   'montant' => 55000,  'note' => 'Courses + restaurant',  'jours_avant' => 0],
                    ['cat' => 'Transport',       'montant' => 40000,  'note' => 'Réparation voiture',    'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Factures',        'montant' => 45000,  'note' => 'Loyer',                'jours_avant' => 0],
                    ['cat' => 'Santé',           'montant' => 60000,  'note' => 'Hospitalisation',      'jours_avant' => 0, 'imprevue' => true],
                    ['cat' => 'Loisirs',         'montant' => 35000,  'note' => 'Voyage week-end',      'jours_avant' => 0],
                    ['cat' => 'Habillement',     'montant' => 55000,  'note' => 'Shopping',             'jours_avant' => 0],
                    ['cat' => 'Éducation',       'montant' => 30000,  'note' => 'Frais scolaires',      'jours_avant' => 0],
                    ['cat' => 'Maison',          'montant' => 45000,  'note' => 'Travaux',              'jours_avant' => 0],
                    ['cat' => 'Alimentation',    'montant' => 20000,  'note' => 'Fête anniversaire',    'jours_avant' => 0, 'imprevue' => true],
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
                // Appliquer le quota 30% manuellement
                $quota = (int) round($r['montant_brut'] * 0.30);
                $dispo = $r['montant_brut'] - $quota;
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
