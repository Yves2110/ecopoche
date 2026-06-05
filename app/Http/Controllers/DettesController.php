<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\Depense;
use App\Models\Dette;
use App\Models\Remboursement;
use App\Models\Revenu;
use App\Services\AlerteService;
use App\Services\BudgetPeriodService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DettesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type', 'emprunt'); // emprunt ou pret
        $statutFiltre = $request->get('statut'); // actif, solde, en_retard ou null

        $query = Dette::where('user_id', $user->id)->where('type', $type);
        if ($statutFiltre) {
            $query->where('statut', $statutFiltre);
        }
        $dettes = $query->orderByRaw("CASE statut WHEN 'en_retard' THEN 1 WHEN 'actif' THEN 2 ELSE 3 END")
            ->orderBy('date_echeance')
            ->orderByDesc('date_operation')
            ->get();

        // Stats globales par type
        $stats = [
            'emprunts' => [
                'total'   => (float) Dette::where('user_id', $user->id)->where('type', 'emprunt')->where('statut', '!=', 'solde')->sum('montant_initial'),
                'count'   => Dette::where('user_id', $user->id)->where('type', 'emprunt')->where('statut', '!=', 'solde')->count(),
                'retard'  => Dette::where('user_id', $user->id)->where('type', 'emprunt')->where('statut', 'en_retard')->count(),
            ],
            'prets' => [
                'total'   => (float) Dette::where('user_id', $user->id)->where('type', 'pret')->where('statut', '!=', 'solde')->sum('montant_initial'),
                'count'   => Dette::where('user_id', $user->id)->where('type', 'pret')->where('statut', '!=', 'solde')->count(),
                'retard'  => Dette::where('user_id', $user->id)->where('type', 'pret')->where('statut', 'en_retard')->count(),
            ],
        ];

        // Soustraire les remboursements pour les vrais "restants"
        $stats['emprunts']['restant'] = (float) Dette::where('user_id', $user->id)->where('type', 'emprunt')->where('statut', '!=', 'solde')->get()->sum('montant_restant');
        $stats['prets']['restant']    = (float) Dette::where('user_id', $user->id)->where('type', 'pret')->where('statut', '!=', 'solde')->get()->sum('montant_restant');

        $periodeCourante = BudgetPeriodService::resolvePeriode($user);
        $periodeLabelCourante = BudgetPeriodService::label(
            $user,
            $periodeCourante['mois'],
            $periodeCourante['annee']
        );

        return view('dettes.index', compact(
            'dettes', 'stats', 'type', 'statutFiltre', 'periodeLabelCourante'
        ));
    }

    public function show(Dette $dette)
    {
        $this->authorize('view', $dette);
        $dette->load('remboursements');
        return view('dettes.show', compact('dette'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'             => ['required', 'in:emprunt,pret'],
            'partie'           => ['required', 'string', 'max:120'],
            'montant_initial'  => ['required', 'numeric', 'min:1'],
            'date_operation'   => ['required', 'date'],
            'date_echeance'    => ['nullable', 'date', 'after_or_equal:date_operation'],
            'interet_pct'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'affecte_budget'   => ['boolean'],
            'note'             => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $user = Auth::user();
            $dette = Dette::create([
                'user_id'         => $user->id,
                'type'            => $data['type'],
                'partie'          => $data['partie'],
                'montant_initial' => $data['montant_initial'],
                'date_operation'  => $data['date_operation'],
                'date_echeance'   => $data['date_echeance'] ?? null,
                'interet_pct'     => $data['interet_pct'] ?? null,
                'affecte_budget'  => $request->boolean('affecte_budget'),
                'note'            => $data['note'] ?? null,
                'statut'          => 'actif',
            ]);

            if ($dette->affecte_budget) {
                $this->creerOperationBudget($dette, null);
            }
        });

        return redirect()->route('dettes.index', ['type' => $data['type']])
            ->with('success', ucfirst($data['type']) . ' enregistré avec succès.');
    }

    public function update(Request $request, Dette $dette)
    {
        $this->authorize('update', $dette);

        $data = $request->validate([
            'partie'           => ['required', 'string', 'max:120'],
            'montant_initial'  => ['required', 'numeric', 'min:1'],
            'date_operation'   => ['required', 'date'],
            'date_echeance'    => ['nullable', 'date', 'after_or_equal:date_operation'],
            'interet_pct'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'note'             => ['nullable', 'string'],
        ]);

        $dette->update($data);
        $dette->recalculerStatut();

        return back()->with('success', 'Modifications enregistrées.');
    }

    public function destroy(Dette $dette)
    {
        $this->authorize('delete', $dette);
        $type = $dette->type;
        $dette->delete(); // cascade : remboursements supprimés. Revenus/Depenses : SET NULL via FK

        return redirect()->route('dettes.index', ['type' => $type])
            ->with('success', 'Opération supprimée.');
    }

    public function storeRemboursement(Request $request, Dette $dette)
    {
        $this->authorize('update', $dette);

        $data = $request->validate([
            'montant'        => ['required', 'numeric', 'min:1'],
            'date'           => ['required', 'date'],
            'affecte_budget' => ['boolean'],
            'note'           => ['nullable', 'string'],
        ]);

        // Empêcher de payer plus que le restant
        if ($data['montant'] > $dette->montant_restant + 0.01) {
            return back()->withErrors(['montant' => 'Le montant dépasse le restant dû (' . number_format($dette->montant_restant, 0, ',', "\u{00A0}") . ' FCFA).'])->withInput();
        }

        $estSolde = false;
        DB::transaction(function () use ($data, $dette, $request, &$estSolde) {
            $rb = $dette->remboursements()->create([
                'montant'        => $data['montant'],
                'date'           => $data['date'],
                'affecte_budget' => $request->boolean('affecte_budget'),
                'note'           => $data['note'] ?? null,
            ]);

            if ($rb->affecte_budget) {
                $this->creerOperationBudget($dette, $rb);
            }

            $dette->refresh()->recalculerStatut();
            $dette->refresh();
            $estSolde = $dette->statut === 'solde';

            // Notifications in-app (cloche)
            $user = Auth::user();
            if ($estSolde) {
                AlerteService::detteSoldee($user, $dette);
            } else {
                AlerteService::remboursementPartiel($user, $dette, (float) $data['montant']);
            }
        });

        $msg = $estSolde
            ? '🎉 Félicitations, cette opération est entièrement soldée !'
            : 'Paiement enregistré. Reste à payer : ' . number_format((int) $dette->fresh()->montant_restant, 0, ',', "\u{00A0}") . ' FCFA.';

        return back()->with('success', $msg);
    }

    public function destroyRemboursement(Remboursement $remboursement)
    {
        $this->authorize('delete', $remboursement);
        $dette = $remboursement->dette;
        $remboursement->delete(); // cascade côté SET NULL pour revenus/depenses
        $dette->refresh()->recalculerStatut();

        return back()->with('success', 'Paiement supprimé.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Intégration budget Option C : crée auto un revenu ou une dépense
     * selon le type de dette et si c'est l'opération initiale ou un remboursement.
     *
     * Logique :
     *  - Emprunt initial → Revenu (l'argent entre)
     *  - Prêt initial → Dépense (l'argent sort)
     *  - Remboursement sur emprunt → Dépense (je paie)
     *  - Remboursement sur prêt → Revenu (on me paie)
     */
    private function creerOperationBudget(Dette $dette, ?Remboursement $rb): void
    {
        $user = Auth::user();
        $date = $rb ? $rb->date : $dette->date_operation;
        $montant = $rb ? $rb->montant : $dette->montant_initial;

        $periode = BudgetPeriodService::resolvePeriodePourDate($user, Carbon::parse($date));
        $budget = Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $periode['mois'], 'annee' => $periode['annee']],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );

        $estRevenu = ($dette->type === 'emprunt' && !$rb) || ($dette->type === 'pret' && $rb);

        if ($estRevenu) {
            $description = $rb
                ? "Remboursement reçu — {$dette->partie}"
                : "Emprunt — {$dette->partie}";

            // Sans quota 30/70 : 100 % dépensable (montant_dispo), pas de réserve
            Revenu::withoutEvents(function () use ($budget, $dette, $rb, $montant, $date, $description) {
                Revenu::create([
                    'budget_id'        => $budget->id,
                    'dette_id'         => $dette->id,
                    'remboursement_id' => $rb?->id,
                    'type'             => 'extra',
                    'montant_brut'     => $montant,
                    'montant_quota'    => 0,
                    'montant_dispo'    => $montant,
                    'quota_applique'   => false,
                    'date'             => $date,
                    'description'      => $description,
                ]);
            });
        } else {
            // Dépense : prêt initial ou remboursement d'emprunt
            $nomCat = $rb ? 'Remboursements emprunts' : 'Prêts accordés';
            $categorie = Categorie::firstOrCreate(
                ['user_id' => $user->id, 'nom' => $nomCat],
                [
                    'icone'      => $rb ? 'payments' : 'handshake',
                    'couleur'    => $rb ? '#DC2626' : '#7C3AED',
                    'type'       => 'depense',
                    'is_default' => false,
                    'is_active'  => true,
                    'ordre'      => 999,
                ]
            );

            $note = $rb
                ? "Remboursement à {$dette->partie}"
                : "Prêt à {$dette->partie}";

            Depense::create([
                'budget_id'        => $budget->id,
                'dette_id'         => $dette->id,
                'remboursement_id' => $rb?->id,
                'categorie_id'     => $categorie->id,
                'montant'          => $montant,
                'date'             => $date,
                'note'             => $note,
                'imprevue'         => false,
            ]);
        }
    }
}
