<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Toujours afficher le même message (anti-énumération)
        if (!$user) {
            return back()->with('status', 'Si cet email est enregistré, vous recevrez un lien de réinitialisation.');
        }

        // Supprimer les anciens tokens pour cet email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $request->email]);

        try {
            Mail::send('emails.reset-password', ['url' => $resetUrl, 'user' => $user], function ($m) use ($user) {
                $m->to($user->email)->subject('Réinitialisation de votre mot de passe EcoPoche');
            });
        } catch (\Throwable $e) {
            // En dev : on log l'URL pour pouvoir tester sans SMTP
            logger('Reset URL: ' . $resetUrl);
        }

        return back()->with('status', 'Si cet email est enregistré, vous recevrez un lien de réinitialisation.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Ce lien est invalide ou a expiré.'])->withInput(['email' => $request->email]);
        }

        // Token valide 60 minutes max
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Ce lien a expiré. Veuillez en demander un nouveau.'])->withInput(['email' => $request->email]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Aucun compte trouvé.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Mot de passe réinitialisé. Vous pouvez vous connecter.');
    }
}
