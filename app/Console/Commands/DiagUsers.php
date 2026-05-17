<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DiagUsers extends Command
{
    protected $signature = 'diag:users';
    protected $description = 'Liste les utilisateurs avec leurs préférences mail';

    public function handle(): void
    {
        $users = User::all(['id', 'email', 'is_active', 'notifs_email']);
        $this->table(
            ['ID', 'Email', 'Actif', 'Notifs Email'],
            $users->map(fn($u) => [
                $u->id,
                $u->email,
                $u->is_active ? '✓' : '✗',
                $u->notifs_email ? '✓' : '✗',
            ])->toArray()
        );
    }
}
