<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Categorie;
use App\Models\User;
use App\Services\AlerteConseilsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlerteConseilsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_categories_line_lists_spending(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create(['user_id' => $user->id]);
        $cat = Categorie::factory()->create(['user_id' => $user->id, 'nom' => 'Transport']);
        $budget->depenses()->create([
            'categorie_id' => $cat->id,
            'montant'      => 50000,
            'date'         => now(),
        ]);

        $line = AlerteConseilsService::topCategoriesLine($budget);
        $this->assertStringContainsString('Transport', $line);
        $this->assertStringContainsString('50', $line);
    }

    public function test_plafond_conseils_include_category_name(): void
    {
        $conseils = AlerteConseilsService::pourType('plafond_depasse', [
            'categorie' => 'Loisirs',
            'depasse'   => 15000,
        ]);

        $this->assertCount(3, $conseils);
        $this->assertStringContainsString('Loisirs', $conseils[0]);
    }
}
