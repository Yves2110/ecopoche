<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\Depense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\AlerteService;
use Illuminate\Support\Facades\Auth;

class DepensesController extends Controller
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
        $vue   = $request->get('vue', 'mois');

        $budget     = $this->getBudgetOuCreer($mois, $annee);
        $categories = Categorie::where('user_id', Auth::id())
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        $depensesQuery = $budget->depenses()->with('categorie');

        if ($vue === 'jour') {
            $date = $request->get('date', now()->format('Y-m-d'));
            $depenses = $depensesQuery->whereDate('date', $date)->orderByDesc('created_at')->get();
            $totalVue = $depenses->sum('montant');
        } elseif ($vue === 'semaine') {
            $debut = Carbon::parse($request->get('semaine_debut', now()->startOfWeek()->format('Y-m-d')));
            $fin   = $debut->copy()->endOfWeek();
            $depenses = $depensesQuery->whereBetween('date', [$debut, $fin])->orderBy('date')->get();
            $totalVue = $depenses->sum('montant');
        } else {
            $depenses = $depensesQuery->orderByDesc('date')->orderByDesc('created_at')->get();
            $totalVue = $depenses->sum('montant');
        }

        $parCategorie = $budget->depenses()
            ->with('categorie')
            ->get()
            ->groupBy('categorie_id')
            ->map(fn($items) => [
                'total'     => $items->sum('montant'),
                'categorie' => $items->first()->categorie,
                'count'     => $items->count(),
            ])
            ->sortByDesc('total');

        $totalMois = $budget->depenses()->sum('montant');

        // Revenus variables (bonus) avec quota
        $revenus = $budget->revenus()->get();
        $totalDepensable = (float) $revenus->where('quota_applique', true)->sum('montant_quota'); // 30% utilisable

        // Épargne programmée sur salaire fixe
        $user = Auth::user();
        $epargneSalaire = (float) ($budget->salaire_fixe * ($user->epargne_salaire_pct ?? 0) / 100);

        // Solde restant = salaire fixe - épargne + bonus dépensable - dépenses
        $soldeRestant = (float) $budget->salaire_fixe - $epargneSalaire + $totalDepensable - $totalMois;

        return view('depenses.index', compact(
            'budget', 'depenses', 'categories', 'mois', 'annee',
            'vue', 'totalVue', 'totalMois', 'parCategorie', 'soldeRestant'
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

        $budget = $this->getBudgetOuCreer((int)$data['mois'], (int)$data['annee']);

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

    public function destroy(Depense $depense)
    {
        $this->authorize('update', $depense->budget);
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
}
