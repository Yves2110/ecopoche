<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    private array $defaults = [
        ['nom' => 'Essence / Transport', 'icone' => 'local_gas_station', 'couleur' => '#2E86AB', 'ordre' => 1],
        ['nom' => 'Nourriture',          'icone' => 'restaurant',         'couleur' => '#10B981', 'ordre' => 2],
        ['nom' => 'Extras / Loisirs',    'icone' => 'celebration',        'couleur' => '#F59E0B', 'ordre' => 3],
        ['nom' => 'Imprévus',            'icone' => 'warning',            'couleur' => '#EF4444', 'ordre' => 4],
        ['nom' => 'Famille',             'icone' => 'family_restroom',    'couleur' => '#8B5CF6', 'ordre' => 5],
        ['nom' => 'Autres',              'icone' => 'more_horiz',         'couleur' => '#6B7280', 'ordre' => 6],
    ];

    public function run(): void
    {
        User::all()->each(function (User $user) {
            foreach ($this->defaults as $cat) {
                Categorie::firstOrCreate(
                    ['user_id' => $user->id, 'nom' => $cat['nom']],
                    array_merge($cat, [
                        'user_id'    => $user->id,
                        'type'       => 'depense',
                        'is_default' => true,
                        'is_active'  => true,
                    ])
                );
            }
        });
    }

    public function createForUser(User $user): void
    {
        foreach ($this->defaults as $cat) {
            Categorie::firstOrCreate(
                ['user_id' => $user->id, 'nom' => $cat['nom']],
                array_merge($cat, [
                    'user_id'    => $user->id,
                    'type'       => 'depense',
                    'is_default' => true,
                    'is_active'  => true,
                ])
            );
        }
    }
}
