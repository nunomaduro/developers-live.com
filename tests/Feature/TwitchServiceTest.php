<?php

use App\Services\TwitchService;
use Illuminate\Support\Facades\Cache;

test('service fetches the access token correctly', function () {

    $service = app(TwitchService::class);

    expect($service->getAccessToken())
        ->not->toBeNull()
        ->toEqual(Cache::get('twitch_access_token'));

});


test('service fetches the broadcaster id correctly', function () {

    $service = app(TwitchService::class);

    /* enunomaduro => 139973107 */
    expect($service->getBroadcasterId('enunomaduro'))
        ->not->toBeNull()
        ->toEqual(139973107);

    expect($service->getBroadcasterId('1'))
        ->toBeNull();

});
