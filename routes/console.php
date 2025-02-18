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

            $streamerTotalFollowers = Http::get('https://twitchtracker.com/api/channels/summary/'.$streamer->twitch_username)->json('followers_total');

            $html = Http::get($url)->body();

            $streamer->update([
                'is_live' => str($html)->contains('"isLiveBroadcast":true'),
                'total_followers' => $streamerTotalFollowers ?? 0,
            ]);
        });
})->everyFiveMinutes();
