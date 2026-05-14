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
    private function logAction(User $cible, string $action, string $description, array $meta = []): void
    {
        ActivityLog::create([
            'user_id'     => $cible->id,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'meta'        => array_merge($meta, ['par' => Auth::id()]),
        ]);
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $search = $request->get('q');
        $statut = $request->get('statut', 'tous');

        $query = User::withCount(['budgets', 'alertes'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
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
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role'  => ['required', 'in:admin,user'],
        ]);

        $mdpProvisoire = Str::random(10);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'role'       => $data['role'],
            'password'   => Hash::make($mdpProvisoire),
            'is_active'  => true,
            'created_by' => Auth::id(),
        ]);

        try {
            Mail::to($user->email)->send(new CompteProvisoire($user, $mdpProvisoire));
        } catch (\Throwable) {}

        $this->logAction($user, 'compte_cree', "Compte créé par l'administrateur.", ['role' => $data['role']]);

        return back()->with('success', "Compte de {$user->name} créé. Identifiants envoyés par email.");
    }

    public function toggleActif(User $user)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);
        if ($user->id === Auth::id()) return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');

        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'compte_reactive' : 'compte_suspendu';
        $label  = $user->is_active ? 'réactivé' : 'suspendu';
        $this->logAction($user, $action, "Compte {$label} par l'administrateur.");

        return back()->with('success', "Compte de {$user->name} {$label}.");
    }

    public function impersonner(User $user)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);
        if ($user->id === Auth::id()) return back();

        $this->logAction($user, 'impersonnification', 'Accès impersonné par le super admin.');

        session(['impersonnation_id' => Auth::id()]);
        Auth::login($user);

        return redirect()->route('dashboard')->with('info', "Vous naviguez en tant que {$user->name}.");
    }

    public function stopImpersonner()
    {
        $adminId = session('impersonnation_id');
        if (!$adminId) return redirect()->route('dashboard');

        $admin = User::find($adminId);
        if (!$admin) return redirect()->route('dashboard');

        session()->forget('impersonnation_id');
        Auth::login($admin);

        return redirect()->route('admin.index')->with('success', 'Impersonnification terminée.');
    }

    public function editCompte(User $user)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $data = request()->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'role'      => ['required', 'in:super_admin,admin,user'],
            'is_active' => ['boolean'],
        ]);

        $ancienRole = $user->role;
        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ]);

        $this->logAction($user, 'compte_modifie',
            "Compte modifié : rôle {$ancienRole} → {$data['role']}.",
            ['champs' => array_keys($data)]
        );

        return back()->with('success', "Compte de {$user->name} mis à jour.");
    }

    public function logs(User $user)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403);

        $logs = ActivityLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.logs', compact('user', 'logs'));
    }
}
