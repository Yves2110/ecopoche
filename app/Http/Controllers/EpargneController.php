<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Epargne;
use App\Models\ObjectifEpargne;
use App\Models\Revenu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EpargneController extends Controller
{
    private function getBudgetOuCreer(int $mois, int $annee): Budget
    {
        return Budget::firstOrCreate(
            ['user_id' => Auth::id(), 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );
    }

    /**
     * Recalcule montant_actuel de chaque objectif non atteint en cumulant
     * l'épargne naturelle (épargne salaire + réserve bonus) de chaque mois
     * couvert par la période de l'objectif.
     */
    private function recalculerObjectifs(): void
    {
        $user = Auth::user();
        $pctSalaire = (float) ($user->epargne_salaire_pct ?? 0) / 100;

        $objectifs = ObjectifEpargne::where('user_id', $user->id)
            ->where('atteint', false)
            ->get();

        foreach ($objectifs as $objectif) {
            $debut = $objectif->date_debut ?? now()->startOfYear();
            $fin   = $objectif->date_fin   ?? now()->endOfMonth();

            // Budgets couverts par la période de l'objectif
            $budgets = Budget::where('user_id', $user->id)
                ->where(DB::raw("STR_TO_DATE(CONCAT(annee,'-',LPAD(mois,2,'0'),'-01'), '%Y-%m-%d')"), '>=', $debut->startOfMonth()->format('Y-m-d'))
                ->where(DB::raw("STR_TO_DATE(CONCAT(annee,'-',LPAD(mois,2,'0'),'-01'), '%Y-%m-%d')"), '<=', $fin->endOfMonth()->format('Y-m-d'))
                ->with('revenus')
                ->get();

            $cumul = 0;
            foreach ($budgets as $b) {
                // Épargne sur salaire fixe
                $cumul += $b->salaire_fixe * $pctSalaire;
                // Réserve bonus (70% des revenus variables)
                $cumul += $b->revenus->where('quota_applique', true)->sum('montant_dispo');
            }

            $cumul = (int) round($cumul);
            $objectif->update([
                'montant_actuel' => min($cumul, (float) $objectif->montant_cible),
            ]);
        }
    }

    public function index(Request $request)
    {
        $mois  = (int) $request->get('mois', now()->month);
        $annee = (int) $request->get('annee', now()->year);

        $budget  = $this->getBudgetOuCreer($mois, $annee);
        $epargne = $budget->epargne;

        $this->recalculerObjectifs();

        // Objectifs personnels (multi-mois)
        $objectifs = ObjectifEpargne::where('user_id', Auth::id())
            ->orderBy('atteint')
            ->orderBy('date_fin')
            ->get();

        // Historique 12 derniers mois (suivi mensuel)
        $historique = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $b = Budget::where('user_id', Auth::id())
                ->where('mois', $date->month)
                ->where('annee', $date->year)
                ->with('epargne')
                ->first();

            $historique[] = [
                'label'    => $date->translatedFormat('M Y'),
                'objectif' => (int) ($b?->epargne?->objectif ?? 0),
                'reel'     => (int) ($b?->epargne?->reel ?? 0),
                'deficit'  => $b?->epargne ? (int) max(0, $b->epargne->objectif - $b->epargne->reel) : 0,
                'mois'     => $date->month,
                'annee'    => $date->year,
                'actif'    => $date->month == $mois && $date->year == $annee,
            ];
        }

        $totalObjectif   = collect($historique)->sum('objectif');
        $totalReel       = collect($historique)->sum('reel');
        $totalDeficit    = collect($historique)->sum('deficit');
        $tauxRealisation = $totalObjectif > 0
            ? round($totalReel / $totalObjectif * 100)
            : 0;

        return view('epargne.index', compact(
            'budget', 'epargne', 'mois', 'annee',
            'objectifs', 'historique',
            'totalObjectif', 'totalReel', 'totalDeficit', 'tauxRealisation'
        ));
    }

    // Suivi mensuel (reel épargné ce mois)
    public function updateMensuel(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $data = $request->validate([
            'objectif' => ['required', 'integer', 'min:0'],
            'reel'     => ['required', 'integer', 'min:0'],
            'analyse'  => ['nullable', 'string', 'max:500'],
        ]);

        $deficit = max(0, $data['objectif'] - $data['reel']);

        Epargne::updateOrCreate(
            ['budget_id' => $budget->id],
            ['objectif' => $data['objectif'], 'reel' => $data['reel'],
             'deficit' => $deficit, 'analyse' => $data['analyse'] ?? null]
        );
        $budget->update(['epargne_objectif' => $data['objectif']]);
        $this->recalculerObjectifs();

        $msg = $deficit > 0
            ? 'Suivi enregistré. Déficit : ' . number_format($deficit, 0, ',', "\u{00A0}") . ' FCFA.'
            : "Objectif mensuel atteint \u{1F389}";

        return back()->with('success', $msg);
    }

    // Créer un objectif d'épargne personnel
    public function storeObjectif(Request $request)
    {
        $data = $request->validate([
            'nom'          => ['required', 'string', 'max:100'],
            'montant_cible'=> ['required', 'integer', 'min:1'],
            'date_debut'   => ['required', 'date'],
            'date_fin'     => ['nullable', 'date', 'after:date_debut'],
            'couleur'      => ['nullable', 'string', 'max:20'],
            'icone'        => ['nullable', 'string', 'max:50'],
            'note'         => ['nullable', 'string', 'max:500'],
        ]);

        ObjectifEpargne::create(array_merge($data, [
            'user_id'        => Auth::id(),
            'montant_actuel' => 0,
            'couleur'        => $data['couleur'] ?? '#006c49',
            'icone'          => $data['icone'] ?? 'savings',
        ]));
        $this->recalculerObjectifs();

        return back()->with('success', "Objectif « {$data['nom']} » créé.");
    }

    // Verser un montant vers un objectif
    public function verserObjectif(Request $request, ObjectifEpargne $objectif)
    {
        if ($objectif->user_id !== Auth::id()) abort(403);

        $data = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
        ]);

        $nouveau = min(
            (float) $objectif->montant_cible,
            (float) $objectif->montant_actuel + $data['montant']
        );
        $objectif->update([
            'montant_actuel' => $nouveau,
            'atteint' => $nouveau >= $objectif->montant_cible,
        ]);

        return back()->with('success', 'Versement enregistré.');
    }

    // Supprimer un objectif
    public function destroyObjectif(ObjectifEpargne $objectif)
    {
        if ($objectif->user_id !== Auth::id()) abort(403);
        $objectif->delete();
        return back()->with('success', 'Objectif supprimé.');
    }
}
