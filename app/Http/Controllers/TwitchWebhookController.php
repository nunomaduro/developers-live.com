<?php

namespace App\Http\Controllers;

use App\Events\StreamerLiveStatus;
use App\Models\EventSubscription;
use App\Services\Enums\EventSub;
use App\Services\TwitchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that handles Twitch Webhook callbacks.
 */
class TwitchWebhookController extends Controller
{
    protected const TWITCH_EVENT_SUB_TYPE = 'twitch-eventsub-message-type';

    protected const TWITCH_EVENT_SUB_TYPE_CHALLENGE = 'webhook_callback_verification';

    protected const TWITCH_EVENT_SUB_TYPE_NOTIFICATION = 'notification';

    protected const TWITCH_EVENT_SUB_TYPE_REVOCATION = 'revocation';

    public function __invoke(Request $request, EventSub $event)
    {
        if (! $request->hasHeader(self::TWITCH_EVENT_SUB_TYPE)) {
            return response()->json([
                'error' => 'Missing required headers',
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
            if (! array_key_exists('type', $subscription) || $subscription['type'] !== $event->value) {
                return response()->json([
                    'error' => 'Invalid subscription type',
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

    /**
     * Handles the Twitch subscription verification challenge
     *
     * @param  Request  $request  the received request
     * @return \Illuminate\Http\Response the response the API is expecting
     */
    protected function handleChallenge(Request $request): \Illuminate\Http\Response
    {
        $challenge = $request->get('challenge');

        return response(
            $challenge,
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        );
    }

    /**
     * Handles the revocation of a Twitch subscription and performs necessary cleanup.
     *
     * @param  mixed  $subscription  the subscription data that was revoked
     * @param  EventSub  $event  the event associated with the subscription revocation
     */
    public function handleSubscriptionRevocation(mixed $subscription, EventSub $event): void
    {
        Log::warning("Twitch revoked subscription with id \"{$subscription['id']}\" with the following reason: \"{$subscription['status']}\"");

        if (array_key_exists('condition', $subscription)) {
            $condition = $subscription['condition'];
            if (array_key_exists('broadcaster_user_id', $condition)) {
                $subscription = EventSubscription::query()
                    ->where('broadcaster_id', $condition['broadcaster_user_id'])
                    ->where('type', $event->value)
                    ->first();

                if (! $subscription && ! $subscription->subscription_id) {
                    app(TwitchService::class)->unsubscribe($event, $subscription->subscription_id);
                    $subscription->delete();
                }
            }
        }
    }
}
