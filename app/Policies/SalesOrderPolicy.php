<?php

namespace App\Policies;

use App\Models\SalesOrder;
use App\Models\User;

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

    public function cancel(User $user, SalesOrder $salesOrder): bool
    {
        return $user->role->isAdmin();
    }

    public function initiatePayment(User $user, SalesOrder $salesOrder): bool
    {
        return $user->role->isAdmin() || $user->role->isStaff();
    }
}
