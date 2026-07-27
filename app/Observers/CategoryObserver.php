<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    private const array CACHE_KEYS = [
        'all' => 'categories:all',
        'dashboard' => 'dashboard:metrics',
    ];

    public function created(Category $category): void
    {
        $this->forget('all', 'dashboard');
    }

    public function updated(Category $category): void
    {
        $this->forget('all');
    }

    public function deleted(Category $category): void
    {
        $this->forget('all', 'dashboard');
    }

    public function restored(Category $category): void
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
