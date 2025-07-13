<?php

use Illuminate\Support\Facades\Route;
use Modules\Event\App\Http\Controller\EventApiController;

Route::apiResource('event', EventApiController::class);
