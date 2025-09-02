<?php

use Illuminate\Support\Facades\Route;
use Modules\Event\App\Http\Controller\EventApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('events', EventApiController::class);
    Route::post('events/{event}/rsvp', [EventApiController::class, 'rsvp']);
    Route::get('events/{event}/rsvp-counts', [EventApiController::class, 'rsvpCounts']);
});
