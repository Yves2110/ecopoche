<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\QuotaLog;
use App\Models\Revenu;
use App\Services\AlerteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenusController extends Controller
{
    private function getBudgetOuCreer(int $mois, int $annee): Budget
    {
        return Budget::firstOrCreate(
            ['user_id' => Auth::id(), 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );
    }

    public function index(Request $request)
    {
        $mois  = (int) $request->get('mois', now()->month);
        $annee = (int) $request->get('annee', now()->year);

        $budget = $this->getBudgetOuCreer($mois, $annee);

        $revenus       = $budget->revenus()->orderByDesc('date')->get();
        $bonusRevenus  = $revenus->where('quota_applique', true);
        // montant_quota = 30% dépensable, montant_dispo = 70% réserve
        $totalDepensable = (float) $bonusRevenus->sum('montant_quota');  // 30%
        $totalReserve    = (float) $bonusRevenus->sum('montant_dispo');   // 70%
        $debloque        = (float) $bonusRevenus->sum(fn($r) => optional($r->quotaLog)->debloquer ?? 0);
        $soldeReserve    = $totalReserve - $debloque; // réserve nette après déblocages

        $budgetPrecedent = Budget::where('user_id', Auth::id())
            ->where(function ($q) use ($mois, $annee) {
                if ($mois === 1) {
                    $q->where('mois', 12)->where('annee', $annee - 1);
                } else {
                    $q->where('mois', $mois - 1)->where('annee', $annee);
                }
            })->first();

        $variationSalaire = null;
        if ($budgetPrecedent && $budgetPrecedent->salaire_fixe > 0 && $budget->salaire_fixe > 0) {
            $variationSalaire = round(
                (($budget->salaire_fixe - $budgetPrecedent->salaire_fixe) / $budgetPrecedent->salaire_fixe) * 100, 1
            );
        }

        return view('revenus.index', compact(
            'budget', 'revenus', 'mois', 'annee',
            'totalDepensable', 'totalReserve', 'soldeReserve', 'variationSalaire'
        ));
    }

    public function updateSalaire(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $data = $request->validate([
            'salaire_fixe' => ['required', 'integer', 'min:0'],
        ]);

        $budget->update(['salaire_fixe' => $data['salaire_fixe']]);

        return back()->with('success', 'Salaire fixe mis à jour.');
    }

    public function storeRevenu(Request $request)
    {
        $data = $request->validate([
            'mois'        => ['required', 'integer', 'between:1,12'],
            'annee'       => ['required', 'integer', 'min:2020'],
            'type'        => ['required', 'in:bonus,extra'],
            'montant_brut'=> ['required', 'integer', 'min:1'],
            'date'        => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $budget = $this->getBudgetOuCreer((int)$data['mois'], (int)$data['annee']);

        $revenu = $budget->revenus()->create([
            'type'        => $data['type'],
            'montant_brut'=> $data['montant_brut'],
            'date'        => $data['date'],
            'description' => $data['description'] ?? null,
        ]);

        if ($revenu->quota_applique) {
            QuotaLog::create([
                'revenu_id'     => $revenu->id,
                'montant_brut'  => $revenu->montant_brut,
                'montant_quota' => $revenu->montant_quota,
                'montant_dispo' => $revenu->montant_dispo,
                'taux'          => 30.00,
                'debloquer'     => 0,
            ]);
        }

        AlerteService::quotaApplique(Auth::user(), $revenu->montant_brut, $revenu->montant_quota, $revenu->montant_dispo);
        AlerteService::analyserBudget(Auth::user(), $budget->fresh());

        return back()->with('success', 'Revenu ajouté. Dépensable ce mois : ' . number_format($revenu->montant_quota, 0, ',', ' ') . ' FCFA (30%) · Réserve : ' . number_format($revenu->montant_dispo, 0, ',', ' ') . ' FCFA (70%).');
    }

    public function debloquerReserve(Request $request, Revenu $revenu)
    {
        $this->authorize('update', $revenu->budget);

        $data = $request->validate([
            'montant'       => ['required', 'integer', 'min:1'],
            'justification' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $log = $revenu->quotaLog;

        if (!$log) {
            return back()->withErrors(['montant' => 'Aucun quota trouvé pour ce revenu.']);
        }

        $reserveRestante = $log->montant_quota - $log->debloquer;

        if ($data['montant'] > $reserveRestante) {
            return back()->withErrors(['montant' => 'Montant supérieur à la réserve disponible (' . number_format($reserveRestante, 0, ',', ' ') . ' FCFA).']);
        }

        $log->update([
            'debloquer'               => $log->debloquer + $data['montant'],
            'justification_deblocage' => $data['justification'],
        ]);

        return back()->with('success', number_format($data['montant'], 0, ',', ' ') . ' FCFA débloqués de la réserve.');
    }

    public function destroyRevenu(Revenu $revenu)
    {
        $this->authorize('update', $revenu->budget);
        $revenu->delete();
        return back()->with('success', 'Revenu supprimé.');
    }
}
