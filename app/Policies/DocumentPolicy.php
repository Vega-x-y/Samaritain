<?php

namespace App\Policies;

use App\Models\OwnerDocument;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, OwnerDocument $document): bool
    {
        return $document->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, OwnerDocument $document): bool
    {
        return $document->created_by === $user->id;
    }

    public function delete(User $user, OwnerDocument $document): bool
    {
        return $document->created_by === $user->id;
    }
}
