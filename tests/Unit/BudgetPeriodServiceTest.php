<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\BudgetPeriodService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetPeriodServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendrier_classique_utilise_le_mois_courant(): void
    {
        $user = User::factory()->create(['jour_debut_mois' => 1]);
        Carbon::setTestNow('2026-06-15');

        $p = BudgetPeriodService::resolvePeriode($user);

        $this->assertSame(6, $p['mois']);
        $this->assertSame(2026, $p['annee']);
    }

    public function test_periode_personnalisee_25_avant_le_25_appartient_au_mois_precedent(): void
    {
        $user = User::factory()->create(['jour_debut_mois' => 25]);
        Carbon::setTestNow('2026-06-10');

        $p = BudgetPeriodService::resolvePeriode($user);

        $this->assertSame(5, $p['mois']);
        $this->assertSame(2026, $p['annee']);
    }

    public function test_periode_personnalisee_25_apres_le_25_appartient_au_mois_courant(): void
    {
        $user = User::factory()->create(['jour_debut_mois' => 25]);
        Carbon::setTestNow('2026-06-26');

        $p = BudgetPeriodService::resolvePeriode($user);

        $this->assertSame(6, $p['mois']);
        $this->assertSame(2026, $p['annee']);
    }

    public function test_bornes_periode_25_au_24(): void
    {
        $user = User::factory()->create(['jour_debut_mois' => 25]);
        [$debut, $fin] = BudgetPeriodService::bornes($user, 6, 2026);

        $this->assertSame('2026-06-25', $debut->format('Y-m-d'));
        $this->assertSame('2026-07-24', $fin->format('Y-m-d'));
    }

    public function test_periode_suivante_apres_mai(): void
    {
        $next = BudgetPeriodService::periodeSuivante(5, 2026);
        $this->assertSame(6, $next['mois']);
        $this->assertSame(2026, $next['annee']);
    }

    public function test_est_periode_passee(): void
    {
        $user = User::factory()->create(['jour_debut_mois' => 1]);
        Carbon::setTestNow('2026-06-15');
        $this->assertTrue(BudgetPeriodService::estPeriodePassee($user, 5, 2026));
        $this->assertFalse(BudgetPeriodService::estPeriodePassee($user, 6, 2026));
    }

    public function test_cloture_masque_les_alertes_budget_anciennes(): void
    {
        $user = User::factory()->create(['jour_debut_mois' => 1]);
        Carbon::setTestNow('2026-06-15');

        $user->alertes()->create([
            'type' => 'attention',
            'gravite' => 'warning',
            'message' => 'Ancienne',
            'meta' => ['mois' => 5, 'annee' => 2026],
        ]);

        $n = \App\Services\AlerteService::cloturerAlertesPeriodesExpirees($user);

        $this->assertSame(1, $n);
        $this->assertSame(0, \App\Services\AlerteService::compterNonLues($user->fresh()));
    }
}
