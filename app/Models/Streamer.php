<?php

namespace App\Models;

use App\Enums\StreamerStatus;
use App\Events\StreamerApprovedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Streamer extends Model
{
    protected static function booted()
    {
        static::updated(function (Streamer $streamer) {

            if (array_key_exists('status', $streamer->getChanges())
                && $streamer->getChanges()['status'] === StreamerStatus::Approved->value) {
                StreamerApprovedEvent::dispatch($streamer);
            }

        });
        static::created(function (Streamer $streamer) {

            if ($streamer->status === StreamerStatus::Approved) {
                StreamerApprovedEvent::dispatch($streamer);
            }

        });
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

    public function eventSubscriptions(): HasMany
    {
        return $this->hasMany(EventSubscription::class, 'broadcaster_id', 'twitch_id');
    }
}
