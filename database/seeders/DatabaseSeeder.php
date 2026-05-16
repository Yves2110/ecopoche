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
                'name'       => 'Super Admin',
                'password'   => bcrypt('Prince2110@'),
                'role'       => 'super_admin',
                'is_active'  => true,
                'devise'     => 'FCFA',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin2@ecopoche.com'],
            [
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
