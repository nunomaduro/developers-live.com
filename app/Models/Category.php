<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public function streamers(): HasMany
    {
        return $this->hasMany(Streamer::class);
    }

    public function dashedStreamerName(): string
    {
        return str_replace(' ', '-', strtolower($this->category_name));
    }
}
