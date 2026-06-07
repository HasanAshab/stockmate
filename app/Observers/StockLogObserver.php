<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;
use App\Models\StockLog;

class StockLogObserver
{
    /**
     * Handle the StockLog "created" event.
     */
    public function created(StockLog $stockLog): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget('dashboard:metrics');
    }
}
