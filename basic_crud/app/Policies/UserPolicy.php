<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function updateRole(User $user, User $target): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        if ($user->id === $target->id) {
            return false;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
