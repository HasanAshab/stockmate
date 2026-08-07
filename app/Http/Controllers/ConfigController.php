<?php

namespace App\Http\Controllers;

use App\Enums\StockLogType;

class ConfigController extends Controller
{
    /**
     * Get Enums
     *
     * Get all available enum values for various system entities.
     */
    public function enums(): array
    {
        return [
            'stock_log_types' => StockLogType::allCasesArray(),
        ];
    }
}
