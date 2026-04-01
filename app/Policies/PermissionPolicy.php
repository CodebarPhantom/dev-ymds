<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{

    public function readPolicy(User $user): bool
    {
        return $user->can('permission-read');

    }

    public function createPolicy(User $user): bool
    {
        return $user->can('permission-create');

    }

    public function updatePolicy(User $user): bool
    {
        return $user->can('permission-update');

    }
    public function deletePolicy(User $user): bool
    {
        return $user->can('permission-delete');

    }
}
