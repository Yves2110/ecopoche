<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_super_admin_cannot_access_admin(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->actingAs($user)->get(route('admin.index'))->assertStatus(403);
    }

    public function test_super_admin_can_access_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.index'))->assertStatus(200);
    }

    public function test_created_account_must_change_password(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($admin)->post(route('admin.comptes.store'), [
            'prenom' => 'Jean',
            'nom'    => 'Test',
            'email'  => 'jean.test@ecopoche.com',
            'role'   => 'user',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'                => 'jean.test@ecopoche.com',
            'must_change_password' => true,
        ]);
    }

    public function test_cannot_impersonate_super_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $other = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.comptes.impersonner', $other))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_impersonation_and_return(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $user  = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.comptes.impersonner', $user));
        $this->assertAuthenticatedAs($user);

        $this->post(route('admin.stop_impersonner'));
        $this->assertAuthenticatedAs($admin);
    }
}
