<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesBudgetPeriod;
use App\Models\Budget;
use App\Models\Categorie;
use App\Models\Depense;
use App\Models\Epargne;
use App\Models\ObjectifEpargne;
use App\Models\Revenu;
use App\Models\User;
use App\Services\AlerteService;
use App\Services\BudgetPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ResolvesBudgetPeriod;

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        AlerteService::cloturerAlertesPeriodesExpirees($user);

        ['mois' => $mois, 'annee' => $annee] = $this->resolveMoisAnnee($request, $user);
        $periodeLabel = BudgetPeriodService::label($user, $mois, $annee);
        $estPeriodeCourante = BudgetPeriodService::estPeriodeCourante($user, $mois, $annee);

        $budget = Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );

        $categories = Categorie::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        $revenus = $budget->revenus()->get();
        $totalDepensable = Revenu::sumDepensable($revenus);
        $totalReserve    = Revenu::sumReserve($revenus);
        $totalDepenses   = (float) $budget->depenses()->sum('montant');

        // Épargne du mois = part salaire fixe + solde réserve bonus
        $epargneSalaire   = (float) ($budget->salaire_fixe * ($user->epargne_salaire_pct ?? 0) / 100);

        // Solde disponible = salaire fixe - épargne programmée + bonus dépensable - dépenses
        $soldeDisponible = (float) $budget->salaire_fixe - $epargneSalaire + $totalDepensable - $totalDepenses;
        $epargneNaturelle = round($epargneSalaire + $totalReserve); // réserve bonus (70%) + part salaire

        // Objectif actif : le premier non atteint couvrant le mois courant
        $dateMois = Carbon::createFromDate($annee, $mois, 1);
        $objectifActif = ObjectifEpargne::where('user_id', $user->id)
            ->where('atteint', false)
            ->where('date_debut', '<=', $dateMois->copy()->endOfMonth())
            ->where(fn($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', $dateMois->copy()->startOfMonth()))
            ->orderBy('date_fin')
            ->first();

        $revenuTotal = (float) $budget->salaire_fixe - $epargneSalaire + $totalDepensable;
        $ratioConso = $revenuTotal > 0 ? $totalDepenses / $revenuTotal : 0;
        $sante = match(true) {
            $revenuTotal == 0                              => 'neutre',
            $totalDepenses == 0                            => 'sain',
            $soldeDisponible <= 0                          => 'critique',
            $ratioConso >= 0.9                             => 'critique',
            $ratioConso >= 0.7                             => 'attention',
            default                                        => 'sain',
        };

        [$debutPeriode, $finPeriode] = BudgetPeriodService::bornes($user, $mois, $annee);

        // Flux journalier sur les 14 derniers jours de la période
        $debut14 = $estPeriodeCourante
            ? now()->subDays(13)->startOfDay()
            : $finPeriode->copy()->subDays(13)->startOfDay();
        if ($debut14->lt($debutPeriode)) {
            $debut14 = $debutPeriode->copy();
        }
        $depensesParJour = $budget->depenses()
            ->whereBetween('date', [$debut14, $finPeriode])
            ->get()
            ->groupBy(fn($d) => $d->date->format('Y-m-d'))
            ->map(fn($items) => (int) $items->sum('montant'));

        $joursLabels = [];
        $joursData   = [];
        $refFin = $estPeriodeCourante ? now() : $finPeriode;
        for ($i = 13; $i >= 0; $i--) {
            $date = $refFin->copy()->subDays($i)->format('Y-m-d');
            $joursLabels[] = Carbon::parse($date)->translatedFormat('d M');
            $joursData[]   = $depensesParJour[$date] ?? 0;
        }

        // Répartition par catégorie : chaque catégorie garde son propre total, et Imprévus
        // affiche le total global de toutes les dépenses marquées imprévues.
        $depensesRepart = $budget->depenses()->with('categorie')->get();
        $imprevusCat    = $categories->firstWhere('nom', 'Imprévus');

        $grouped = [];
        foreach ($depensesRepart as $depense) {
            $cat = $depense->categorie;

            // Total par catégorie d'origine
            $key = $cat?->id ?? 'unknown';
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'total'   => 0,
                    'nom'     => $cat?->nom ?? 'Autres',
                    'couleur' => $cat?->couleur ?? '#6B7280',
                ];
            }
            $grouped[$key]['total'] += (float) $depense->montant;
        }

        // Imprévus = total global des dépenses marquées imprévues
        $imprevusExpenses = $depensesRepart->where('imprevue', true);
        if ($imprevusExpenses->isNotEmpty()) {
            $imprevusKey = $imprevusCat?->id ?? 'imprevus';
            $grouped[$imprevusKey] = [
                'total'   => (int) $imprevusExpenses->sum('montant'),
                'nom'     => $imprevusCat?->nom ?? 'Imprévus',
                'couleur' => $imprevusCat?->couleur ?? '#EF4444',
            ];
        }

        $parCategorie = collect($grouped)
            ->map(fn ($g) => ['total' => (int) $g['total'], 'nom' => $g['nom'], 'couleur' => $g['couleur']])
            ->sortByDesc('total')
            ->take(6);

        // 5 dernières dépenses
        $dernieresDepenses = $budget->depenses()
            ->with('categorie')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Alertes actives (période affichée + dettes)
        $alertes = $user->alertes()
            ->whereNull('lu_at')
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn ($a) => AlerteService::alerteVisiblePourPeriode($a, $mois, $annee))
            ->take(5)
            ->values();

        $epargne_salaire_pct = (int) ($user->epargne_salaire_pct ?? 0);

        // Stats Emprunts & Prêts (sommes des restants pour les opérations non soldées)
        $dettesActives = $user->dettes()->where('statut', '!=', 'solde')->with('remboursements')->get();
        $emprunts = $dettesActives->where('type', 'emprunt');
        $prets    = $dettesActives->where('type', 'pret');
        // Insights vs période précédente
        $prec = BudgetPeriodService::periodePrecedente($mois, $annee);
        $budgetPrec = Budget::where('user_id', $user->id)
            ->where('mois', $prec['mois'])
            ->where('annee', $prec['annee'])
            ->first();
        $depensesPrec = $budgetPrec ? (float) $budgetPrec->depenses()->sum('montant') : null;
        $variationDepensesPct = ($depensesPrec && $depensesPrec > 0)
            ? round((($totalDepenses - $depensesPrec) / $depensesPrec) * 100, 1)
            : null;
        $topCategorie = $parCategorie->first();
        $joursEcoules = max(1, BudgetPeriodService::joursEcoules($user, $mois, $annee));
        $joursMois    = max(1, BudgetPeriodService::joursTotal($user, $mois, $annee));
        $projectionDepenses = $joursEcoules > 0
            ? (int) round(($totalDepenses / $joursEcoules) * $joursMois)
            : 0;

        $dettesStats = [
            'emprunts_restant' => (float) $emprunts->sum(fn($d) => $d->montant_restant),
            'emprunts_count'   => $emprunts->count(),
            'emprunts_top'     => $emprunts->sortByDesc(fn($d) => $d->montant_restant)->take(3)->map(fn($d) => [
                'partie' => $d->partie, 'restant' => (int) $d->montant_restant,
            ])->values()->toArray(),
            'prets_restant'    => (float) $prets->sum(fn($d) => $d->montant_restant),
            'prets_count'      => $prets->count(),
            'prets_top'        => $prets->sortByDesc(fn($d) => $d->montant_restant)->take(3)->map(fn($d) => [
                'partie' => $d->partie, 'restant' => (int) $d->montant_restant,
            ])->values()->toArray(),
            'retards'          => $dettesActives->where('statut', 'en_retard')->count(),
        ];

        return view('dashboard', compact(
            'budget', 'revenus', 'user',
            'totalDepensable', 'totalReserve', 'totalDepenses', 'soldeDisponible',
            'epargneSalaire', 'epargneNaturelle', 'objectifActif', 'epargne_salaire_pct',
            'revenuTotal', 'sante', 'joursLabels', 'joursData',
            'parCategorie', 'dernieresDepenses', 'alertes',
            'dettesStats',
            'variationDepensesPct', 'depensesPrec', 'topCategorie', 'projectionDepenses',
            'mois', 'annee', 'periodeLabel', 'estPeriodeCourante',
        ));
    }
}
