<?php

namespace App\Policies;

use App\Models\Rab;
use App\Models\User;

class RabPolicy
{
    public function readPolicy(User $user): bool
    {
        return $user->hasPermissionTo('rab-read');
    }

    public function createPolicy(User $user): bool
    {
        return $user->hasPermissionTo('rab-create')
            && str_starts_with($user->getRoleNames()->first() ?? '', 'divisi_');
    }

    public function updatePolicy(User $user, Rab $rab): bool
    {
        return $user->hasPermissionTo('rab-update')
            && str_starts_with($user->getRoleNames()->first() ?? '', 'divisi_')
            && $rab->divisi_id === $user->getRoleNames()->first()
            && $rab->isEditable();
    }

    public function cancelPolicy(User $user, Rab $rab): bool
    {
        return str_starts_with($user->getRoleNames()->first() ?? '', 'divisi_')
            && $rab->divisi_id === $user->getRoleNames()->first()
            && $rab->isCancellable();
    }

    public function approvePolicy(User $user, Rab $rab): bool
    {
        return $user->hasRole('bendahara_umum') || $user->hasRole('ketua_yayasan');
    }
}
