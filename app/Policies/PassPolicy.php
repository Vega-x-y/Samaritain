<?php

namespace App\Policies;

use App\Models\Pass;
use App\Models\User;

class PassPolicy
{
    public function viewAny(User $user): bool
    {
        // return $user->hasRole('admin') || $user->hasRole('agent');
        return true;
    }

    public function view(User $user, Pass $pass): bool
    {
        // return $user->hasRole('admin') || $user->hasRole('agent');
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Pass $pass): bool
    {
        // return $user->hasRole('admin');
        return true;
    }

    public function delete(User $user, Pass $pass): bool
    {
        // return $user->hasRole('admin');
        return true;
    }
    
    public function scan(User $user): bool
    {
        // return $user->hasRole('admin') || $user->hasRole('agent');
        return true;
    }
}