<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Services\AlerteService;
use App\Services\BudgetPeriodService;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        AlerteService::cloturerAlertesPeriodesExpirees($user);

        $filtre = $request->get('filtre', 'toutes');

        $query = Alerte::where('user_id', Auth::id())
            ->orderByDesc('created_at');

        if ($filtre === 'non_lues') {
            $query->whereNull('lu_at');
        } elseif ($filtre === 'lues') {
            $query->whereNotNull('lu_at');
        }

        $alertes = $query->paginate(20);

        $courant = BudgetPeriodService::resolvePeriode($user);
        $nonLues = AlerteService::compterNonLues($user);
        $periodeLabel = BudgetPeriodService::label($user, $courant['mois'], $courant['annee']);

        return view('alertes.index', compact('alertes', 'filtre', 'nonLues', 'periodeLabel'));
    }

    public function marquerLue(Alerte $alerte)
    {
        if ($alerte->user_id !== Auth::id()) abort(403);
        $alerte->update(['lu_at' => now()]);
        return back();
    }

    public function toutMarquerLu()
    {
        Alerte::where('user_id', Auth::id())
            ->whereNull('lu_at')
            ->update(['lu_at' => now()]);
        return back()->with('success', 'Toutes les alertes ont été marquées comme lues.');
    }

    public function supprimer(Alerte $alerte)
    {
        if ($alerte->user_id !== Auth::id()) abort(403);
        $alerte->delete();
        return back();
    }

    public function toutSupprimer()
    {
        Alerte::where('user_id', Auth::id())
            ->whereNotNull('lu_at')
            ->delete();
        return back()->with('success', 'Alertes lues supprimées.');
    }

    // Endpoint AJAX : nombre de non-lues
    public function compteur()
    {
        return response()->json([
            'count' => AlerteService::compterNonLues(Auth::user()),
        ]);
    }

    // Analyser le budget courant manuellement
    public function analyser()
    {
        $user = Auth::user();
        $periode = BudgetPeriodService::resolvePeriode($user);
        $budget = Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $periode['mois'], 'annee' => $periode['annee']],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );
        AlerteService::analyserBudget($user, $budget);
        return back()->with('success', 'Analyse du budget effectuée.');
    }
}
