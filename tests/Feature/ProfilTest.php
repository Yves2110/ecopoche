<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'is_active'  => true,
            'quota_taux' => 30,
            'devise'     => 'FCFA',
        ]);
        $this->actingAs($this->user);
    }

    public function test_profil_page_loads(): void
    {
        $this->get(route('profil.index'))->assertStatus(200);
    }

    public function test_can_update_name_and_email(): void
    {
        $this->put(route('profil.update.infos'), [
            'name'  => 'Nouveau Nom',
            'email' => 'nouveau@ecopoche.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'    => $this->user->id,
            'name'  => 'Nouveau Nom',
            'email' => 'nouveau@ecopoche.com',
        ]);
    }

    public function test_email_must_be_unique(): void
    {
        $other = User::factory()->create(['email' => 'taken@ecopoche.com', 'is_active' => true]);

        $this->put(route('profil.update.infos'), [
            'name'  => 'Test',
            'email' => 'taken@ecopoche.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_can_change_password(): void
    {
        $this->user->update(['password' => Hash::make('ancienmdp')]);

        $this->put(route('profil.update.password'), [
            'current_password'      => 'ancienmdp',
            'password'              => 'nouveauMdp1!',
            'password_confirmation' => 'nouveauMdp1!',
        ])->assertRedirect();

        $this->user->refresh();
        $this->assertTrue(Hash::check('nouveauMdp1!', $this->user->password));
    }

    public function test_wrong_current_password_rejected(): void
    {
        $this->user->update(['password' => Hash::make('correct')]);

        $this->put(route('profil.update.password'), [
            'current_password'      => 'wrong',
            'password'              => 'nouveauMdp1!',
            'password_confirmation' => 'nouveauMdp1!',
        ])->assertSessionHasErrors('current_password');
    }

    private function validPrefsPayload(array $overrides = []): array
    {
        return array_merge([
            'quota_taux'           => 25,
            'devise'               => 'EUR',
            'notifs_email'         => '1',
            'seuil_attention'      => 65,
            'seuil_critique'       => 88,
            'seuil_plafond_cat'    => 75,
            'objectif_epargne_pct' => 15,
            'jour_bilan_email'     => 5,
            'mode_discret'         => '0',
        ], $overrides);
    }

    public function test_can_update_preferences(): void
    {
        $this->put(route('profil.update.preferences'), $this->validPrefsPayload())->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'                   => $this->user->id,
            'quota_taux'           => 25,
            'devise'               => 'EUR',
            'seuil_attention'      => 65,
            'seuil_critique'       => 88,
            'seuil_plafond_cat'    => 75,
            'objectif_epargne_pct' => 15,
            'jour_bilan_email'     => 5,
        ]);
    }

    public function test_quota_taux_must_be_between_0_and_100(): void
    {
        $this->put(route('profil.update.preferences'), $this->validPrefsPayload(['quota_taux' => 150]))
             ->assertSessionHasErrors('quota_taux');
    }

    public function test_seuil_critique_must_be_greater_than_seuil_attention(): void
    {
        $this->put(route('profil.update.preferences'), $this->validPrefsPayload([
            'seuil_attention' => 80,
            'seuil_critique'  => 70,
        ]))->assertSessionHasErrors('seuil_critique');
    }

    public function test_jour_bilan_must_be_between_1_and_28(): void
    {
        $this->put(route('profil.update.preferences'), $this->validPrefsPayload(['jour_bilan_email' => 31]))
             ->assertSessionHasErrors('jour_bilan_email');
    }

    public function test_objectif_epargne_pct_max_80(): void
    {
        $this->put(route('profil.update.preferences'), $this->validPrefsPayload(['objectif_epargne_pct' => 90]))
             ->assertSessionHasErrors('objectif_epargne_pct');
    }
}
