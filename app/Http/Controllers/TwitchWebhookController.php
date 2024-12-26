<?php

namespace App\Http\Controllers;

use App\Events\StreamerLiveStatus;
use App\Services\Enums\EventSub;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TwitchWebhookController extends Controller
{
    protected const TWITCH_EVENT_SUB_TYPE = 'twitch-eventsub-message-type';
    protected const TWITCH_EVENT_SUB_TYPE_CHALLENGE = 'webhook_callback_verification';
    protected const TWITCH_EVENT_SUB_TYPE_NOTIFICATION = 'notification';
    protected const TWITCH_EVENT_SUB_TYPE_REVOCATION = 'revocation';

    public function __invoke(Request $request, EventSub $event)
    {
        if (!$request->hasHeader(self::TWITCH_EVENT_SUB_TYPE)) {
            return response()->json([
                'error' => 'Missing required headers'
            ], Response::HTTP_BAD_REQUEST);
        }

        $eventType = $request->header(self::TWITCH_EVENT_SUB_TYPE);
        $subscription = $request->get('subscription');

        if ($eventType === self::TWITCH_EVENT_SUB_TYPE_CHALLENGE) {
            return $this->handleChallenge($request);
        }

        if ($eventType === self::TWITCH_EVENT_SUB_TYPE_REVOCATION) {
            $this->handleSubscriptionRevocation($subscription, $event);
        }

        if ($eventType === self::TWITCH_EVENT_SUB_TYPE_NOTIFICATION) {
            /* handle notification callback */

            if (!array_key_exists('type', $subscription) || $subscription['type'] !== $event->value) {
                return response()->json([
                    'error' => 'Invalid subscription type'
                ], Response::HTTP_BAD_REQUEST);
            }

            $event = $request->get('event');
            StreamerLiveStatus::dispatch(
                $event['id'] ?? null,
                $event['broadcaster_user_id'] ?? null,
                $event['broadcaster_user_login'] ?? null,
                $event['broadcaster_user_name'] ?? null,
                $event['type'] ?? null,
                isset($event['started_at']) ? Carbon::parse($event['started_at']) : Carbon::now()
            );
        }

    }

    protected function handleChallenge(Request $request): \Illuminate\Http\Response
    {
        $challenge = $request->get('challenge');
        return response(
            $challenge,
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        );
    }

    public function handleSubscriptionRevocation(mixed $subscription, mixed $event): void
    {
        Log::warning("Twitch revoked subscription with id \"{$subscription['id']}\" with the following reason: \"{$subscription['status']}\"");

        if (array_key_exists('condition', $subscription)) {
            $condition = $subscription['condition'];
            if (array_key_exists('broadcaster_user_id', $condition)) {
                Cache::forget("twitch:{$condition['broadcaster_user_id']}:subscriptions:$event->value");
                /* TODO handle database updates if necessary */
            }
        }
    }

}
