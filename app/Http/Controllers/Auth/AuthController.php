<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Services\AlerteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Compte suspendu : déconnecter immédiatement
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => 'Identifiants incorrects ou compte inactif.',
                ]);
            }

            $budget = Budget::firstOrCreate(
                ['user_id' => $user->id, 'mois' => now()->month, 'annee' => now()->year],
                ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
            );
            AlerteService::analyserBudget($user, $budget);

            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => 'connexion',
                'description' => 'Connexion réussie.',
                'ip_address'  => $request->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        // Message générique : ne pas révéler si l'email existe ou non
        throw ValidationException::withMessages([
            'email' => 'Identifiants incorrects ou compte inactif.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'deconnexion',
                'description' => 'Déconnexion du compte.',
                'ip_address'  => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
