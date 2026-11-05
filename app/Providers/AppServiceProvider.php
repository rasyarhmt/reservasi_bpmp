<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $assetUrl = config('app.asset_url');

        if (is_string($assetUrl) && str_contains($assetUrl, '${')) {
            config(['app.asset_url' => null]);
        }
    }
}
