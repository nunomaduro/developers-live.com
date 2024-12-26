<?php

return [
    'client' => [
        'id' => env('TWITCH_CLIENT_ID'),
        'secret' => env('TWITCH_CLIENT_SECRET'),
    ],
    'callback' => [
        'secret' => env('TWITCH_CALLBACK_SECRET'),
    ]
];
