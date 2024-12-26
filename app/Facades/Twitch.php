<?php

namespace App\Facades;

use App\Services\TwitchService;
use Illuminate\Support\Facades\Facade;

/**
 * @see TwitchService
 */
class Twitch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TwitchService::class;
    }
}
