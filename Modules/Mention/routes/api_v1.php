<?php

use Illuminate\Support\Facades\Route;
use Modules\Mention\App\Http\Controller\MentionApiController;

Route::apiResource('mention', MentionApiController::class);
