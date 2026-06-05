<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Recurrence;
use App\Services\RecurrenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecurrenceController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $categories = Categorie::where('user_id', $user->id)->where('is_active', true)->orderBy('ordre')->get();

        $recurrences = Recurrence::where('user_id', $user->id)
            ->with('categorie')
            ->orderBy('type')
            ->orderBy('jour_du_mois')
            ->get();

        return view('profil.recurrences', compact('categories', 'recurrences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'         => ['required', 'in:depense,revenu'],
            'categorie_id' => [
                'nullable',
                Rule::requiredIf($request->input('type') === 'depense'),
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', Auth::id())),
            ],
            'revenu_type'  => ['nullable', 'required_if:type,revenu', 'in:bonus,extra'],
            'montant'      => ['required', 'integer', 'min:1'],
            'jour_du_mois' => ['required', 'integer', 'min:1', 'max:28'],
            'libelle'      => ['nullable', 'string', 'max:255'],
            'imprevue'     => ['boolean'],
        ]);

        Recurrence::create([
            'user_id'       => Auth::id(),
            'type'          => $data['type'],
            'categorie_id'  => $data['type'] === 'depense' ? $data['categorie_id'] : null,
            'revenu_type'   => $data['type'] === 'revenu' ? ($data['revenu_type'] ?? 'extra') : null,
            'montant'       => $data['montant'],
            'jour_du_mois'  => $data['jour_du_mois'],
            'libelle'       => $data['libelle'] ?? null,
            'imprevue'      => $request->boolean('imprevue'),
            'is_active'     => true,
        ]);

        return back()->with('success_rec', 'Récurrence enregistrée.');
    }

    public function toggle(Recurrence $recurrence): RedirectResponse
    {
        $this->authorize('update', $recurrence);
        $recurrence->update(['is_active' => ! $recurrence->is_active]);

        return back()->with('success_rec', $recurrence->is_active ? 'Récurrence activée.' : 'Récurrence désactivée.');
    }

    public function genererMaintenant(Recurrence $recurrence): RedirectResponse
    {
        $this->authorize('update', $recurrence);
        $user = Auth::user();

        $recurrence->update(['last_generated_ym' => null]);
        $ok = RecurrenceService::genererUne($user, $recurrence->fresh(), now());

        return back()->with(
            $ok ? 'success_rec' : 'error_rec',
            $ok ? 'Opération générée pour ce mois.' : 'Impossible de générer (déjà fait ce mois ou jour non atteint).'
        );
    }

    public function destroy(Recurrence $recurrence): RedirectResponse
    {
        $this->authorize('delete', $recurrence);
        $recurrence->delete();

        return back()->with('success_rec', 'Récurrence supprimée.');
    }
}
