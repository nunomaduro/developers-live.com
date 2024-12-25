<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class TwitchService
{
    public function getProfileData(string $twitchUsername): array
    {
        try {

            $url = config('twitch.url').$twitchUsername;

            $html = Http::get($url)->body();

            $crawler = new Crawler($html);

            $avatarUrl = $crawler->filterXPath('//meta[@property="og:image"]')->attr('content');
            $description = $crawler->filterXPath('//meta[@name="twitter:description"]')->attr('content');

            return [
                'avatar_url' => $avatarUrl,
                'description' => $description,
            ];

        } catch (\Exception $e) {

            return [
                'avatar_url' => null,
                'description' => null,
            ];
        }
    }
}
