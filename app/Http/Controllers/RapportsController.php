<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Depense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RapportsController extends Controller
{
    private function getUserBudgets(int $moisDebut, int $anneeDebut, int $nbMois = 12): array
    {
        /** @var User $user */
        $user = Auth::user();
        $data = [];

        for ($i = $nbMois - 1; $i >= 0; $i--) {
            $date = Carbon::createFromDate($anneeDebut, $moisDebut, 1)->subMonths($i);
            $budget = Budget::where('user_id', $user->id)
                ->where('mois', $date->month)
                ->where('annee', $date->year)
                ->with(['revenus', 'depenses.categorie', 'epargne'])
                ->first();

            $revenus     = $budget?->revenus ?? collect();
            $depenses    = $budget?->depenses ?? collect();
            $salaire     = (int) ($budget?->salaire_fixe ?? 0);
            $bonusDispo  = (int) $revenus->where('quota_applique', true)->sum('montant_dispo');
            $totalDep    = (int) $depenses->sum('montant');
            $revenuTotal = $salaire + $bonusDispo;
            $solde       = $revenuTotal - $totalDep;
            $epargne     = (int) ($budget?->epargne?->reel ?? 0);

            $data[] = [
                'label'       => $date->translatedFormat('M Y'),
                'mois'        => $date->month,
                'annee'       => $date->year,
                'salaire'     => $salaire,
                'bonus_dispo' => $bonusDispo,
                'revenu'      => $revenuTotal,
                'depenses'    => $totalDep,
                'solde'       => $solde,
                'epargne'     => $epargne,
                'actif'       => $date->month == now()->month && $date->year == now()->year,
                'budget'      => $budget,
            ];
        }

        return $data;
    }

    public function index(Request $request): \Illuminate\View\View
    {
        /** @var User $user */
        $user  = Auth::user();
        $mois  = (int) $request->get('mois',  now()->month);
        $annee = (int) $request->get('annee', now()->year);
        $nbMois = (int) $request->get('periode', 12);
        if (!in_array($nbMois, [6, 12])) $nbMois = 12;

        $historique = $this->getUserBudgets($mois, $annee, $nbMois);

        // Agrégats globaux
        $totalRevenu   = collect($historique)->sum('revenu');
        $totalDepenses = collect($historique)->sum('depenses');
        $totalEpargne  = collect($historique)->sum('epargne');
        $tauxEpargne   = $totalRevenu > 0 ? round($totalEpargne / $totalRevenu * 100) : 0;

        // Top catégories (période sélectionnée)
        $topCategories = collect();
        foreach ($historique as $h) {
            if ($h['budget']) {
                foreach ($h['budget']->depenses as $dep) {
                    $nom = $dep->categorie?->nom ?? 'Autres';
                    $col = $dep->categorie?->couleur ?? '#6B7280';
                    if (!$topCategories->has($nom)) {
                        $topCategories->put($nom, ['nom' => $nom, 'couleur' => $col, 'total' => 0]);
                    }
                    $topCategories[$nom] = array_merge($topCategories[$nom], [
                        'total' => $topCategories[$nom]['total'] + (int) $dep->montant,
                    ]);
                }
            }
        }
        $topCategories = $topCategories->sortByDesc('total')->take(5)->values();

        // Mois courant pour CSV/PDF export
        $budgetActif = Budget::where('user_id', $user->id)
            ->where('mois', $mois)->where('annee', $annee)
            ->with(['revenus', 'depenses.categorie', 'epargne'])
            ->first();

        return view('rapports.index', compact(
            'historique', 'mois', 'annee', 'nbMois',
            'totalRevenu', 'totalDepenses', 'totalEpargne', 'tauxEpargne',
            'topCategories', 'budgetActif'
        ));
    }

    public function exportCsv(Request $request): Response
    {
        /** @var User $user */
        $user  = Auth::user();
        $mois  = (int) $request->get('mois',  now()->month);
        $annee = (int) $request->get('annee', now()->year);

        $budget = Budget::where('user_id', $user->id)
            ->where('mois', $mois)->where('annee', $annee)
            ->first();

        $depenses = $budget
            ? $budget->depenses()->with('categorie')->orderBy('date')->get()
            : collect();

        $moisLabel = Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F_Y');
        $filename  = "ecopoche_depenses_{$moisLabel}.csv";

        $lines = [];
        $lines[] = implode(';', ['Date', 'Catégorie', 'Désignation', 'Montant (FCFA)', 'Imprévue']);
        foreach ($depenses as $dep) {
            $lines[] = implode(';', [
                $dep->date->format('d/m/Y'),
                $dep->categorie?->nom ?? 'Autres',
                $dep->note ?? '',
                (int) $dep->montant,
                $dep->imprevue ? 'Oui' : 'Non',
            ]);
        }
        $lines[] = '';
        $lines[] = implode(';', ['', '', 'TOTAL', (int) $depenses->sum('montant'), '']);

        $content = "\xEF\xBB\xBF" . implode("\r\n", $lines);

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        /** @var User $user */
        $user  = Auth::user();
        $mois  = (int) $request->get('mois',  now()->month);
        $annee = (int) $request->get('annee', now()->year);

        $budget = Budget::where('user_id', $user->id)
            ->where('mois', $mois)->where('annee', $annee)
            ->with(['revenus', 'depenses.categorie', 'epargne'])
            ->first();

        $revenus         = $budget?->revenus ?? collect();
        $depenses        = $budget ? $budget->depenses()->with('categorie')->orderBy('date')->get() : collect();
        $totalDepensable = (int) $revenus->where('quota_applique', true)->sum('montant_dispo');
        $totalDepenses   = (int) $depenses->sum('montant');
        $solde           = (int) ($budget?->salaire_fixe ?? 0) + $totalDepensable - $totalDepenses;
        $epargne         = $budget?->epargne;

        $revenuTotal = (int) ($budget?->salaire_fixe ?? 0) + $totalDepensable;
        $sante = match(true) {
            $revenuTotal == 0                                                       => 'neutre',
            $totalDepenses == 0                                                     => 'sain',
            $solde > 0 && $revenuTotal > 0 && $totalDepenses / $revenuTotal < 0.7  => 'sain',
            $solde > 0                                                              => 'attention',
            default                                                                 => 'critique',
        };

        $parCategorie = $depenses->groupBy('categorie_id')
            ->map(fn($items) => [
                'total'   => (int) $items->sum('montant'),
                'nom'     => $items->first()->categorie?->nom ?? 'Autres',
                'couleur' => $items->first()->categorie?->couleur ?? '#6B7280',
            ])
            ->sortByDesc('total')
            ->take(8);

        $moisLabel = Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y');
        $filename  = 'ecopoche_bilan_' . Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F_Y') . '.pdf';

        $salaire = (int) ($budget?->salaire_fixe ?? 0);

        $pdf = Pdf::loadView('rapports.pdf.bilan', compact(
            'budget', 'user', 'revenus', 'depenses', 'epargne',
            'totalDepensable', 'totalDepenses', 'solde', 'sante', 'salaire',
            'parCategorie', 'moisLabel', 'mois', 'annee'
        ))->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
