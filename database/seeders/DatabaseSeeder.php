<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@ecopoche.com'],
            [
                'prenom'            => 'Super',
                'nom'               => 'Admin',
                'name'              => 'Super Admin',
                'password'          => bcrypt('Prince2110@'),
                'role'              => 'super_admin',
                'is_active'         => true,
                'devise'            => 'FCFA',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin2@ecopoche.com'],
            [
                'prenom'            => 'Marie',
                'nom'               => 'Admin',
                'name'              => 'Marie Admin',
                'password'          => bcrypt('password123'),
                'role'              => 'admin',
                'is_active'         => true,
                'devise'            => 'FCFA',
                'email_verified_at' => now(),
                'created_by'        => 1,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@ecopoche.com'],
            [
                'prenom'            => 'Paul',
                'nom'               => 'Utilisateur',
                'name'              => 'Paul Utilisateur',
                'password'          => bcrypt('password123'),
                'role'              => 'user',
                'is_active'         => true,
                'devise'            => 'FCFA',
                'email_verified_at' => now(),
                'created_by'        => 1,
            ]
        );

        User::firstOrCreate(
            ['email' => 'suspendu@ecopoche.com'],
            [
                'prenom'            => 'Jean',
                'nom'               => 'Suspendu',
                'name'              => 'Jean Suspendu',
                'password'          => bcrypt('password123'),
                'role'              => 'user',
                'is_active'         => false,
                'devise'            => 'FCFA',
                'email_verified_at' => now(),
                'created_by'        => 1,
            ]
        );

        $this->call(CategorieSeeder::class);
    }
}
