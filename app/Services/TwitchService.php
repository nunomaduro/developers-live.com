<?php

namespace App\Services;

use App\Models\EventSubscription;
use App\Models\Streamer;
use App\Services\Enums\EventSub;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service handles interaction with the Twitch API,
 * including user and broadcaster data retrieval, subscriptions, and managing authentication tokens.
 */
class TwitchService
{
    protected function __construct(
        protected ?string $client_id,
        protected ?string $client_secret,
        protected ?string $access_token = null,
    ) {
        $this->fetchAccessToken();
    }

    /**
     * Creates a new instance with configuration values.
     */
    public static function make(): static
    {
        return new static(
            config('twitch.client.id'),
            config('twitch.client.secret'),
        );
    }

    /**
     * Prepares and returns a pending HTTP request with the necessary headers.
     */
    protected function getRequest(): PendingRequest
    {
        $this->fetchAccessToken();

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->access_token,
            'Client-Id' => $this->client_id,
        ]);
    }

    /**
     * Builds the transport array required for subscribing to events using webhooks
     */
    protected function getWebhookTransport(string $event): array
    {
        return [
            'method' => 'webhook',
            'callback' => route('webhooks.twitch', compact('event')),
            'secret' => $this->getCallbackSecret(),
        ];
    }

    /**
     * Fetches and caches the Twitch API access token.
     *
     * @throws \Exception If the Twitch client ID or secret is not set.
     */
    public function fetchAccessToken(): void
    {
        if (! $this->client_id || ! $this->client_secret) {
            throw new \Exception('Twitch client id or secret not set.');
        }

        if (! Cache::has('twitch:accessToken')) {
            $response = Http::post(config('twitch.url.auth'), [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials',
            ])->json();

            $this->access_token = Cache::remember(
                'twitch:accessToken',
                now()->addMillis($response['expires_in'] ?? 5),
                fn () => $response['access_token'] ?? null,
            );
        } else {
            $this->access_token = Cache::get('twitch:accessToken');
        }
    }

    /**
     * Retrieves the access token.
     */
    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    /**
     * Retrieves the Twitch broadcaster ID associated with the given username.
     *
     * Queries the database first, if no match is found retrieves the id from the Twitch API
     *
     * @param  string  $username  The Twitch username to retrieve the broadcaster ID for.
     * @return string|null The broadcaster ID if found, otherwise null.
     */
    public function getBroadcasterId(string $username): ?string
    {
        $streamer = Streamer::query()
            ->where('twitch_username', $username)
            ->whereNotNull('twitch_id');

        return $streamer->exists()
            ? $streamer->first()->twitch_id
            : $this->getRequest()->get(config('twitch.url.api').'/users',
                [
                    'login' => $username,
                ]
            )->json('data.0.id');
    }

    /**
     * Checks whether a Twitch username exists by querying the Twitch API.
     *
     * @param  string  $username  The Twitch username to check for existence.
     * @return bool True if the username exists, otherwise false.
     */
    public function checkUsernameExists(string $username): bool
    {
        return ! empty($this->getRequest()->get(config('twitch.url.api').'/users', [
            'login' => $username,
        ])->json('data'));
    }

    /**
     * Creates an EventSub subscription by sending a POST request to the API.
     *
     * @param  EventSub  $eventSub  Represents the event subscription type and its details.
     * @param  array  $condition  The conditions required to trigger the subscription.
     * @return array The response data from the API for the created subscription.
     */
    protected function createSubscription(EventSub $eventSub, array $condition): array
    {
        return $this->getRequest()
            ->post(config('twitch.url.api').'/eventsub/subscriptions', [
                'type' => $eventSub->value,
                'version' => $eventSub->getVersion(),
                'condition' => $condition,
                'transport' => $this->getWebhookTransport($eventSub->value),
            ])->json('data.0');
    }

    /**
     * Subscribes to an EventSub event for a specified broadcaster.
     *
     * Queries the database first for an existing subscription, if none is found creates a new one with the Twitch API
     *
     * @param  EventSub  $eventSub  Represents the event subscription type and its details.
     * @param  string  $username  The username of the broadcaster for whom the subscription is created.
     * @return string|null The ID of the created subscription, or the existing subscription ID if it already exists.
     *
     * @throws \Exception If the subscription could not be created due to a failure in the API response.
     */
    public function subscribe(EventSub $eventSub, string $username): ?string
    {
        $broadcasterId = $this->getBroadcasterId($username);

        $subscription = EventSubscription::query()
            ->where('broadcaster_id', $broadcasterId)
            ->where('type', $eventSub->value);

        if ($subscription->exists()) {
            Log::warning("Subscription \"$eventSub->value\" already exists for \"$username\"");

            return $subscription->first()->subscription_id;
        }

        $response = $this->createSubscription($eventSub, [
            'broadcaster_user_id' => $broadcasterId,
        ]);
        if (! $response || ! array_key_exists('id', $response)) {
            throw new \Exception('Could not subscribe to event',
                [$eventSub, $username, $broadcasterId, $response]);
        }

        Log::debug("Subscribed to \"$eventSub->value\" for \"$username\"");
        EventSubscription::query()->create([
            'broadcaster_id' => $broadcasterId,
            'type' => $eventSub->value,
            'subscription_id' => $response['id'],
            'status' => $response['status'],
        ]);

        return $response['id'];
    }

    /**
     * Deletes a subscription from the Twitch API
     *
     * @param  string  $subscriptionId  the id of the subscription to delete
     * @return bool True if subscription was deleted successfully
     */
    protected function deleteSubscription(string $subscriptionId): bool
    {
        return $this->getRequest()
            ->delete(config('twitch.url.api').'/eventsub/subscriptions', [
                'id' => $subscriptionId,
            ])->noContent();
    }

    /**
     * Unsubscribes from an EventSub subscription by removing it from the database
     * and sending a delete request to the API.
     *
     * @param  EventSub  $eventSub  Represents the event subscription type.
     * @param  string  $broadcasterId  The unique identifier of the broadcaster associated with the subscription.
     * @return bool True if the subscription was successfully deleted, false otherwise.
     */
    public function unsubscribe(EventSub $eventSub, string $broadcasterId): bool
    {
        $subscription = EventSubscription::query()
            ->where('broadcaster_id', $broadcasterId)
            ->where('type', $eventSub->value)
            ->first();

        if (! $subscription) {
            Log::warning("Subscription \"$eventSub->value\" does not exist for \"$broadcasterId\"");

            return false;
        }

        if ($this->deleteSubscription($subscription->subscription_id)) {

            Log::debug("Unsubscribed from \"$eventSub->value\" for \"$broadcasterId\"");

            return $subscription->delete();
        }

        return false;
    }

    /**
     * Retrieves the secret key used for the Twitch callback verification.
     *
     * @return string The secret key for the callback verification from the config
     */
    public function getCallbackSecret(): string
    {
        return config('twitch.callback.secret');
    }
}
