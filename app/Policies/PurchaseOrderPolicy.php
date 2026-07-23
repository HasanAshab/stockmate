<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PurchaseOrdersView);
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermissionTo(Permission::PurchaseOrdersView);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::PurchaseOrdersCreate);
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): Response
    {
        if (! $user->hasPermissionTo(Permission::PurchaseOrdersUpdate)) {
            return Response::deny('You do not have permission to update purchase orders.');
        }

        if ($purchaseOrder->status->isReceived() || $purchaseOrder->status->isCancelled()) {
            return Response::deny('This purchase order can no longer be edited.');
        }

        return Response::allow();
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermissionTo(Permission::PurchaseOrdersDelete);
    }

    public function cancel(User $user, PurchaseOrder $purchaseOrder): Response
    {
        if (! $user->hasPermissionTo(Permission::PurchaseOrdersCancel)) {
            return Response::deny('You do not have permission to cancel purchase orders.');
        }

        if ($purchaseOrder->status->isPartiallyReceived() || $purchaseOrder->status->isReceived()) {
            return Response::deny('Cannot cancel a PO that has already received stock.');
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft && $purchaseOrder->status !== PurchaseOrderStatus::Ordered) {
            return Response::deny('Only draft or ordered purchase orders can be cancelled.');
        }

        return Response::allow();
    }

    public function markOrdered(User $user, PurchaseOrder $purchaseOrder): Response
    {
        if (! $user->hasPermissionTo(Permission::PurchaseOrdersMarkOrdered)) {
            return Response::deny('You do not have permission to mark purchase orders as ordered.');
        }

        if (! $purchaseOrder->status->isDraft()) {
            return Response::deny('Only draft purchase orders can be marked as ordered.');
        }

        return Response::allow();
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): Response
    {
        if (! $user->hasPermissionTo(Permission::PurchaseOrdersReceive)) {
            return Response::deny('You do not have permission to receive purchase orders.');
        }

        if ($purchaseOrder->status !== PurchaseOrderStatus::Ordered && $purchaseOrder->status !== PurchaseOrderStatus::PartiallyReceived) {
            return Response::deny('Only ordered or partially received purchase orders can receive stock.');
        }

        return Response::allow();
    }
}
