<?php

use App\Http\Controllers\Api\MediaStreamController;
use App\Http\Controllers\Api\TwilioWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('twilio')->group(function () {
    Route::post('status', [TwilioWebhookController::class, 'status'])->name('api.twilio.status');
    Route::get('twiml/{callSession}', [TwilioWebhookController::class, 'twiml'])->name('api.twilio.twiml');
    Route::get('twiml', [TwilioWebhookController::class, 'twiml'])->name('api.twilio.twiml.query');
});

Route::post('media-stream', [MediaStreamController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.media-stream.store');
