<?php

namespace App\Models;

use App\Enums\StreamerStatus;
use App\Facades\Twitch;
use Illuminate\Database\Eloquent\Model;
use function Illuminate\Events\queueable;

class Streamer extends Model
{

    protected static function booted()
    {
        self::created(queueable(function (self $streamer) {
            $twitch_id =  Twitch::getBroadcasterId($streamer->twitch_username);

            if (!$twitch_id) {
                throw new \Exception('Could not find broadcaster id');
            }

            $streamer->twitch_id = $twitch_id;
            $streamer->save();
        }));
    }

    /**
     * The casted attributes.
     */
    public function casts(): array
    {
        return [
            'status' => StreamerStatus::class,
        ];
    }

}
