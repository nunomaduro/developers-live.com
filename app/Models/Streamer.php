<?php

namespace App\Models;

use App\Enums\StreamerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Streamer extends Model
{
    /**
     * The casted attributes.
     */
    public function casts(): array
    {
        return [
            'status' => StreamerStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
