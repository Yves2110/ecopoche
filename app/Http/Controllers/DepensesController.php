<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesBudgetPeriod;
use App\Models\Budget;
use App\Models\Categorie;
use App\Models\Depense;
use App\Models\Revenu;
use App\Services\BudgetPeriodService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Services\AlerteService;
use App\Services\DepenseCsvImportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DepensesController extends Controller
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
        $vue   = $request->get('vue', 'mois');

        [$debutPeriode, $finPeriode] = BudgetPeriodService::bornes($user, $mois, $annee);
        $periodeLabel       = BudgetPeriodService::label($user, $mois, $annee);
        $estPeriodeCourante = BudgetPeriodService::estPeriodeCourante($user, $mois, $annee);
        $estPeriodePassee   = BudgetPeriodService::estPeriodePassee($user, $mois, $annee);
        $estPeriodeFuture   = BudgetPeriodService::estPeriodeFuture($user, $mois, $annee);

        $budget     = $this->getBudgetOuCreer($mois, $annee);
        $categories = Categorie::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        $depensesQuery = $budget->depenses()
            ->with('categorie')
            ->whereBetween('date', [$debutPeriode, $finPeriode]);

        if ($vue === 'jour') {
            $defaultDate = $estPeriodeCourante ? now() : $finPeriode;
            $date = Carbon::parse($request->get('date', $defaultDate->format('Y-m-d')));
            if ($date->lt($debutPeriode)) {
                $date = $debutPeriode->copy();
            }
            if ($date->gt($finPeriode)) {
                $date = $finPeriode->copy();
            }
            $depenses = $depensesQuery->whereDate('date', $date)->orderByDesc('created_at')->get();
            $totalVue = $depenses->sum('montant');
        } elseif ($vue === 'semaine') {
            $defaultDebut = $estPeriodeCourante ? now()->startOfWeek() : $finPeriode->copy()->startOfWeek();
            $debut = Carbon::parse($request->get('semaine_debut', $defaultDebut->format('Y-m-d')));
            $fin   = $debut->copy()->endOfWeek();
            if ($debut->lt($debutPeriode)) {
                $debut = $debutPeriode->copy();
            }
            if ($fin->gt($finPeriode)) {
                $fin = $finPeriode->copy();
            }
            $depenses = $depensesQuery->whereBetween('date', [$debut, $fin])->orderBy('date')->get();
            $totalVue = $depenses->sum('montant');
        } else {
            $depenses = $depensesQuery->orderByDesc('date')->orderByDesc('created_at')->get();
            $totalVue = $depenses->sum('montant');
        }

        $depensesPeriode = $budget->depenses()
            ->with('categorie')
            ->whereBetween('date', [$debutPeriode, $finPeriode])
            ->get();

        // Répartition : chaque catégorie affiche son propre total (y compris les dépenses marquées
        // imprévues qui y sont classées). La catégorie Imprévus affiche le total global de toutes
        // les dépenses imprévues, pour une vue d'ensemble par catégorie ET par nature imprévue.
        $imprevusCat = $categories->firstWhere('nom', 'Imprévus');

        $grouped = [];
        foreach ($depensesPeriode as $depense) {
            $cat = $depense->categorie;

            // Total par catégorie d'origine
            $key = $cat?->id ?? 'unknown';
            if (! isset($grouped[$key])) {
                $grouped[$key] = ['total' => 0, 'categorie' => $cat, 'count' => 0];
            }
            $grouped[$key]['total'] += (float) $depense->montant;
            $grouped[$key]['count']++;
        }

        // Imprévus = total global des dépenses marquées imprévues
        $imprevusExpenses = $depensesPeriode->where('imprevue', true);
        if ($imprevusExpenses->isNotEmpty()) {
            $imprevusKey = $imprevusCat?->id ?? 'imprevus';
            $grouped[$imprevusKey] = [
                'total'     => (int) $imprevusExpenses->sum('montant'),
                'categorie' => $imprevusCat ?? (object) [
                    'nom'             => 'Imprévus',
                    'icone'           => 'warning',
                    'couleur'         => '#EF4444',
                    'plafond_mensuel' => null,
                ],
                'count' => $imprevusExpenses->count(),
            ];
        }

        $parCategorie = collect($grouped)
            ->map(fn ($g) => ['total' => (int) $g['total'], 'categorie' => $g['categorie'], 'count' => $g['count']])
            ->sortByDesc('total');

        $totalMois = (int) $depensesPeriode->sum('montant');

        // Revenus variables (bonus) avec quota
        $revenus = $budget->revenus()->get();
        $totalDepensable = Revenu::sumDepensable($revenus);

        $epargneSalaire = (float) ($budget->salaire_fixe * ($user->epargne_salaire_pct ?? 0) / 100);

        // Solde restant = salaire fixe - épargne + bonus dépensable - dépenses
        $soldeRestant = (float) $budget->salaire_fixe - $epargneSalaire + $totalDepensable - $totalMois;

        return view('depenses.index', compact(
            'budget', 'depenses', 'categories', 'mois', 'annee',
            'vue', 'totalVue', 'totalMois', 'parCategorie', 'soldeRestant',
            'periodeLabel', 'estPeriodeCourante', 'estPeriodePassee', 'estPeriodeFuture',
            'debutPeriode', 'finPeriode',
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mois'        => ['required', 'integer', 'between:1,12'],
            'annee'       => ['required', 'integer', 'min:2020'],
            'categorie_id'=> ['required', 'exists:categories,id'],
            'montant'     => ['required', 'integer', 'min:1'],
            'date'        => ['required', 'date'],
            'note'        => ['nullable', 'string', 'max:255'],
            'imprevue'    => ['boolean'],
        ]);

        $categorie = Categorie::where('id', $data['categorie_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $user = Auth::user();
        if (BudgetPeriodService::estPeriodePassee($user, (int) $data['mois'], (int) $data['annee'])) {
            return back()->withErrors(['date' => 'Cette période est clôturée.'])->withInput();
        }

        $dateDep = Carbon::parse($data['date']);
        $periodeDate = BudgetPeriodService::resolvePeriodePourDate($user, $dateDep);
        $budget = $this->getBudgetOuCreer($periodeDate['mois'], $periodeDate['annee']);

        if (! BudgetPeriodService::dateDansPeriode($user, $dateDep, (int) $data['mois'], (int) $data['annee'])) {
            return back()->withErrors([
                'date' => 'La date doit être dans la période budgétaire sélectionnée.',
            ])->withInput();
        }

        $budget->depenses()->create([
            'categorie_id' => $categorie->id,
            'montant'      => $data['montant'],
            'date'         => $data['date'],
            'note'         => $data['note'] ?? null,
            'imprevue'     => $request->boolean('imprevue'),
        ]);

        AlerteService::analyserBudget(Auth::user(), $budget->fresh());

        return back()->with('success', 'Dépense de ' . number_format($data['montant'], 0, ',', "\u{00A0}") . ' FCFA enregistrée.');
    }

    public function update(Request $request, Depense $depense)
    {
        $this->authorize('update', $depense);

        $user = Auth::user();
        $budget = $depense->budget;
        if (BudgetPeriodService::estPeriodePassee($user, $budget->mois, $budget->annee)) {
            return back()->withErrors(['date' => 'Cette période est clôturée.']);
        }

        $data = $request->validate([
            'categorie_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', Auth::id())),
            ],
            'montant'  => ['required', 'integer', 'min:1'],
            'date'     => ['required', 'date'],
            'note'     => ['nullable', 'string', 'max:255'],
            'imprevue' => ['boolean'],
        ]);

        $dateDep = Carbon::parse($data['date']);
        if (! BudgetPeriodService::dateDansPeriode($user, $dateDep, $budget->mois, $budget->annee)) {
            return back()->withErrors([
                'date' => 'La date doit rester dans la période ' . BudgetPeriodService::label($user, $budget->mois, $budget->annee) . '.',
            ]);
        }

        $depense->update([
            'categorie_id' => $data['categorie_id'],
            'montant'      => $data['montant'],
            'date'         => $data['date'],
            'note'         => $data['note'] ?? null,
            'imprevue'     => $request->boolean('imprevue'),
        ]);

        AlerteService::analyserBudget($user, $depense->budget->fresh());

        return back()->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Depense $depense)
    {
        $this->authorize('delete', $depense);
        $depense->delete();
        return back()->with('success', 'Dépense supprimée.');
    }

    public function updateCategories(Request $request)
    {
        $data = $request->validate([
            'categories'                => ['required', 'array'],
            'categories.*.id'           => ['required', 'exists:categories,id'],
            'categories.*.plafond'      => ['nullable', 'integer', 'min:0'],
        ]);

        foreach ($data['categories'] as $item) {
            Categorie::where('id', $item['id'])
                ->where('user_id', Auth::id())
                ->update(['plafond_mensuel' => $item['plafond'] ?: null]);
        }

        return back()->with('success', 'Plafonds mis à jour.');
    }

    public function storeCategorie(Request $request)
    {
        $data = $request->validate([
            'nom'            => ['required', 'string', 'max:100'],
            'icone'          => ['nullable', 'string', 'max:60'],
            'couleur'        => ['nullable', 'string', 'max:20'],
            'plafond_mensuel'=> ['nullable', 'integer', 'min:0'],
        ]);

        Categorie::create([
            'user_id'        => Auth::id(),
            'nom'            => $data['nom'],
            'icone'          => $data['icone'] ?? 'category',
            'couleur'        => $data['couleur'] ?? '#6B7280',
            'type'           => 'depense',
            'plafond_mensuel'=> $data['plafond_mensuel'] ?? null,
            'is_default'     => false,
            'is_active'      => true,
            'ordre'          => Categorie::where('user_id', Auth::id())->max('ordre') + 1,
        ]);

        return back()->with('success', 'Catégorie créée.');
    }

    public function importCsv(Request $request, DepenseCsvImportService $importer)
    {
        $data = $request->validate([
            'csv'   => ['required', 'file', 'mimes:csv,txt', 'max:512'],
            'mois'  => ['required', 'integer', 'between:1,12'],
            'annee' => ['required', 'integer', 'min:2020'],
        ]);

        $user = Auth::user();
        if (BudgetPeriodService::estPeriodePassee($user, (int) $data['mois'], (int) $data['annee'])) {
            return back()->withErrors(['csv' => 'Cette période est clôturée.']);
        }

        $result = $importer->import($user, $data['csv'], (int) $data['mois'], (int) $data['annee']);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'import_csv_depenses',
            'description' => "Import CSV : {$result['imported']} dépense(s), {$result['skipped']} ignorée(s).",
            'ip_address'  => $request->ip(),
            'meta'        => ['errors' => array_slice($result['errors'], 0, 20)],
        ]);

        $msg = "{$result['imported']} dépense(s) importée(s).";
        if ($result['skipped'] > 0) {
            $msg .= " {$result['skipped']} ligne(s) ignorée(s).";
        }

        return back()
            ->with('success', $msg)
            ->with('import_errors', $result['errors']);
    }
}
