<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminLevel();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isAdminLevel()) {
            return true;
        }

        return $student->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminLevel();
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->isAdminLevel()) {
            return true;
        }

        return $student->user_id === $user->id;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isSuperAdmin();
    }
}
