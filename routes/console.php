<?php

use App\Enums\StreamerStatus;
use App\Models\Category;
use App\Models\Streamer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('sync:streamers', function () {
    Streamer::query()
        ->where('status', StreamerStatus::Approved)
        ->each(function (Streamer $streamer) {
            $url = 'https://www.twitch.tv/' . urlencode(
                $streamer->twitch_username
            );

            $html = Http::get($url)->body();

            $streamer->update([
                'is_live' => str($html)->contains('"isLiveBroadcast":true'),
            ]);
        });
})->everyFiveMinutes();

Artisan::command('sync:streamers-to-categories', function () {
    Streamer::query()
        ->where('status', StreamerStatus::Approved)
        ->each(function (Streamer $streamer) {
            $url = 'https://api.twitch.tv/helix/search/channels?query=' . urlencode(
                $streamer->twitch_username
            );

            $streamerHttp = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.twitch.access_token'),
                'Client-ID' => config('services.twitch.client_id')
            ])->get($url);

            $streamerJson = $streamerHttp->json();

            $filteredStreamerData = [];
            foreach ($streamerJson['data'] as $data) {
                if ($data['display_name'] === $streamer->twitch_username) {
                    $filteredStreamerData[$data['display_name']] = $data;
                }
            }

            if (empty($filteredStreamerData[$streamer->twitch_username])) {
                return;
            }

            $streamerData = $filteredStreamerData[$streamer->twitch_username];

            $category = Category::query()
                ->where('game_id', $streamerData['game_id'])
                ->orWhere('category_name', $streamerData['game_name'])
                ->first();

            if (! $category) {
                $category = Category::create([
                    'category_name' => $streamerData['game_name'],
                    'game_id' => $streamerData['game_id'],
                ]);
            }

            $category->update([
                'game_id' => $streamerData['game_id'],
            ]);

            $streamer->update([
                'category_id' => $category->id,
            ]);
        });
})->everyFiveMinutes();
