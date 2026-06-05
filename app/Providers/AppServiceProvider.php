<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewApiDocs', function () {
        // WARN: This is a dummy project otherwise
        // never expose API DOCS on production
            // return !app()->environment('production');

            return true;
        });
    }
}