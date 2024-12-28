<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event class that indicates the live status of a streamer.
 */
class StreamerLiveStatus implements ShouldBroadcast
{
    use Dispatchable;

    public function __construct(
        public ?string $id,
        public ?string $broadcaster_user_id,
        public ?string $broadcaster_user_login,
        public ?string $broadcaster_user_name,
        public ?string $type,
        public ?Carbon $started_at,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('streamers-live-status'),
        ];
    }
}
