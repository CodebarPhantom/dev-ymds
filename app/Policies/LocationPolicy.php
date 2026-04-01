<?php

namespace App\Policies;

use App\Models\User;

class LocationPolicy
{

    public function readPolicy(User $user): bool
    {
        return $user->can('location-read');
    }

    public function createPolicy(User $user): bool
    {
        return $user->can('location-create');
    }

    public function updatePolicy(User $user): bool
    {
        return $user->can('location-update');
    }
    public function deletePolicy(User $user): bool
    {
        return $user->can('location-delete');
    }
}
