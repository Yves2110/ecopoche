<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\ObjectifEpargne;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpargneTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Budget $budget;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
        $this->budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'mois'    => now()->month,
            'annee'   => now()->year,
        ]);
        $this->actingAs($this->user);
    }

    public function test_epargne_page_loads(): void
    {
        $this->get(route('epargne.index'))->assertStatus(200);
    }

    public function test_suivi_mensuel_can_be_updated(): void
    {
        $this->post(route('epargne.mensuel.update', $this->budget), [
            'objectif' => 50000,
            'reel'     => 35000,
        ])->assertRedirect();

        $this->assertDatabaseHas('epargnes', [
            'budget_id' => $this->budget->id,
            'objectif'  => 50000,
            'reel'      => 35000,
            'deficit'   => 15000,
        ]);
    }

    public function test_deficit_is_zero_when_reel_exceeds_objectif(): void
    {
        $this->post(route('epargne.mensuel.update', $this->budget), [
            'objectif' => 30000,
            'reel'     => 40000,
        ])->assertRedirect();

        $this->assertDatabaseHas('epargnes', [
            'budget_id' => $this->budget->id,
            'deficit'   => 0,
        ]);
    }

    public function test_objectif_can_be_created(): void
    {
        $this->post(route('epargne.objectifs.store'), [
            'nom'           => 'Voyage',
            'montant_cible' => 500000,
            'date_debut'    => now()->format('Y-m-d'),
            'icone'         => 'flight',
            'couleur'       => '#006c49',
        ])->assertRedirect();

        $this->assertDatabaseHas('objectifs_epargne', [
            'user_id'       => $this->user->id,
            'nom'           => 'Voyage',
            'montant_cible' => 500000,
        ]);
    }

    public function test_objectif_requires_nom_and_montant(): void
    {
        $this->post(route('epargne.objectifs.store'), [])
             ->assertSessionHasErrors(['nom', 'montant_cible']);
    }

    public function test_versement_increments_montant_actuel(): void
    {
        $objectif = ObjectifEpargne::factory()->create([
            'user_id'       => $this->user->id,
            'montant_cible' => 200000,
            'montant_actuel'=> 0,
            'atteint'       => false,
        ]);

        $this->post(route('epargne.objectifs.verser', $objectif), ['montant' => 50000])
             ->assertRedirect();

        $this->assertDatabaseHas('objectifs_epargne', [
            'id'             => $objectif->id,
            'montant_actuel' => 50000,
        ]);
    }

    public function test_versement_marks_atteint_when_cible_reached(): void
    {
        $objectif = ObjectifEpargne::factory()->create([
            'user_id'        => $this->user->id,
            'montant_cible'  => 100000,
            'montant_actuel' => 90000,
            'atteint'        => false,
        ]);

        $this->post(route('epargne.objectifs.verser', $objectif), ['montant' => 10000])
             ->assertRedirect();

        $this->assertDatabaseHas('objectifs_epargne', [
            'id'      => $objectif->id,
            'atteint' => 1,
        ]);
    }

    public function test_objectif_can_be_deleted(): void
    {
        $objectif = ObjectifEpargne::factory()->create([
            'user_id'       => $this->user->id,
            'montant_cible' => 100000,
        ]);

        $this->delete(route('epargne.objectifs.destroy', $objectif))->assertRedirect();
        $this->assertDatabaseMissing('objectifs_epargne', ['id' => $objectif->id]);
    }

    public function test_other_user_cannot_delete_objectif(): void
    {
        $other = User::factory()->create(['is_active' => true]);
        $objectif = ObjectifEpargne::factory()->create([
            'user_id'       => $this->user->id,
            'montant_cible' => 100000,
        ]);

        $this->actingAs($other)
             ->delete(route('epargne.objectifs.destroy', $objectif))
             ->assertStatus(403);
    }
}
