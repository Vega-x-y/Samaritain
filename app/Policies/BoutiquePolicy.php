<?php

namespace App\Policies;

use App\Models\Boutique;
use App\Models\User;

class BoutiquePolicy
{
    public function view(?User $user, Boutique $boutique): bool
    {
        if ($boutique->is_active && $boutique->is_verify) {
            return true;
        }

        return $user !== null && ($user->id === $boutique->created_by || $user->isStaff());
    }

    public function update(User $user, Boutique $boutique): bool
    {
        return $user->id === $boutique->created_by || $user->isStaff();
    }

    public function delete(User $user, Boutique $boutique): bool
    {
        return $user->id === $boutique->created_by || $user->isStaff();
    }
}

