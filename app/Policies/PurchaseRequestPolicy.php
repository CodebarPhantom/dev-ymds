<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function readPolicy(User $user): bool
    {
        return $user->hasPermissionTo('purchase-request-read');
    }

    public function createPolicy(User $user): bool
    {
        return $user->hasPermissionTo('purchase-request-create')
            && str_starts_with($user->getRoleNames()->first() ?? '', 'divisi_');
    }

    public function updatePolicy(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermissionTo('purchase-request-update')
            && str_starts_with($user->getRoleNames()->first() ?? '', 'divisi_')
            && $purchaseRequest->divisi_id === $user->getRoleNames()->first()
            && $purchaseRequest->isEditable();
    }

    public function cancelPolicy(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermissionTo('purchase-request-cancel')
            && $purchaseRequest->divisi_id === $user->getRoleNames()->first()
            && $purchaseRequest->isCancellable();
    }

    public function approvePolicy(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermissionTo('purchase-request-approve')
            && ($user->hasRole('bendahara_umum') || $user->hasRole('ketua_yayasan'));
    }

    public function purchasingPolicy(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->hasPermissionTo('purchase-request-approve')
            && $user->hasRole('divisi_sarpras');
    }
}
