<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
        $this->actingAs($this->user);
    }

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $this->get(route('dashboard'))->assertStatus(200);
    }

    public function test_dashboard_creates_budget_if_missing(): void
    {
        $this->assertDatabaseMissing('budgets', [
            'user_id' => $this->user->id,
            'mois'    => now()->month,
            'annee'   => now()->year,
        ]);

        $this->get(route('dashboard'))->assertStatus(200);

        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'mois'    => now()->month,
            'annee'   => now()->year,
        ]);
    }

    public function test_dashboard_accepts_mois_annee_params(): void
    {
        $this->get(route('dashboard', ['mois' => 1, 'annee' => 2025]))->assertStatus(200);
    }

    public function test_dashboard_blocks_future_month(): void
    {
        $futureMonth = now()->addMonth()->month;
        $futureYear  = now()->addMonth()->year;

        $response = $this->get(route('dashboard', ['mois' => $futureMonth, 'annee' => $futureYear]));
        $response->assertStatus(200);

        // Le contrôleur doit rediriger sur le mois courant — budget créé pour now(), pas futur
        $this->assertDatabaseHas('budgets', [
            'user_id' => $this->user->id,
            'mois'    => now()->month,
            'annee'   => now()->year,
        ]);
    }

    public function test_rapports_page_loads(): void
    {
        $this->get(route('rapports.index'))->assertStatus(200);
    }

    public function test_rapports_csv_export_returns_csv(): void
    {
        $this->get(route('rapports.export.csv', ['mois' => now()->month, 'annee' => now()->year]))
             ->assertStatus(200)
             ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_rapports_pdf_export_returns_pdf(): void
    {
        $this->get(route('rapports.export.pdf', ['mois' => now()->month, 'annee' => now()->year]))
             ->assertStatus(200)
             ->assertHeader('Content-Type', 'application/pdf');
    }
}
