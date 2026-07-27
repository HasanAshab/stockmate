<?php

use App\Enums\Role;

return [
    \App\Notifications\ProductLowStockNotification::TYPE => [
        Role::StoreManager,
        Role::WarehouseManager,
        Role::PurchasingOfficer,
    ],
    
    \App\Notifications\ProductOutOfStockNotification::TYPE => [
        Role::StoreManager,
        Role::WarehouseManager,
    ],
];