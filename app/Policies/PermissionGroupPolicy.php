<?php

namespace App\Policies;

use App\Models\User;

class PermissionGroupPolicy
{

    public function readPolicy(User $user): bool
    {
        return $user->can('permission-group-read');

    }

    public function createPolicy(User $user): bool
    {
        return $user->can('permission-group-create');

    }

    public function updatePolicy(User $user): bool
    {
        return $user->can('permission-group-update');

    }
    public function deletePolicy(User $user): bool
    {
        return $user->can('permission-group-delete');

    }
}
