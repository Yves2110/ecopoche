<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Depense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $mois  = now()->month;
        $annee = now()->year;
        $user  = Auth::user();

        $budget = Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );

        $revenus = $budget->revenus()->get();
        $totalDepensable = (float) $revenus->where('quota_applique', true)->sum('montant_quota'); // 30% utilisable
        $totalReserve    = (float) $revenus->where('quota_applique', true)->sum('montant_dispo'); // 70% bloqué
        $totalDepenses   = (float) $budget->depenses()->sum('montant');
        $soldeDisponible = (float) $budget->salaire_fixe + $totalDepensable - $totalDepenses;

        $revenuTotal = $budget->salaire_fixe + $totalDepensable;
        $sante = match(true) {
            $revenuTotal == 0                                                              => 'neutre',
            $totalDepenses == 0                                                            => 'sain',
            $soldeDisponible > 0 && $totalDepenses / $revenuTotal < 0.7                   => 'sain',
            $soldeDisponible > 0                                                           => 'attention',
            default                                                                        => 'critique',
        };

        // Flux journalier sur les 14 derniers jours
        $debut14 = now()->subDays(13)->startOfDay();
        $depensesParJour = $budget->depenses()
            ->where('date', '>=', $debut14)
            ->get()
            ->groupBy(fn($d) => $d->date->format('Y-m-d'))
            ->map(fn($items) => (int) $items->sum('montant'));

        $joursLabels = [];
        $joursData   = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $joursLabels[] = Carbon::parse($date)->translatedFormat('d M');
            $joursData[]   = $depensesParJour[$date] ?? 0;
        }

        // Répartition par catégorie
        $parCategorie = $budget->depenses()
            ->with('categorie')
            ->get()
            ->groupBy('categorie_id')
            ->map(fn($items) => [
                'total'    => (int) $items->sum('montant'),
                'nom'      => $items->first()->categorie?->nom ?? 'Autres',
                'couleur'  => $items->first()->categorie?->couleur ?? '#6B7280',
            ])
            ->sortByDesc('total')
            ->take(6);

        // 5 dernières dépenses
        $dernieresDepenses = $budget->depenses()
            ->with('categorie')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Alertes actives
        $alertes = $user->alertes()->whereNull('lu_at')->orderByDesc('created_at')->take(5)->get();

        return view('dashboard', compact(
            'budget', 'revenus',
            'totalDepensable', 'totalReserve', 'totalDepenses', 'soldeDisponible',
            'sante', 'joursLabels', 'joursData',
            'parCategorie', 'dernieresDepenses', 'alertes',
            'mois', 'annee'
        ));
    }
}
