<?php

namespace App\Policies;

use App\Models\Remboursement;
use App\Models\User;
use App\Policies\Concerns\OwnsUserData;

class RemboursementPolicy
{
    use OwnsUserData;

    public function delete(User $user, Remboursement $remboursement): bool
    {
        return $this->owns($user, $remboursement->dette->user_id);
    }
}
