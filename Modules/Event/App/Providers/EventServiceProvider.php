<?php

namespace Modules\Event\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->bind(
            \Modules\Event\App\Contracts\EventServiceInterface::class,
            \Modules\Event\App\Services\EventService::class
        );
    }

    public function boot()
    {
           Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/Event/routes/api_v1.php'));
        
        // Load module config
        $this->mergeConfigFrom(__DIR__.'/../../config/Event.php', 'Event');
    }
}