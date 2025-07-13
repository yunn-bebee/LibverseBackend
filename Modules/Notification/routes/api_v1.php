<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\App\Http\Controller\NotificationApiController;

Route::apiResource('notification', NotificationApiController::class);
