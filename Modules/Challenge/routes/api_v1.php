<?php

use Illuminate\Support\Facades\Route;
use Modules\Challenge\App\Http\Controller\ChallengeApiController;

Route::apiResource('challenge', ChallengeApiController::class);
