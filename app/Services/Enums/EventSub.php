<?php

namespace App\Services\Enums;

enum EventSub: string
{
    case ChannelUpdate = 'channel.update';
    case StreamOnline = 'stream.online';
    case StreamOffline = 'stream.offline';

    /**
     * @return int the version of the event
     */
    public function getVersion(): int
    {
        return match ($this) {
            self::ChannelUpdate => 2,
            self::StreamOffline, self::StreamOnline => 1,
        };
    }
}
