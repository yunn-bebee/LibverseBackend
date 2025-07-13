<?php

namespace Modules\User\App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\User\App\Contracts\UserServiceInterface;
use Modules\User\App\Services\UserService;
use Illuminate\Support\Facades\Route;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add this binding
    $this->app->bind(
        \Modules\User\App\Contracts\ProfileServiceInterface::class,
        \Modules\User\App\Services\ProfileService::class
    );
        $this->app->bind(UserServiceInterface::class, UserService::class);
        Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/User/routes/api_v1.php'));
        $this->mergeConfigFrom(__DIR__."/../../config/config.php", "user");
    }
}
