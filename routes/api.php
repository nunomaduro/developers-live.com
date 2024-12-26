<?php

use App\Http\Controllers\TwitchWebhookController;
use App\Http\Middleware\TwitchCallbackMiddleware;
use Illuminate\Support\Facades\Route;

Route::any('/webhooks/twitch/{event}', TwitchWebhookController::class)
    ->middleware(TwitchCallbackMiddleware::class)
    ->name('webhooks.twitch');
