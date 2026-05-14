<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function update(User $user, Budget $budget): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $budget->user_id === $user->id;
    }

    public function view(User $user, Budget $budget): bool
    {
        if ($user->isSuperAdmin()) return true;
        return $budget->user_id === $user->id;
    }
}
