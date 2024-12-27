<?php

namespace App\Listeners;

use App\Events\StreamerLiveStatus;
use App\Models\Streamer;
use Illuminate\Contracts\Queue\ShouldQueue;

class LiveStatusListener implements ShouldQueue
{
    public function __construct() {}

    public function handle(StreamerLiveStatus $event): void
    {
        Streamer::query()
            ->where('twitch_id', $event->broadcaster_user_id)
            ->update([
                'is_live' => $event->type === 'live',
            ]);
    }
}
