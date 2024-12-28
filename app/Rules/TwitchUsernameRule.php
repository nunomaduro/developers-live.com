<?php

namespace App\Rules;

use App\Services\TwitchService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule to check if a given Twitch username exists.
 * This rule utilizes the TwitchService to validate the existence of the username.
 */
class TwitchUsernameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(TwitchService::class)->checkUsernameExists($value)) {
            $fail('This username is not a valid twitch username');
        }
    }
}
