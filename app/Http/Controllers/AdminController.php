<?php

namespace App\Http\Controllers;

use App\Mail\CompteProvisoire;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    private function authUser(): User
    {
        /** @var User */
        return Auth::user();
    }

    private function logAction(User $cible, string $action, string $description, array $meta = []): void
    {
        ActivityLog::create([
            'user_id'     => $cible->id,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'meta'        => array_merge($meta, [
                'par'        => Auth::id(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]),
        ]);
    }

    public function index(Request $request)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);

        $search = $request->get('q');
        $statut = $request->get('statut', 'tous');

        $query = User::withCount(['budgets', 'alertes'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('prenom', 'like', "%{$search}%")
                ->orWhere('nom', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($statut === 'actif') $query->where('is_active', true);
        elseif ($statut === 'inactif') $query->where('is_active', false);

        $users = $query->paginate(20)->withQueryString();

        $stats = [
            'total'        => User::count(),
            'actifs'       => User::where('is_active', true)->count(),
            'admins'       => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'utilisateurs' => User::where('role', 'user')->count(),
            'suspendus'    => User::where('is_active', false)->count(),
        ];

        return view('admin.index', compact('users', 'stats', 'search', 'statut'));
    }

    public function creerCompte(Request $request)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);

        $data = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom'    => ['required', 'string', 'max:100'],
            'email'  => ['required', 'email', 'unique:users,email'],
            'role'   => ['required', 'in:admin,user'],
        ], [
            'prenom.required' => 'Le prénom est obligatoire.',
            'nom.required'    => 'Le nom est obligatoire.',
        ]);

        $mdpProvisoire = Str::random(10);

        $user = User::create([
            'prenom'     => $data['prenom'],
            'nom'        => $data['nom'],
            'name'       => trim($data['prenom'] . ' ' . $data['nom']),
            'email'      => $data['email'],
            'role'       => $data['role'],
            'password'             => Hash::make($mdpProvisoire),
            'must_change_password' => true,
            'is_active'            => true,
            'created_by'           => Auth::id(),
        ]);

        try {
            Mail::to($user->email)->send(new CompteProvisoire($user, $mdpProvisoire));
        } catch (\Throwable) {}

        $this->logAction($user, 'compte_cree', "Compte créé par l'administrateur.", ['role' => $data['role']]);

        return back()->with('success', "Compte de {$user->full_name} créé. Identifiants envoyés par email.");
    }

    public function toggleActif(User $user)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);
        if ($user->id === Auth::id()) return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');

        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'compte_reactive' : 'compte_suspendu';
        $label  = $user->is_active ? 'réactivé' : 'suspendu';
        $this->logAction($user, $action, "Compte {$label} par l'administrateur.");

        return back()->with('success', "Compte de {$user->full_name} {$label}.");
    }

    public function impersonner(User $user)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);
        if ($user->id === Auth::id()) return back();

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Impossible d\'accéder au compte d\'un autre super administrateur.');
        }

        $adminId = Auth::id();
        $this->logAction($user, 'impersonnification', 'Accès impersonné par le super admin.', [
            'admin_id' => $adminId,
        ]);

        session(['impersonnation_id' => $adminId]);
        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')->with('info', "Vous naviguez en tant que {$user->full_name}.");
    }

    public function stopImpersonner()
    {
        $adminId = session('impersonnation_id');
        if (!$adminId) return redirect()->route('dashboard');

        $admin = User::find($adminId);
        if (!$admin) return redirect()->route('dashboard');

        session()->forget('impersonnation_id');
        Auth::login($admin);
        request()->session()->regenerate();

        return redirect()->route('admin.index')->with('success', 'Impersonnification terminée.');
    }

    public function editCompte(User $user)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);

        $data = request()->validate([
            'prenom'                    => ['required', 'string', 'max:100'],
            'nom'                       => ['required', 'string', 'max:100'],
            'email'                     => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'                      => ['required', 'in:super_admin,admin,user'],
            'is_active'                 => ['boolean'],
            'new_password'              => ['nullable', 'string', 'min:8', 'confirmed'],
            'new_password_confirmation' => ['nullable', 'string'],
        ], [
            'prenom.required' => 'Le prénom est obligatoire.',
            'nom.required'    => 'Le nom est obligatoire.',
        ]);

        $ancienRole = $user->role;
        $updates = [
            'prenom'    => $data['prenom'],
            'nom'       => $data['nom'],
            'name'      => trim($data['prenom'] . ' ' . $data['nom']),
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ];

        $mdpChange = false;
        if (!empty($data['new_password'])) {
            $updates['password']             = Hash::make($data['new_password']);
            $updates['must_change_password'] = true;
            $mdpChange = true;
        }

        $user->update($updates);

        $desc = "Compte modifié : rôle {$ancienRole} → {$data['role']}.";
        if ($mdpChange) $desc .= ' Mot de passe redéfini manuellement.';
        $this->logAction($user, 'compte_modifie', $desc, ['champs' => array_keys($data)]);

        $msg = "Compte de {$user->full_name} mis à jour.";
        if ($mdpChange) $msg .= ' Mot de passe modifié.';

        return back()->with('success', $msg);
    }

    public function resetPassword(User $user)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);

        $nouveauMdp = Str::random(10);
        $user->update([
            'password'             => Hash::make($nouveauMdp),
            'must_change_password' => true,
        ]);

        try {
            Mail::to($user->email)->send(new CompteProvisoire($user, $nouveauMdp));
        } catch (\Throwable) {}

        $this->logAction($user, 'mdp_reinitialise', 'Mot de passe réinitialisé par l\'administrateur.');

        return back()->with('success', "Mot de passe de {$user->full_name} réinitialisé : {$nouveauMdp}");
    }

    public function logs(User $user)
    {
        if (!$this->authUser()->isSuperAdmin()) abort(403);

        $logs = ActivityLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.logs', compact('user', 'logs'));
    }
}
