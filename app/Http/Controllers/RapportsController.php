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

            $revenus         = $budget?->revenus ?? collect();
            $depenses        = $budget?->depenses ?? collect();
            $salaire         = (int) ($budget?->salaire_fixe ?? 0);

            // montant_quota = part dépensable ce mois (ex: 30% du bonus)
            // montant_dispo = part réservée/épargnée (ex: 70% du bonus)
            $bonusDepensable = (int) $revenus->where('quota_applique', true)->sum('montant_quota');
            $bonusEpargne    = (int) $revenus->where('quota_applique', true)->sum('montant_dispo');

            // Épargne sur salaire fixe
            $epargnesSalairePct = (int) ($user->epargne_salaire_pct ?? 0);
            $epargneSalaire     = (int) round($salaire * $epargnesSalairePct / 100);

            $totalDep    = (int) $depenses->sum('montant');
            $revenuTotal = $salaire + $bonusDepensable;
            $solde       = $revenuTotal - $totalDep;

            // Épargne = ce qui est stocké en DB (salaire_pct + bonus_réserve)
            $epargne = (int) ($budget?->epargne?->reel ?? ($epargneSalaire + $bonusEpargne));

            $data[] = [
                'label'       => $date->translatedFormat('M Y'),
                'mois'        => $date->month,
                'annee'       => $date->year,
                'salaire'        => $salaire,
                'bonus_dispo'    => $bonusDepensable,
                'epargne_salaire'=> $epargneSalaire,
                'bonus_epargne'  => $bonusEpargne,
                'revenu'         => $revenuTotal,
                'depenses'    => $totalDep,
                'solde'       => $solde,
                'epargne'     => $epargne,
                'actif'       => $date->month == now()->month && $date->year == now()->year,
                'budget'      => $budget,
            ];
        }

        return $data;
    }

    private function buildAggregats(array $historique): array
    {
        $totalRevenu   = collect($historique)->sum('revenu');
        $totalDepenses = collect($historique)->sum('depenses');
        $totalEpargne  = collect($historique)->sum('epargne');
        $tauxEpargne   = $totalRevenu > 0 ? round($totalEpargne / $totalRevenu * 100) : 0;

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

        return compact('totalRevenu', 'totalDepenses', 'totalEpargne', 'tauxEpargne', 'topCategories');
    }

    private function anneesDisponibles(): array
    {
        return Budget::where('user_id', Auth::id())
            ->selectRaw('DISTINCT annee')
            ->orderByDesc('annee')
            ->pluck('annee')
            ->toArray();
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
        $agg = $this->buildAggregats($historique);

        // Mois courant pour CSV/PDF export mensuel
        $budgetActif = Budget::where('user_id', $user->id)
            ->where('mois', $mois)->where('annee', $annee)
            ->with(['revenus', 'depenses.categorie', 'epargne'])
            ->first();

        $anneesDisponibles = $this->anneesDisponibles();

        return view('rapports.index', array_merge(compact(
            'historique', 'mois', 'annee', 'nbMois', 'budgetActif', 'anneesDisponibles'
        ), $agg));
    }

    public function exportPdfComparatif(Request $request): \Illuminate\Http\Response
    {
        /** @var User $user */
        $user   = Auth::user();
        $mois   = (int) $request->get('mois',  now()->month);
        $annee  = (int) $request->get('annee', now()->year);
        $nbMois = (int) $request->get('periode', 12);
        if (!in_array($nbMois, [6, 12])) $nbMois = 12;

        $historique    = $this->getUserBudgets($mois, $annee, $nbMois);
        $agg           = $this->buildAggregats($historique);
        $periodeLabel  = $nbMois === 6 ? '6 derniers mois' : '12 derniers mois';
        $filename      = 'ecopoche_comparatif_' . $annee . '_' . $mois . '.pdf';

        $pdf = Pdf::loadView('rapports.pdf.comparatif', array_merge(
            compact('historique', 'user', 'periodeLabel', 'mois', 'annee'),
            $agg
        ))->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function exportCsvComparatif(Request $request): Response
    {
        /** @var User $user */
        $user   = Auth::user();
        $mois   = (int) $request->get('mois',  now()->month);
        $annee  = (int) $request->get('annee', now()->year);
        $nbMois = (int) $request->get('periode', 12);
        if (!in_array($nbMois, [6, 12])) $nbMois = 12;

        $historique = $this->getUserBudgets($mois, $annee, $nbMois);
        $agg        = $this->buildAggregats($historique);

        $periodeLabel = $nbMois === 6 ? '6 derniers mois' : '12 derniers mois';
        $filename = 'ecopoche_comparatif_' . $annee . '_' . $mois . '.csv';

        $lines = [];
        $lines[] = "EcoPoche — Rapport comparatif : {$periodeLabel}";
        $lines[] = "Généré le : " . now()->translatedFormat('d F Y à H:i');
        $lines[] = '';
        $lines[] = implode(';', ['Mois', 'Revenus', 'Dépenses', 'Solde', 'Épargne', 'Taux épargne %', 'Santé']);

        foreach (array_reverse($historique) as $h) {
            $taux  = $h['revenu'] > 0 ? round($h['depenses'] / $h['revenu'] * 100) : 0;
            $sante = match(true) {
                $h['revenu'] == 0 => '—',
                $h['solde'] < 0   => 'Dépassé',
                $taux >= 70        => 'Attention',
                default            => 'Sain',
            };
            $tauxEp = $h['revenu'] > 0 ? round($h['epargne'] / $h['revenu'] * 100, 1) : 0;
            $lines[] = implode(';', [
                $h['label'],
                $h['revenu'],
                $h['depenses'],
                $h['solde'],
                $h['epargne'],
                $tauxEp,
                $sante,
            ]);
        }
        $lines[] = '';
        $lines[] = implode(';', ['TOTAL', $agg['totalRevenu'], $agg['totalDepenses'],
            $agg['totalRevenu'] - $agg['totalDepenses'], $agg['totalEpargne'], $agg['tauxEpargne'] . '%', '']);

        $lines[] = '';
        $lines[] = 'TOP CATÉGORIES';
        $lines[] = implode(';', ['Catégorie', 'Total dépensé']);
        foreach ($agg['topCategories'] as $cat) {
            $lines[] = implode(';', [$cat['nom'], $cat['total']]);
        }

        $content = "\xEF\xBB\xBF" . implode("\r\n", $lines);
        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function bilanAnnuel(Request $request): \Illuminate\View\View
    {
        /** @var User $user */
        $user  = Auth::user();
        $annee = (int) $request->get('annee', now()->year - 1);

        // Les 12 mois de l'année sélectionnée
        $historique = $this->getUserBudgets(12, $annee, 12);

        $agg = $this->buildAggregats($historique);
        $anneesDisponibles = $this->anneesDisponibles();

        // Meilleur et pire mois
        $meilleurMois = collect($historique)->filter(fn($h) => $h['revenu'] > 0)
            ->sortByDesc(fn($h) => $h['solde'])->first();
        $pireMois     = collect($historique)->filter(fn($h) => $h['revenu'] > 0)
            ->sortBy(fn($h) => $h['solde'])->first();

        return view('rapports.bilan-annuel', array_merge(compact(
            'historique', 'annee', 'anneesDisponibles', 'meilleurMois', 'pireMois'
        ), $agg));
    }

    public function exportPdfBilanAnnuel(Request $request): \Illuminate\Http\Response
    {
        /** @var User $user */
        $user  = Auth::user();
        $annee = (int) $request->get('annee', now()->year - 1);

        $historique = $this->getUserBudgets(12, $annee, 12);
        $agg        = $this->buildAggregats($historique);

        $meilleurMois = collect($historique)->filter(fn($h) => $h['revenu'] > 0)
            ->sortByDesc(fn($h) => $h['solde'])->first();
        $pireMois     = collect($historique)->filter(fn($h) => $h['revenu'] > 0)
            ->sortBy(fn($h) => $h['solde'])->first();

        $periodeLabel = "Bilan annuel {$annee}";
        $filename     = "ecopoche_bilan_annuel_{$annee}.pdf";

        $pdf = Pdf::loadView('rapports.pdf.comparatif', array_merge(
            compact('historique', 'user', 'periodeLabel', 'annee', 'meilleurMois', 'pireMois'),
            $agg,
            ['mois' => 12]
        ))->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function saveChartImageTemp(?string $base64): ?string
    {
        if (!$base64 || !str_starts_with($base64, 'data:image/png;base64,')) {
            return null;
        }
        $data    = base64_decode(substr($base64, strlen('data:image/png;base64,')));
        $tmpPath = sys_get_temp_dir() . '/ecopoche_chart_' . uniqid() . '.png';
        file_put_contents($tmpPath, $data);
        return $tmpPath;
    }

    public function exportPdfAvecGraphique(Request $request): \Illuminate\Http\Response
    {
        /** @var User $user */
        $user   = Auth::user();
        $mois   = (int) $request->get('mois',  now()->month);
        $annee  = (int) $request->get('annee', now()->year);
        $nbMois = (int) $request->get('periode', 12);
        if (!in_array($nbMois, [6, 12])) $nbMois = 12;

        $tmpChart   = $this->saveChartImageTemp($request->input('chart_image'));
        $chartImage = $tmpChart ? 'file:///' . str_replace('\\', '/', $tmpChart) : null;

        $historique    = $this->getUserBudgets($mois, $annee, $nbMois);
        $agg           = $this->buildAggregats($historique);
        $periodeLabel  = $nbMois === 6 ? '6 derniers mois' : '12 derniers mois';
        $filename      = 'ecopoche_comparatif_' . $annee . '_' . $mois . '.pdf';

        $pdf = Pdf::loadView('rapports.pdf.comparatif', array_merge(
            compact('historique', 'user', 'periodeLabel', 'mois', 'annee', 'chartImage'),
            $agg
        ))->setPaper('a4', 'landscape');

        $response = $pdf->download($filename);
        if ($tmpChart && file_exists($tmpChart)) @unlink($tmpChart);
        return $response;
    }

    public function exportPdfBilanAnnuelGraphique(Request $request): \Illuminate\Http\Response
    {
        /** @var User $user */
        $user  = Auth::user();
        $annee = (int) $request->get('annee', now()->year - 1);

        $tmpChart   = $this->saveChartImageTemp($request->input('chart_image'));
        $chartImage = $tmpChart ? 'file:///' . str_replace('\\', '/', $tmpChart) : null;

        $historique = $this->getUserBudgets(12, $annee, 12);
        $agg        = $this->buildAggregats($historique);

        $meilleurMois = collect($historique)->filter(fn($h) => $h['revenu'] > 0)
            ->sortByDesc(fn($h) => $h['solde'])->first();
        $pireMois     = collect($historique)->filter(fn($h) => $h['revenu'] > 0)
            ->sortBy(fn($h) => $h['solde'])->first();

        $periodeLabel = "Bilan annuel {$annee}";
        $filename     = "ecopoche_bilan_annuel_{$annee}.pdf";

        $pdf = Pdf::loadView('rapports.pdf.comparatif', array_merge(
            compact('historique', 'user', 'periodeLabel', 'annee', 'meilleurMois', 'pireMois', 'chartImage'),
            $agg,
            ['mois' => 12]
        ))->setPaper('a4', 'landscape');

        $response = $pdf->download($filename);
        if ($tmpChart && file_exists($tmpChart)) @unlink($tmpChart);
        return $response;
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
