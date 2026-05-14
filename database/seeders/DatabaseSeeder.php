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
                'password'   => bcrypt('password123'),
                'role'       => 'super_admin',
                'is_active'  => true,
                'devise'     => 'FCFA',
                'email_verified_at' => now(),
            ]
        );

        $this->call(CategorieSeeder::class);
    }
}
