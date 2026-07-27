<?php

use App\Enums\Role;
use App\Notifications\ProductLowStockNotification;
use App\Notifications\ProductOutOfStockNotification;

return [
    ProductLowStockNotification::class => [
        Role::StoreManager,
        Role::WarehouseManager,
        Role::PurchasingOfficer,
    ],

    ProductOutOfStockNotification::class => [
        Role::StoreManager,
        Role::WarehouseManager,
    ],
];
