<?php

namespace Modules\Challenge\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ChallengeServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->bind(
            \Modules\Challenge\App\Contracts\ChallengeServiceInterface::class,
            \Modules\Challenge\App\Services\ChallengeService::class
        );
    }

    public function boot()
    {
           Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/Challenge/routes/api_v1.php'));
        
        // Load module config
        $this->mergeConfigFrom(__DIR__.'/../../config/Challenge.php', 'Challenge');
    }
}