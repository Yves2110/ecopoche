<?php

namespace App\Policies;

use App\Models\Categorie;
use App\Models\User;
use App\Policies\Concerns\OwnsUserData;

class CategoriePolicy
{
    use OwnsUserData;

    public function update(User $user, Categorie $categorie): bool
    {
        return $this->owns($user, $categorie->user_id);
    }

    public function delete(User $user, Categorie $categorie): bool
    {
        return $this->update($user, $categorie);
    }
}
