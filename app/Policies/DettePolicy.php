<?php

namespace App\Policies;

use App\Models\Dette;
use App\Models\User;
use App\Policies\Concerns\OwnsUserData;

class DettePolicy
{
    use OwnsUserData;

    public function view(User $user, Dette $dette): bool
    {
        return $this->owns($user, $dette->user_id);
    }

    public function update(User $user, Dette $dette): bool
    {
        return $this->view($user, $dette);
    }

    public function delete(User $user, Dette $dette): bool
    {
        return $this->view($user, $dette);
    }
}
