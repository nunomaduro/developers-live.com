<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSubscription extends Model
{
    protected function casts(): array
    {
        return [
            'subscription_id' => 'string',
        ];
    }

    public function streamer(): BelongsTo
    {
        return $this->belongsTo(Streamer::class, 'broadcaster_id', 'twitch_id');
    }
}
