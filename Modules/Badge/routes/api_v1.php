<?php

use Illuminate\Support\Facades\Route;
use Modules\Badge\App\Http\Controller\BadgeApiController;

Route::apiResource('badge', BadgeApiController::class);
