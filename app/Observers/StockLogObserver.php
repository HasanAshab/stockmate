<?php

namespace App\Observers;

use App\Models\StockLog;
use Illuminate\Support\Facades\Cache;

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
