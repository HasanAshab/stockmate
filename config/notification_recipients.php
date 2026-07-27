<?php

use App\Enums\Role;

return [
    \App\Notifications\LowStockAlert::TYPE => [
        Role::StoreManager,
        Role::WarehouseManager,
        Role::PurchasingOfficer,
    ],
];