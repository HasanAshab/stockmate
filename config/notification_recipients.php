<?php

use App\Enums\Role;
use App\Notifications\ProductLowStockNotification;
use App\Notifications\ProductOutOfStockNotification;

return [
    ProductLowStockNotification::TYPE => [
        Role::StoreManager,
        Role::WarehouseManager,
        Role::PurchasingOfficer,
    ],

    ProductOutOfStockNotification::TYPE => [
        Role::StoreManager,
        Role::WarehouseManager,
    ],
];
