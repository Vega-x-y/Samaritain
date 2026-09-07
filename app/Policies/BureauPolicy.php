<?php

namespace App\Policies;

use App\Models\Bureau;
use App\Models\User;

class BureauPolicy
{
    public function view(?User $user, Bureau $bureau): bool
    {
        if ($bureau->is_active && $bureau->is_verify) {
            return true;
        }

        return $user !== null && ($user->id === $bureau->created_by || $user->isStaff());
    }

    public function update(User $user, Bureau $bureau): bool
    {
        return $user->id === $bureau->created_by || $user->isStaff();
    }

    public function delete(User $user, Bureau $bureau): bool
    {
        return $user->id === $bureau->created_by || $user->isStaff();
    }
}

