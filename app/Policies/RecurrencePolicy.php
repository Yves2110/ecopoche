<?php

namespace App\Policies;

use App\Models\Recurrence;
use App\Models\User;
use App\Policies\Concerns\OwnsUserData;

class RecurrencePolicy
{
    use OwnsUserData;

    public function update(User $user, Recurrence $recurrence): bool
    {
        return $this->owns($user, $recurrence->user_id);
    }

    public function delete(User $user, Recurrence $recurrence): bool
    {
        return $this->update($user, $recurrence);
    }
}
