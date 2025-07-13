<?php

use Illuminate\Support\Facades\Route;
use Modules\Book\App\Http\Controller\BookApiController;

Route::apiResource('book', BookApiController::class);
