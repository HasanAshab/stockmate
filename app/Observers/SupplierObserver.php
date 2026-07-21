<?php

namespace App\Observers;

use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;

class SupplierObserver
{
    private const array CACHE_KEYS = [
        'all' => 'suppliers:all',
        'dashboard' => 'dashboard:metrics',
    ];

    public function created(Supplier $supplier): void
    {
        $this->forget('all', 'dashboard');
    }

    public function updated(Supplier $supplier): void
    {
        $this->forget('all');
    }

    public function deleted(Supplier $supplier): void
    {
        $this->forget('all', 'dashboard');
    }

    public function restored(Supplier $supplier): void
    {
        $this->forget('all', 'dashboard');
    }

    private function forget(string ...$keys): void
    {
        foreach ($keys as $key) {
            Cache::forget(self::CACHE_KEYS[$key]);
        }
    }
}
