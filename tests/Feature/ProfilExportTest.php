<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_donnees_returns_json(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->actingAs($user);

        $response = $this->get(route('profil.export.donnees'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/json; charset=UTF-8');
        $this->assertStringContainsString('ecopoche_donnees', $response->headers->get('content-disposition'));
    }
}
