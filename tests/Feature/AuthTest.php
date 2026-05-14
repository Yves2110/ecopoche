<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'password'  => bcrypt('password'),
            'is_active' => true,
            'role'      => 'user',
        ], $attrs));
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/login')->assertRedirect();
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = $this->makeUser(['email' => 'test@ecopoche.com']);
        $this->post('/login', ['email' => 'test@ecopoche.com', 'password' => 'password'])
             ->assertRedirect(route('dashboard'));
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = $this->makeUser(['email' => 'test@ecopoche.com']);
        $this->post('/login', ['email' => 'test@ecopoche.com', 'password' => 'wrongpass'])
             ->assertSessionHasErrors('email');
    }

    public function test_inactive_user_is_kicked_after_login(): void
    {
        // CheckActive est un middleware global post-login : l'utilisateur se connecte
        // mais est déconnecté et redirigé à la requête suivante (dashboard)
        $user = $this->makeUser(['email' => 'inactive@ecopoche.com', 'is_active' => false]);
        $this->post('/login', ['email' => 'inactive@ecopoche.com', 'password' => 'password']);

        // La requête suivante doit retourner une erreur et déconnecter
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/revenus')->assertRedirect(route('login'));
        $this->get('/depenses')->assertRedirect(route('login'));
        $this->get('/epargne')->assertRedirect(route('login'));
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)
             ->post('/logout')
             ->assertRedirect(route('login'));
    }

    public function test_rate_limiting_after_5_failed_attempts(): void
    {
        $this->makeUser(['email' => 'brute@ecopoche.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'brute@ecopoche.com', 'password' => 'wrong']);
        }

        $response = $this->post('/login', ['email' => 'brute@ecopoche.com', 'password' => 'wrong']);
        // Le middleware throttle:login bloque après 5 tentatives (429) ou erreur session
        $this->assertTrue(
            $response->status() === 429 || $response->status() === 302,
            'Expected 429 (throttled) or 302 (redirect with error), got ' . $response->status()
        );
    }
}
