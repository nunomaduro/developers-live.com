<?php

return [
    'url' => [
        'auth' => 'https://id.twitch.tv/oauth2/token',
        'api' => 'https://api.twitch.tv/helix',
    ],
    'client' => [
        'id' => env('TWITCH_CLIENT_ID'),
        'secret' => env('TWITCH_CLIENT_SECRET'),
    ],
    'callback' => [
        'secret' => env('TWITCH_CALLBACK_SECRET'),
    ]
];
