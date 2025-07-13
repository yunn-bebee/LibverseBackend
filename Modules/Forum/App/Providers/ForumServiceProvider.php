<?php

namespace Modules\Forum\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ForumServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->bind(
            \Modules\Forum\App\Contracts\ForumServiceInterface::class,
            \Modules\Forum\App\Services\ForumService::class
        );
    }

    public function boot()
    {
           Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/Forum/routes/api_v1.php'));
        
        // Load module config
        $this->mergeConfigFrom(__DIR__.'/../../config/Forum.php', 'Forum');
    }
}