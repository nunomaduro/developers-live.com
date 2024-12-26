<?php

namespace App\Rules;

use App\Facades\Twitch;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TwitchUsernameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!Twitch::getBroadcasterId($value)) {
            $fail('This username is not a valid twitch username');
        }
    }
}
