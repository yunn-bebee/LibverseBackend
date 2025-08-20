<?php

namespace Modules\Post\App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PostServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register module services
        $this->app->bind(
            \Modules\Post\App\Contracts\PostServiceInterface::class,
            \Modules\Post\App\Services\PostService::class
        );
    }

    public function boot()
    {
           Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('Modules/Post/routes/api_v1.php'));

        // Load module config
        $this->mergeConfigFrom(__DIR__.'/../../config/Post.php', 'Post');
    }
}
