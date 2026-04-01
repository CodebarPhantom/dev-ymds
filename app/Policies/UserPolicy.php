<?php

namespace App\Policies;


use App\Models\User;

class UserPolicy
{
    public function readPolicy(User $user): bool
    {
        return $user->can('user-read');

    }

    public function createPolicy(User $user): bool
    {
        return $user->can('user-create');

    }

    public function updatePolicy(User $user): bool
    {
        return $user->can('user-update');

    }
    public function deletePolicy(User $user): bool
    {
        return $user->can('user-delete');

    }
}
