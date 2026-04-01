<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{

    public function readPolicy(User $user): bool
    {
        return $user->can('role-read');

    }

    public function createPolicy(User $user): bool
    {
        return $user->can('role-create');

    }

    public function updatePolicy(User $user): bool
    {
        return $user->can('role-update');

    }
    public function deletePolicy(User $user): bool
    {
        return $user->can('role-delete');

    }
}
