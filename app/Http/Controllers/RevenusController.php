<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesBudgetPeriod;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Models\QuotaLog;
use App\Models\Revenu;
use App\Services\AlerteService;
use App\Services\BudgetPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RevenusController extends Controller
{
    use ResolvesBudgetPeriod;

    private function getBudgetOuCreer(int $mois, int $annee): Budget
    {
        return Budget::firstOrCreate(
            ['user_id' => Auth::id(), 'mois' => $mois, 'annee' => $annee],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        ['mois' => $mois, 'annee' => $annee] = $this->resolveMoisAnnee($request, $user, allowFuture: true);

        $budget = $this->getBudgetOuCreer($mois, $annee);
        [$debutPeriode, $finPeriode] = BudgetPeriodService::bornes($user, $mois, $annee);

        $revenus = $budget->revenus()
            ->orderByDesc('date')
            ->get()
            ->filter(fn (Revenu $r) => BudgetPeriodService::dateDansPeriode($user, $r->date, $mois, $annee))
            ->values();
        $bonusRevenus  = $revenus->where('quota_applique', true);
        $totalDepensable = Revenu::sumDepensable($revenus);
        $totalReserve    = Revenu::sumReserve($revenus);
        $debloque        = (float) $bonusRevenus->sum(fn ($r) => optional($r->quotaLog)->debloquer ?? 0);
        $soldeReserve    = $totalReserve - $debloque;

        $periodeLabel       = BudgetPeriodService::label($user, $mois, $annee);
        $estPeriodeCourante = BudgetPeriodService::estPeriodeCourante($user, $mois, $annee);
        $estPeriodePassee   = BudgetPeriodService::estPeriodePassee($user, $mois, $annee);
        $estPeriodeFuture   = BudgetPeriodService::estPeriodeFuture($user, $mois, $annee);
        $periodeCourante    = BudgetPeriodService::resolvePeriode($user);

        // Épargne programmée sur salaire fixe
        $epargnesSalairePct = (int) ($user->epargne_salaire_pct ?? 0);
        $epargneSalaire     = (float) round($budget->salaire_fixe * $epargnesSalairePct / 100, 2);
        $salaireDisponible  = (float) $budget->salaire_fixe - $epargneSalaire;

        $prec = BudgetPeriodService::periodePrecedente($mois, $annee);
        $budgetPrecedent = Budget::where('user_id', Auth::id())
            ->where('mois', $prec['mois'])
            ->where('annee', $prec['annee'])
            ->first();

        $variationSalaire = null;
        if ($budgetPrecedent && $budgetPrecedent->salaire_fixe > 0 && $budget->salaire_fixe > 0) {
            $variationSalaire = round(
                (($budget->salaire_fixe - $budgetPrecedent->salaire_fixe) / $budgetPrecedent->salaire_fixe) * 100, 1
            );
        }

        return view('revenus.index', compact(
            'budget', 'revenus', 'mois', 'annee',
            'totalDepensable', 'totalReserve', 'soldeReserve', 'variationSalaire',
            'epargnesSalairePct', 'epargneSalaire', 'salaireDisponible',
            'periodeLabel', 'estPeriodeCourante', 'estPeriodePassee', 'estPeriodeFuture',
            'periodeCourante', 'debutPeriode', 'finPeriode',
        ));
    }

    public function updateSalaire(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        if (BudgetPeriodService::estPeriodePassee(Auth::user(), $budget->mois, $budget->annee)) {
            return back()->withErrors(['salaire_fixe' => 'Cette période est clôturée.']);
        }

        $data = $request->validate([
            'salaire_fixe' => ['required', 'integer', 'min:0'],
        ]);

        $budget->update(['salaire_fixe' => $data['salaire_fixe']]);

        return back()->with('success', 'Salaire fixe mis à jour.');
    }

    public function copierSalaireMoisPrecedent(Request $request)
    {
        $user = Auth::user();
        ['mois' => $mois, 'annee' => $annee] = $this->resolveMoisAnnee($request, $user, allowFuture: true);

        if (BudgetPeriodService::estPeriodePassee($user, $mois, $annee)) {
            return back()->withErrors(['salaire' => 'Cette période est clôturée.']);
        }

        $budget = $this->getBudgetOuCreer($mois, $annee);

        if ($budget->salaire_fixe > 0) {
            return back()->with('info', 'Le salaire de cette période est déjà renseigné.');
        }

        $prec = BudgetPeriodService::periodePrecedente($mois, $annee);
        $budgetPrec = Budget::where('user_id', Auth::id())
            ->where('mois', $prec['mois'])
            ->where('annee', $prec['annee'])
            ->first();

        if (! $budgetPrec || $budgetPrec->salaire_fixe <= 0) {
            return back()->withErrors(['salaire' => 'Aucun salaire trouvé sur le mois précédent.']);
        }

        $budget->update(['salaire_fixe' => $budgetPrec->salaire_fixe]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'salaire_copie_mois_precedent',
            'description' => 'Salaire repris du mois précédent (' . number_format((int) $budget->salaire_fixe, 0, ',', "\u{00A0}") . ' FCFA).',
            'ip_address'  => $request->ip(),
            'meta'        => ['mois' => $mois, 'annee' => $annee],
        ]);

        return back()->with('success', 'Salaire repris du mois précédent : ' . number_format((int) $budget->salaire_fixe, 0, ',', "\u{00A0}") . ' FCFA.');
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

        $user = Auth::user();
        $date = Carbon::parse($data['date']);
        $periodeDate = BudgetPeriodService::resolvePeriodePourDate($user, $date);

        if ((int) $data['mois'] !== $periodeDate['mois'] || (int) $data['annee'] !== $periodeDate['annee']) {
            return back()->withErrors([
                'date' => 'La date doit correspondre à la période affichée (' . BudgetPeriodService::label($user, (int) $data['mois'], (int) $data['annee']) . ').',
            ])->withInput();
        }

        if (! BudgetPeriodService::dateDansPeriode($user, $date, (int) $data['mois'], (int) $data['annee'])) {
            return back()->withErrors(['date' => 'La date est hors de la période sélectionnée.'])->withInput();
        }

        if (BudgetPeriodService::estPeriodePassee($user, (int) $data['mois'], (int) $data['annee'])) {
            return back()->withErrors(['date' => 'Impossible d\'ajouter un revenu sur une période clôturée.'])->withInput();
        }

        $budget = $this->getBudgetOuCreer($periodeDate['mois'], $periodeDate['annee']);

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

    public function updateRevenu(Request $request, Revenu $revenu)
    {
        $this->authorize('update', $revenu);

        if ($revenu->dette_id) {
            return back()->withErrors(['revenu' => 'Ce revenu lié à une dette ne peut pas être modifié ici.']);
        }

        $data = $request->validate([
            'type'         => ['required', 'in:bonus,extra'],
            'montant_brut' => ['required', 'integer', 'min:1'],
            'date'         => ['required', 'date'],
            'description'  => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $date = Carbon::parse($data['date']);
        $budget = $revenu->budget;

        if (BudgetPeriodService::estPeriodePassee($user, $budget->mois, $budget->annee)) {
            return back()->withErrors(['revenu' => 'Cette période est clôturée.']);
        }

        if (! BudgetPeriodService::dateDansPeriode($user, $date, $budget->mois, $budget->annee)) {
            return back()->withErrors(['date' => 'La date doit rester dans la période ' . BudgetPeriodService::label($user, $budget->mois, $budget->annee) . '.']);
        }
        $tauxPct = (int) ($user->quota_taux ?? 30);
        $taux    = $tauxPct / 100;
        $brut    = (float) $data['montant_brut'];

        $revenu->update([
            'type'           => $data['type'],
            'montant_brut'   => $brut,
            'montant_quota'  => round($brut * $taux, 2),
            'montant_dispo'  => round($brut * (1 - $taux), 2),
            'quota_applique' => true,
            'date'           => $data['date'],
            'description'    => $data['description'] ?? null,
        ]);

        if ($log = $revenu->quotaLog) {
            $log->update([
                'montant_brut'  => $brut,
                'montant_quota' => $revenu->montant_quota,
                'montant_dispo' => $revenu->montant_dispo,
                'taux'          => $tauxPct,
            ]);
        }

        AlerteService::analyserBudget($user, $revenu->budget->fresh());

        return back()->with('success', 'Revenu mis à jour.');
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
        $this->authorize('delete', $revenu);
        $revenu->delete();
        return back()->with('success', 'Revenu supprimé.');
    }
}
