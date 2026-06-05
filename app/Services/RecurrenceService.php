<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Depense;
use App\Models\QuotaLog;
use App\Models\Recurrence;
use App\Models\Revenu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RecurrenceService
{
    public static function genererPourUtilisateur(User $user, ?Carbon $date = null): int
    {
        $date = ($date ?? now())->copy();
        $ym   = $date->format('Y-m');
        $count = 0;

        $recurrences = Recurrence::where('user_id', $user->id)
            ->where('is_active', true)
            ->where(function ($q) use ($ym) {
                $q->whereNull('last_generated_ym')->orWhere('last_generated_ym', '!=', $ym);
            })
            ->where('jour_du_mois', '<=', $date->day)
            ->get();

        foreach ($recurrences as $recurrence) {
            if (self::genererUne($user, $recurrence, $date)) {
                $count++;
            }
        }

        return $count;
    }

    public static function genererUne(User $user, Recurrence $recurrence, Carbon $date): bool
    {
        $ym = $date->format('Y-m');

        if ($recurrence->last_generated_ym === $ym) {
            return false;
        }

        if ($date->day < $recurrence->jour_du_mois) {
            return false;
        }

        $jour = min($recurrence->jour_du_mois, $date->daysInMonth);
        $dateOp = Carbon::createFromDate($date->year, $date->month, $jour);

        $budget = Budget::firstOrCreate(
            ['user_id' => $user->id, 'mois' => $date->month, 'annee' => $date->year],
            ['salaire_fixe' => 0, 'solde_charges' => 0, 'epargne_objectif' => 0]
        );

        if ($recurrence->isDepense()) {
            if (! $recurrence->categorie_id) {
                return false;
            }

            $categorie = $user->categories()->where('id', $recurrence->categorie_id)->first();
            if (! $categorie) {
                return false;
            }

            Depense::create([
                'budget_id'    => $budget->id,
                'categorie_id' => $categorie->id,
                'montant'      => $recurrence->montant,
                'date'         => $dateOp,
                'note'         => $recurrence->libelle ?: 'Récurrent',
                'imprevue'     => (bool) $recurrence->imprevue,
            ]);
        } else {
            Auth::login($user);
            $revenu = $budget->revenus()->create([
                'type'        => $recurrence->revenu_type ?? 'extra',
                'montant_brut'=> $recurrence->montant,
                'date'        => $dateOp,
                'description' => $recurrence->libelle ?: 'Récurrent',
            ]);

            if ($revenu->quota_applique && $revenu->quotaLog === null) {
                QuotaLog::create([
                    'revenu_id'     => $revenu->id,
                    'montant_brut'  => $revenu->montant_brut,
                    'montant_quota' => $revenu->montant_quota,
                    'montant_dispo' => $revenu->montant_dispo,
                    'taux'          => (int) ($user->quota_taux ?? 30),
                    'debloquer'     => 0,
                ]);
            }
        }

        $recurrence->update(['last_generated_ym' => $ym]);
        AlerteService::analyserBudget($user, $budget->fresh());

        return true;
    }
}
