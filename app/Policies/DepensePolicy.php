<?php

namespace App\Policies;

use App\Models\Depense;
use App\Models\User;
use App\Policies\Concerns\OwnsUserData;

class DepensePolicy
{
    use OwnsUserData;

    public function update(User $user, Depense $depense): bool
    {
        return $this->owns($user, $depense->budget->user_id);
    }

    public function delete(User $user, Depense $depense): bool
    {
        return $this->update($user, $depense);
    }
}
