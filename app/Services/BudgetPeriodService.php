<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class BudgetPeriodService
{
    /** Jour 1 = mois calendaire classique. */
    public static function jourDebut(User $user): int
    {
        $j = (int) ($user->jour_debut_mois ?? 1);

        return max(1, min(28, $j));
    }

    public static function usesCustomPeriod(User $user): bool
    {
        return self::jourDebut($user) > 1;
    }

    /**
     * @return array{mois: int, annee: int}
     */
    public static function resolvePeriode(User $user, ?Carbon $date = null): array
    {
        $date = ($date ?? now())->copy();
        $d    = self::jourDebut($user);

        if ($d <= 1) {
            return ['mois' => $date->month, 'annee' => $date->year];
        }

        if ($date->day >= $d) {
            return ['mois' => $date->month, 'annee' => $date->year];
        }

        $prev = $date->copy()->subMonthNoOverflow();

        return ['mois' => $prev->month, 'annee' => $prev->year];
    }

    /**
     * @return array{mois: int, annee: int}
     */
    public static function periodePrecedente(int $mois, int $annee): array
    {
        $ref  = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        $prev = $ref->copy()->subMonthNoOverflow();

        return ['mois' => $prev->month, 'annee' => $prev->year];
    }

    /**
     * @return array{mois: int, annee: int}
     */
    public static function periodeSuivante(int $mois, int $annee): array
    {
        $ref  = Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        $next = $ref->copy()->addMonthNoOverflow();

        return ['mois' => $next->month, 'annee' => $next->year];
    }

    /**
     * @return array{mois: int, annee: int}
     */
    public static function resolvePeriodePourDate(User $user, Carbon $date): array
    {
        return self::resolvePeriode($user, $date);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public static function bornes(User $user, int $mois, int $annee): array
    {
        $d = self::jourDebut($user);

        if ($d <= 1) {
            $debut = Carbon::createFromDate($annee, $mois, 1)->startOfDay();
            $fin   = $debut->copy()->endOfMonth()->endOfDay();

            return [$debut, $fin];
        }

        $daysInStartMonth = Carbon::createFromDate($annee, $mois, 1)->daysInMonth;
        $debut = Carbon::createFromDate($annee, $mois, min($d, $daysInStartMonth))->startOfDay();
        $fin   = $debut->copy()->addMonth()->subDay()->endOfDay();

        return [$debut, $fin];
    }

    public static function label(User $user, int $mois, int $annee): string
    {
        if (! self::usesCustomPeriod($user)) {
            return Carbon::createFromDate($annee, $mois, 1)->translatedFormat('F Y');
        }

        [$debut, $fin] = self::bornes($user, $mois, $annee);

        return $debut->translatedFormat('d M').' – '.$fin->translatedFormat('d M Y');
    }

    public static function estPeriodeCourante(User $user, int $mois, int $annee): bool
    {
        $c = self::resolvePeriode($user);

        return $c['mois'] === $mois && $c['annee'] === $annee;
    }

    public static function estPeriodeFuture(User $user, int $mois, int $annee): bool
    {
        $c = self::resolvePeriode($user);

        if ($annee > $c['annee']) {
            return true;
        }

        return $annee === $c['annee'] && $mois > $c['mois'];
    }

    public static function estPeriodePassee(User $user, int $mois, int $annee): bool
    {
        if (self::estPeriodeCourante($user, $mois, $annee)) {
            return false;
        }

        return ! self::estPeriodeFuture($user, $mois, $annee);
    }

    public static function joursEcoules(User $user, int $mois, int $annee, ?Carbon $ref = null): int
    {
        [$debut, $fin] = self::bornes($user, $mois, $annee);
        $ref = ($ref ?? now())->copy();

        if ($ref->lt($debut)) {
            return 0;
        }

        if ($ref->gt($fin)) {
            return (int) $debut->diffInDays($fin) + 1;
        }

        return (int) $debut->diffInDays($ref) + 1;
    }

    public static function joursTotal(User $user, int $mois, int $annee): int
    {
        [$debut, $fin] = self::bornes($user, $mois, $annee);

        return (int) $debut->diffInDays($fin) + 1;
    }

    public static function dateDansPeriode(User $user, Carbon $date, int $mois, int $annee): bool
    {
        [$debut, $fin] = self::bornes($user, $mois, $annee);

        return $date->betweenIncluded($debut, $fin);
    }
}
