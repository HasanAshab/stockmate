<?php

namespace App\Policies;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalesOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isAdmin() || $user->role->isStaff();
    }

    public function view(User $user, SalesOrder $salesOrder): bool
    {
        return $user->role->isAdmin() || $user->role->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->role->isAdmin() || $user->role->isStaff();
    }

    public function cancel(User $user, SalesOrder $salesOrder): Response
    {
        if (! $user->role->isAdmin()) {
            return Response::deny('You do not have permission to cancel sales orders.');
        }

        if ($salesOrder->status->isPaid()) {
            return Response::deny('Cannot cancel a paid order.');
        }

        if ($salesOrder->status !== SalesOrderStatus::Pending) {
            return Response::deny('Only pending sales orders can be cancelled.');
        }

        return Response::allow();
    }

    public function initiatePayment(User $user, SalesOrder $salesOrder): Response
    {
        if (! $user->role->isAdmin() && ! $user->role->isStaff()) {
            return Response::deny('You do not have permission to initiate payment.');
        }

        if ($salesOrder->status !== SalesOrderStatus::Pending) {
            return Response::deny('Only pending sales orders can initiate payment.');
        }

        return Response::allow();
    }
}
