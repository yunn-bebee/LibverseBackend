<?php

use Illuminate\Support\Facades\Route;
use Modules\Post\App\Http\Controller\PostApiController;

Route::apiResource('post', PostApiController::class);
