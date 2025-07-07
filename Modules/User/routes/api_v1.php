<?php

use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controller\UserApiController;

Route::apiResource('user',UserApiController::class);
