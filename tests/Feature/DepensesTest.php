<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepensesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Budget $budget;
    private Categorie $categorie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
        $this->budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'mois'    => now()->month,
            'annee'   => now()->year,
        ]);
        $this->categorie = Categorie::factory()->create([
            'user_id'  => $this->user->id,
            'nom'      => 'Alimentation',
            'couleur'  => '#22c55e',
            'plafond_mensuel' => 100000,
        ]);
        $this->actingAs($this->user);
    }

    public function test_depenses_page_loads(): void
    {
        $this->get(route('depenses.index'))->assertStatus(200);
    }

    public function test_can_create_depense(): void
    {
        $this->post(route('depenses.store'), [
            'mois'         => now()->month,
            'annee'        => now()->year,
            'categorie_id' => $this->categorie->id,
            'montant'      => 25000,
            'date'         => now()->format('Y-m-d'),
            'note'         => 'Supermarché',
            'imprevue'     => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('depenses', [
            'budget_id'    => $this->budget->id,
            'categorie_id' => $this->categorie->id,
            'montant'      => 25000,
        ]);
    }

    public function test_depense_requires_montant_and_categorie(): void
    {
        $this->post(route('depenses.store'), [
            'date' => now()->format('Y-m-d'),
        ])->assertSessionHasErrors(['montant', 'categorie_id']);
    }

    public function test_depense_montant_must_be_positive(): void
    {
        $this->post(route('depenses.store'), [
            'categorie_id' => $this->categorie->id,
            'montant'      => -500,
            'date'         => now()->format('Y-m-d'),
        ])->assertSessionHasErrors('montant');
    }

    public function test_depense_can_be_deleted(): void
    {
        $depense = $this->budget->depenses()->create([
            'categorie_id' => $this->categorie->id,
            'montant'      => 10000,
            'date'         => now(),
            'imprevue'     => false,
        ]);

        $this->delete(route('depenses.destroy', $depense))->assertRedirect();
        $this->assertDatabaseMissing('depenses', ['id' => $depense->id]);
    }

    public function test_other_user_cannot_delete_depense(): void
    {
        $other = User::factory()->create(['is_active' => true]);
        $depense = $this->budget->depenses()->create([
            'categorie_id' => $this->categorie->id,
            'montant'      => 10000,
            'date'         => now(),
            'imprevue'     => false,
        ]);

        $this->actingAs($other)
             ->delete(route('depenses.destroy', $depense))
             ->assertStatus(403);
    }

    public function test_categorie_can_be_created(): void
    {
        $this->post(route('depenses.categories.store'), [
            'nom'             => 'Transport',
            'couleur'         => '#3b82f6',
            'plafond_mensuel' => 50000,
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'nom'     => 'Transport',
        ]);
    }
}
