<?php

namespace App\Policies;

use App\Models\Hotel;
use App\Models\User;

class HotelPolicy
{
    public function view(?User $user, Hotel $hotel): bool
    {
        if ($hotel->is_active && $hotel->is_verify) {
            return true;
        }

        return $user !== null && ($user->id === $hotel->created_by || $user->isAdmin());
    }

    public function update(User $user, Hotel $hotel): bool
    {
        return $user->id === $hotel->created_by || $user->isAdmin();
    }

    public function delete(User $user, Hotel $hotel): bool
    {
        return $user->id === $hotel->created_by || $user->isAdmin();
    }
}
