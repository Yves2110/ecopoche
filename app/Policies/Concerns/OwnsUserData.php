<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait OwnsUserData
{
    protected function owns(User $user, int $ownerId): bool
    {
        return $user->id === $ownerId;
    }
}
