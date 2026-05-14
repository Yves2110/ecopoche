<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        /** @var User $user */
        $user = Auth::user();
        return view('profil.index', compact('user'));
    }

    public function updateInfos(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
        ]);

        $user->update($data);
        return back()->with('success_infos', 'Informations mises à jour.');
    }

    public function updateMotDePasse(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success_mdp', 'Mot de passe modifié avec succès.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = $request->validate([
            'quota_taux'           => ['required', 'integer', 'min:0', 'max:100'],
            'devise'               => ['required', 'string', 'max:10'],
            'notifs_email'         => ['nullable', 'boolean'],
            'seuil_attention'      => ['required', 'integer', 'min:10', 'max:99'],
            'seuil_critique'       => ['required', 'integer', 'min:10', 'max:100'],
            'seuil_plafond_cat'    => ['required', 'integer', 'min:10', 'max:100'],
            'objectif_epargne_pct' => ['required', 'integer', 'min:0', 'max:80'],
            'jour_bilan_email'     => ['required', 'integer', 'min:1', 'max:28'],
            'mode_discret'         => ['nullable', 'boolean'],
        ]);

        // seuil_critique doit être > seuil_attention
        if ($data['seuil_critique'] <= $data['seuil_attention']) {
            return back()->withErrors([
                'seuil_critique' => 'Le seuil critique doit être supérieur au seuil d\'attention.'
            ])->withInput();
        }

        $user->update([
            'quota_taux'           => $data['quota_taux'],
            'devise'               => $data['devise'],
            'notifs_email'         => !empty($data['notifs_email']),
            'seuil_attention'      => $data['seuil_attention'],
            'seuil_critique'       => $data['seuil_critique'],
            'seuil_plafond_cat'    => $data['seuil_plafond_cat'],
            'objectif_epargne_pct' => $data['objectif_epargne_pct'],
            'jour_bilan_email'     => $data['jour_bilan_email'],
            'mode_discret'         => !empty($data['mode_discret']),
        ]);

        return back()->with('success_prefs', 'Préférences enregistrées avec succès.');
    }
}
