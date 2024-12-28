<?php

namespace App\Listeners;

use App\Events\StreamerApprovedEvent;
use App\Services\Enums\EventSub;
use App\Services\TwitchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener handles logic when a streamer is approved. It subscribes the streamer to Twitch
 * event notifications and updates their Twitch ID in the database.
 */
class StreamerApprovedListener implements ShouldQueue
{
    protected TwitchService $service;

    public function __construct()
    {
        $this->service = app(TwitchService::class);
    }

    public function handle(StreamerApprovedEvent $event): void
    {
        $username = $event->streamer->twitch_username;
        $twitch_id = $this->service->getBroadcasterId($username);

        if (!$twitch_id) {
            throw new \Exception('Could not find broadcaster id');
        }

        $event->streamer->update(['twitch_id' => $twitch_id]);

        if ($this->service->subscribe(EventSub::StreamOnline, $username)
            && $this->service->subscribe(EventSub::StreamOffline, $username)) {
            Log::debug("Subscribed to stream online and offline events for $username");
        }
    }
}
