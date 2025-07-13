<?php

namespace Modules\Badge\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BadgeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->bind(
            \Modules\Badge\App\Contracts\BadgeServiceInterface::class,
            \Modules\Badge\App\Services\BadgeService::class
        );
    }

    public function boot()
    {
           Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/Badge/routes/api_v1.php'));
        
        // Load module config
        $this->mergeConfigFrom(__DIR__.'/../../config/Badge.php', 'Badge');
    }
}