<?php

namespace App\Models;

use App\Enums\StreamerStatus;
use Illuminate\Database\Eloquent\Model;

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
}
