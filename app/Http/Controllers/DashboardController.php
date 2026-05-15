<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Depense;
use App\Models\Epargne;
use App\Models\ObjectifEpargne;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mois  = (int) $request->get('mois',  now()->month);
        $annee = (int) $request->get('annee', now()->year);

        // Bornes : pas de futur au-delà du mois courant
        $today = now();
        if ($annee > $today->year || ($annee == $today->year && $mois > $today->month)) {
            $mois  = $today->month;
            $annee = $today->year;
        }

        /** @var User $user */
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

        // Épargne du mois = part salaire fixe + solde réserve bonus
        $epargneSalaire   = (float) ($budget->salaire_fixe * ($user->epargne_salaire_pct ?? 0) / 100);
        $epargneNaturelle = round($epargneSalaire + $totalReserve); // réserve bonus (70%) + part salaire

        // Objectif actif : le premier non atteint couvrant le mois courant
        $dateMois = Carbon::createFromDate($annee, $mois, 1);
        $objectifActif = ObjectifEpargne::where('user_id', $user->id)
            ->where('atteint', false)
            ->where('date_debut', '<=', $dateMois->copy()->endOfMonth())
            ->where(fn($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', $dateMois->copy()->startOfMonth()))
            ->orderBy('date_fin')
            ->first();

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

        $epargne_salaire_pct = (int) ($user->epargne_salaire_pct ?? 0);

        return view('dashboard', compact(
            'budget', 'revenus', 'user',
            'totalDepensable', 'totalReserve', 'totalDepenses', 'soldeDisponible',
            'epargneSalaire', 'epargneNaturelle', 'objectifActif', 'epargne_salaire_pct',
            'revenuTotal', 'sante', 'joursLabels', 'joursData',
            'parCategorie', 'dernieresDepenses', 'alertes',
            'mois', 'annee'
        ));
    }
}
