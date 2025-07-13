<?php

namespace Modules\Auth\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->bind(
            \Modules\Auth\App\Contracts\AuthServiceInterface::class,
            \Modules\Auth\App\Services\AuthService::class
        );
    }

    public function boot()
    {
          Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/Auth/routes/api_v1.php'));
        
        // Load module config
        $this->mergeConfigFrom(__DIR__.'/../../config/Auth.php', 'Auth');
    }
}