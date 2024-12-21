<?php

use App\Enums\StreamerStatus;
use App\Models\Streamer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('sync:streamers', function () {
    Streamer::query()
        ->where('status', StreamerStatus::Approved)
        ->each(function (Streamer $streamer) {
            $url = 'https://www.twitch.tv/'.urlencode(
                $streamer->twitch_username
            );

            $html = Http::get($url)->body();

            $streamer->update([
                'is_live' => str($html)->contains('"isLiveBroadcast":true'),
            ]);
        });
})->everyFiveMinutes();
