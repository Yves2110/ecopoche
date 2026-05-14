<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Budget $budget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'is_active'  => true,
            'quota_taux' => 30,
        ]);
        $this->budget = Budget::factory()->create([
            'user_id'    => $this->user->id,
            'mois'       => now()->month,
            'annee'      => now()->year,
            'salaire_fixe' => 0,
        ]);
        $this->actingAs($this->user);
    }

    public function test_revenus_page_loads(): void
    {
        $this->get(route('revenus.index'))->assertStatus(200);
    }

    public function test_salaire_fixe_can_be_updated(): void
    {
        $this->post(route('revenus.salaire.update', $this->budget), ['salaire_fixe' => 500000])
             ->assertRedirect();
        $this->assertDatabaseHas('budgets', ['id' => $this->budget->id, 'salaire_fixe' => 500000]);
    }

    public function test_bonus_applies_quota_30_percent(): void
    {
        $this->post(route('revenus.store'), [
            'mois'          => now()->month,
            'annee'         => now()->year,
            'type'          => 'bonus',
            'montant_brut'  => 100000,
            'description'   => 'Prime test',
            'date'          => now()->format('Y-m-d'),
        ])->assertRedirect();

        $revenu = $this->budget->revenus()->first();
        $this->assertNotNull($revenu);
        $this->assertTrue((bool) $revenu->quota_applique);
        $this->assertEquals(30000, (int) $revenu->montant_quota);
        $this->assertEquals(70000, (int) $revenu->montant_dispo);
    }

    public function test_bonus_applies_custom_quota_taux(): void
    {
        $this->user->update(['quota_taux' => 20]);

        $this->post(route('revenus.store'), [
            'mois'         => now()->month,
            'annee'        => now()->year,
            'type'         => 'bonus',
            'montant_brut' => 100000,
            'description'  => 'Prime taux 20%',
            'date'         => now()->format('Y-m-d'),
        ])->assertRedirect();

        $revenu = $this->budget->revenus()->latest()->first();
        $this->assertEquals(20000, (int) $revenu->montant_quota);
        $this->assertEquals(80000, (int) $revenu->montant_dispo);
    }

    public function test_salaire_type_has_no_quota(): void
    {
        // Le salaire fixe passe par updateSalaire, pas storeRevenu (qui n'accepte que bonus/extra).
        // On vérifie que le modèle Revenu::booted() n'applique pas de quota sur un type non-bonus.
        $revenu = $this->budget->revenus()->create([
            'type'           => 'salaire',
            'montant_brut'   => 300000,
            'montant_quota'  => 0,
            'montant_dispo'  => 300000,
            'quota_applique' => false,
            'date'           => now(),
        ]);

        $this->assertFalse((bool) $revenu->quota_applique);
        $this->assertEquals(0, (int) $revenu->montant_quota);
        $this->assertEquals(300000, (int) $revenu->montant_dispo);
    }

    public function test_revenu_requires_montant_brut(): void
    {
        $this->post(route('revenus.store'), [
            'type' => 'bonus',
            'date' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('montant_brut');
    }

    public function test_revenu_can_be_deleted(): void
    {
        $revenu = $this->budget->revenus()->create([
            'type' => 'salaire', 'montant_brut' => 200000,
            'montant_quota' => 0, 'montant_dispo' => 200000,
            'quota_applique' => false, 'date' => now(),
        ]);

        $this->delete(route('revenus.destroy', $revenu))->assertRedirect();
        $this->assertDatabaseMissing('revenus', ['id' => $revenu->id]);
    }

    public function test_other_user_cannot_delete_revenu(): void
    {
        $other = User::factory()->create(['is_active' => true]);
        $revenu = $this->budget->revenus()->create([
            'type' => 'salaire', 'montant_brut' => 200000,
            'montant_quota' => 0, 'montant_dispo' => 200000,
            'quota_applique' => false, 'date' => now(),
        ]);

        $this->actingAs($other)
             ->delete(route('revenus.destroy', $revenu))
             ->assertStatus(403);
    }
}
