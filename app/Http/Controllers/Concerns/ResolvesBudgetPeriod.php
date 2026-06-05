<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Services\BudgetPeriodService;
use Illuminate\Http\Request;

trait ResolvesBudgetPeriod
{
    /**
     * @return array{mois: int, annee: int}
     */
    protected function resolveMoisAnnee(Request $request, User $user, bool $allowFuture = false): array
    {
        $courant = BudgetPeriodService::resolvePeriode($user);
        $mois    = (int) $request->get('mois', $courant['mois']);
        $annee   = (int) $request->get('annee', $courant['annee']);

        if (! $allowFuture && BudgetPeriodService::estPeriodeFuture($user, $mois, $annee)) {
            return $courant;
        }

        return ['mois' => $mois, 'annee' => $annee];
    }
}
