<?php

namespace App\Services;

use App\Services\Enums\EventSub;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TwitchService
{
    protected function __construct(
        protected ?string $client_id,
        protected ?string $client_secret,
        protected ?string $scopes = null,
        protected ?string $access_token = null,
    ) {
        $this->fetchAccessToken();
    }

    public static function make(): static
    {
        return new static(
            config('twitch.client.id'),
            config('twitch.client.secret'),
        );
    }

    public function scopes(string $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    protected function getRequest(): PendingRequest
    {
        $this->fetchAccessToken();

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->access_token,
            'Client-Id' => $this->client_id,
        ]);
    }

    protected function getWebhookTransport(string $event): array
    {
        return [
            'method' => 'webhook',
            'callback' => route('webhooks.twitch', compact('event')),
            'secret' => $this->getCallbackSecret(),
        ];
    }

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

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function getBroadcasterId(string $username): ?string
    {
        return Cache::rememberForever(
            "twitch:broadcaster:$username",
            fn () => $this->getRequest()->get(config('twitch.url.api').'/users', [
                'login' => $username,
            ])->json('data.0.id')
        );
    }

    public function checkUsernameExists(string $username): bool
    {
        return ! empty($this->getRequest()->get(config('twitch.url.api').'/users', [
            'login' => $username,
        ])->json('data'));
    }

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

    public function subscribe(EventSub $eventSub, string $username): ?string
    {
        $broadcasterId = $this->getBroadcasterId($username);

        return Cache::rememberForever("twitch:$broadcasterId:subscriptions:$eventSub->value", function () use ($eventSub, $username, $broadcasterId) {
            $response = $this->createSubscription($eventSub, [
                'broadcaster_user_id' => $broadcasterId,
            ]);

            if (! $response) {
                throw new \Exception('Could not subscribe to event', [$eventSub, $username, $broadcasterId, $response]);
            }

            return $response['id'];
        });
    }

    protected function deleteSubscription(string $subscriptionId): bool
    {
        return $this->getRequest()
            ->delete(config('twitch.url.api').'/eventsub/subscriptions', [
                'id' => $subscriptionId,
            ])->noContent();
    }

    public function unsubscribe(EventSub $eventSub, string $username): bool
    {
        $subscriptionId = Cache::get("twitch:$username:subscriptions:$eventSub->value");

        return $this->deleteSubscription($subscriptionId);
    }

    public function getCallbackSecret(): string
    {
        return config('twitch.callback.secret');
    }
}
