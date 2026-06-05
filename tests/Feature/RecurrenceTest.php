<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\Recurrence;
use App\Models\User;
use App\Services\RecurrenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurrenceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Categorie $categorie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true, 'quota_taux' => 30]);
        $this->categorie = Categorie::factory()->create(['user_id' => $this->user->id, 'nom' => 'Loyer']);
        $this->actingAs($this->user);
    }

    public function test_recurrences_page_loads(): void
    {
        $this->get(route('profil.recurrences.index'))->assertStatus(200);
    }

    public function test_can_create_depense_recurrence(): void
    {
        $this->post(route('profil.recurrences.store'), [
            'type'         => 'depense',
            'categorie_id' => $this->categorie->id,
            'montant'      => 150000,
            'jour_du_mois' => 1,
            'libelle'      => 'Loyer mensuel',
        ])->assertRedirect();

        $this->assertDatabaseHas('recurrences', [
            'user_id' => $this->user->id,
            'type'    => 'depense',
            'montant' => 150000,
        ]);
    }

    public function test_service_generates_depense_for_month(): void
    {
        Budget::factory()->create([
            'user_id' => $this->user->id,
            'mois'    => now()->month,
            'annee'   => now()->year,
        ]);

        $rec = Recurrence::create([
            'user_id'      => $this->user->id,
            'type'         => 'depense',
            'categorie_id' => $this->categorie->id,
            'montant'      => 50000,
            'jour_du_mois' => 1,
            'libelle'      => 'Test',
            'is_active'    => true,
        ]);

        $ok = RecurrenceService::genererUne($this->user, $rec, now());
        $this->assertTrue($ok);
        $this->assertDatabaseHas('depenses', ['montant' => 50000]);
        $rec->refresh();
        $this->assertEquals(now()->format('Y-m'), $rec->last_generated_ym);
    }

    public function test_generer_command_runs(): void
    {
        $this->artisan('ecopoche:generer-recurrences')->assertSuccessful();
    }
}
