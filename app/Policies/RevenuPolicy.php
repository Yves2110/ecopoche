<?php

namespace App\Policies;

use App\Models\Revenu;
use App\Models\User;
use App\Policies\Concerns\OwnsUserData;

class RevenuPolicy
{
    use OwnsUserData;

    public function update(User $user, Revenu $revenu): bool
    {
        return $this->owns($user, $revenu->budget->user_id);
    }

    public function delete(User $user, Revenu $revenu): bool
    {
        return $this->update($user, $revenu);
    }
}
