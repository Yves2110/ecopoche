<?php

namespace Tests\Feature;

use App\Models\Depense;
use App\Models\Dette;
use App\Models\Remboursement;
use App\Models\Revenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DettesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user);
    }

    public function test_index_page_loads(): void
    {
        $this->get(route('dettes.index'))->assertStatus(200);
    }

    public function test_can_create_emprunt(): void
    {
        $this->post(route('dettes.store'), [
            'type'            => 'emprunt',
            'partie'          => 'Jean Dupont',
            'montant_initial' => 50000,
            'date_operation'  => now()->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertDatabaseHas('dettes', [
            'user_id'         => $this->user->id,
            'type'            => 'emprunt',
            'partie'          => 'Jean Dupont',
            'montant_initial' => 50000,
            'affecte_budget'  => false,
            'statut'          => 'actif',
        ]);
    }

    public function test_can_create_pret(): void
    {
        $this->post(route('dettes.store'), [
            'type'            => 'pret',
            'partie'          => 'Marie',
            'montant_initial' => 20000,
            'date_operation'  => now()->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertDatabaseHas('dettes', ['type' => 'pret', 'partie' => 'Marie']);
    }

    public function test_emprunt_avec_affecte_budget_cree_revenu(): void
    {
        $this->post(route('dettes.store'), [
            'type'            => 'emprunt',
            'partie'          => 'Banque',
            'montant_initial' => 100000,
            'date_operation'  => now()->format('Y-m-d'),
            'affecte_budget'  => 1,
        ])->assertRedirect();

        $dette = Dette::where('partie', 'Banque')->first();
        $this->assertNotNull($dette);
        $this->assertTrue($dette->affecte_budget);

        // Vérifier qu'un revenu a été créé
        $revenu = Revenu::where('dette_id', $dette->id)->first();
        $this->assertNotNull($revenu);
        $this->assertEquals(100000, (float) $revenu->montant_brut);
        $this->assertEquals(100000, (float) $revenu->montant_dispo);
        $this->assertFalse((bool) $revenu->quota_applique);
    }

    public function test_pret_avec_affecte_budget_cree_depense(): void
    {
        $this->post(route('dettes.store'), [
            'type'            => 'pret',
            'partie'          => 'Ami',
            'montant_initial' => 15000,
            'date_operation'  => now()->format('Y-m-d'),
            'affecte_budget'  => 1,
        ])->assertRedirect();

        $dette = Dette::where('partie', 'Ami')->first();
        $depense = Depense::where('dette_id', $dette->id)->first();

        $this->assertNotNull($depense);
        $this->assertEquals(15000, (float) $depense->montant);
    }

    public function test_remboursement_pret_affecte_budget_compte_dans_revenu_depensable(): void
    {
        $this->user->update(['jour_debut_mois' => 25]);

        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Yam',
            'montant_initial' => 150000,
            'date_operation'  => '2026-05-01',
            'affecte_budget'  => false,
            'statut'          => 'actif',
        ]);

        $this->post(route('dettes.remboursement.store', $dette), [
            'montant'        => 50000,
            'date'           => '2026-05-25',
            'affecte_budget' => 1,
        ])->assertRedirect();

        $revenu = Revenu::where('dette_id', $dette->id)->whereNotNull('remboursement_id')->first();
        $this->assertNotNull($revenu);
        $this->assertEquals(50000, $revenu->montantDepensable());
        $this->assertEquals(0, $revenu->montantReserve());
        $this->assertEquals(50000, Revenu::sumDepensable(collect([$revenu])));
    }

    public function test_remboursement_met_a_jour_statut_solde(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'emprunt',
            'partie'          => 'Test',
            'montant_initial' => 10000,
            'date_operation'  => now(),
            'affecte_budget'  => false,
            'statut'          => 'actif',
        ]);

        $this->post(route('dettes.remboursement.store', $dette), [
            'montant' => 10000,
            'date'    => now()->format('Y-m-d'),
        ])->assertRedirect();

        $dette->refresh();
        $this->assertEquals('solde', $dette->statut);
        $this->assertEquals(0, $dette->montant_restant);

        // Notification "dette soldée" créée
        $this->assertDatabaseHas('alertes', [
            'user_id' => $this->user->id,
            'type'    => 'dette_soldee',
        ]);
    }

    public function test_remboursement_partiel_cree_notification(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Bob',
            'montant_initial' => 20000,
            'date_operation'  => now(),
            'affecte_budget'  => false,
            'statut'          => 'actif',
        ]);

        $this->post(route('dettes.remboursement.store', $dette), [
            'montant' => 5000,
            'date'    => now()->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertDatabaseHas('alertes', [
            'user_id' => $this->user->id,
            'type'    => 'remboursement_partiel',
        ]);
    }

    public function test_remboursement_partiel_garde_statut_actif(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Test',
            'montant_initial' => 10000,
            'date_operation'  => now(),
            'affecte_budget'  => false,
            'statut'          => 'actif',
        ]);

        $this->post(route('dettes.remboursement.store', $dette), [
            'montant' => 3000,
            'date'    => now()->format('Y-m-d'),
        ]);

        $dette->refresh();
        $this->assertEquals('actif', $dette->statut);
        $this->assertEquals(7000, $dette->montant_restant);
        $this->assertEquals(3000, $dette->montant_rembourse);
    }

    public function test_cannot_pay_more_than_restant(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'emprunt',
            'partie'          => 'Test',
            'montant_initial' => 5000,
            'date_operation'  => now(),
            'affecte_budget'  => false,
            'statut'          => 'actif',
        ]);

        $this->post(route('dettes.remboursement.store', $dette), [
            'montant' => 10000,
            'date'    => now()->format('Y-m-d'),
        ])->assertSessionHasErrors(['montant']);

        $this->assertEquals(0, Remboursement::where('dette_id', $dette->id)->count());
    }

    public function test_other_user_cannot_access_dette(): void
    {
        $otherUser = User::factory()->create();
        $dette = Dette::create([
            'user_id'         => $otherUser->id,
            'type'            => 'emprunt',
            'partie'          => 'Test',
            'montant_initial' => 1000,
            'date_operation'  => now(),
            'statut'          => 'actif',
        ]);

        $this->get(route('dettes.show', $dette))->assertStatus(403);
        $this->delete(route('dettes.destroy', $dette))->assertStatus(403);
    }

    public function test_can_delete_dette(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Test',
            'montant_initial' => 5000,
            'date_operation'  => now(),
            'statut'          => 'actif',
        ]);

        $this->delete(route('dettes.destroy', $dette))->assertRedirect();
        $this->assertDatabaseMissing('dettes', ['id' => $dette->id]);
    }

    public function test_supprimer_dette_cascade_remboursements(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Test',
            'montant_initial' => 5000,
            'date_operation'  => now(),
            'statut'          => 'actif',
        ]);
        $dette->remboursements()->create([
            'montant' => 1000,
            'date'    => now(),
        ]);

        $dette->delete();
        $this->assertEquals(0, Remboursement::where('dette_id', $dette->id)->count());
    }

    public function test_analyser_dettes_cree_alerte_echeance_proche(): void
    {
        Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'emprunt',
            'partie'          => 'Bank',
            'montant_initial' => 10000,
            'date_operation'  => now()->subDays(10),
            'date_echeance'   => now()->addDays(7),
            'statut'          => 'actif',
        ]);

        $nb = \App\Services\AlerteService::analyserDettes($this->user);
        $this->assertEquals(1, $nb);
        $this->assertDatabaseHas('alertes', ['user_id' => $this->user->id, 'type' => 'echeance_proche']);
    }

    public function test_analyser_dettes_cree_alerte_echeance_j1(): void
    {
        Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Sophie',
            'montant_initial' => 8000,
            'date_operation'  => now()->subDays(5),
            'date_echeance'   => now()->addDay(),
            'statut'          => 'actif',
        ]);

        $nb = \App\Services\AlerteService::analyserDettes($this->user);
        $this->assertEquals(1, $nb);
        $this->assertDatabaseHas('alertes', ['user_id' => $this->user->id, 'type' => 'echeance_j1']);
    }

    public function test_analyser_dettes_cree_alerte_echeance_depassee_et_maj_statut(): void
    {
        $dette = Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'pret',
            'partie'          => 'Marc',
            'montant_initial' => 5000,
            'date_operation'  => now()->subDays(30),
            'date_echeance'   => now()->subDays(5), // dépassée
            'statut'          => 'actif',
        ]);

        $nb = \App\Services\AlerteService::analyserDettes($this->user);
        $this->assertEquals(1, $nb);
        $this->assertDatabaseHas('alertes', ['user_id' => $this->user->id, 'type' => 'echeance_depassee']);
        $this->assertEquals('en_retard', $dette->fresh()->statut);
    }

    public function test_analyser_dettes_ignore_dettes_soldees(): void
    {
        Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'emprunt',
            'partie'          => 'Old',
            'montant_initial' => 1000,
            'date_operation'  => now()->subDays(30),
            'date_echeance'   => now()->subDays(1),
            'statut'          => 'solde',
        ]);

        $nb = \App\Services\AlerteService::analyserDettes($this->user);
        $this->assertEquals(0, $nb);
    }

    public function test_analyser_dettes_pas_de_doublons(): void
    {
        Dette::create([
            'user_id'         => $this->user->id,
            'type'            => 'emprunt',
            'partie'          => 'Test',
            'montant_initial' => 5000,
            'date_operation'  => now()->subDays(10),
            'date_echeance'   => now()->addDays(2),
            'statut'          => 'actif',
        ]);

        \App\Services\AlerteService::analyserDettes($this->user);
        $nb2 = \App\Services\AlerteService::analyserDettes($this->user);
        $this->assertEquals(0, $nb2); // pas de doublon
    }
}
