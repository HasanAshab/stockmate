<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('viewApiDocs', function () {
            // WARN: This is a dummy project otherwise
            // never expose API DOCS on production
            // return !app()->isProduction();

            return true;
        });

        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );
    }
}
