<?php

namespace App\Events;

use App\Models\Streamer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event triggered when a Streamer is approved.
 */
class StreamerApprovedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public Streamer $streamer) {}
}
