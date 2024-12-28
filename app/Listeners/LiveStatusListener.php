<?php

namespace App\Listeners;

use App\Events\StreamerLiveStatus;
use App\Models\Streamer;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener handles the broadcast live status of a streamer.
 *
 * This listener responds to the StreamerLiveStatus event and updates the
 * "is_live" status of the streamer in the database accordingly based on
 * the event type.
 */
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
