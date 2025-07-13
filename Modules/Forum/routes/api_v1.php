<?php

use Illuminate\Support\Facades\Route;
use Modules\Forum\App\Http\Controller\ForumApiController;

Route::apiResource('forum', ForumApiController::class);
