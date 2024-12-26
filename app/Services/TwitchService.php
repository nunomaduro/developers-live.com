<?php

namespace App\Services;

use App\Http\Resources\CreateEventSubResource;
use App\Services\Enums\EventSub;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TwitchService
{
    protected const ID_URL = 'https://id.twitch.tv/oauth2/token';
    protected const API_URL = 'https://api.twitch.tv/helix/';

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
        if (!$this->client_id || !$this->client_secret) {
            throw new \Exception('Twitch client id or secret not set.');
        }

        if (!Cache::has('twitch:accessToken')) {
            $response = Http::post(self::ID_URL, [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials',
            ])->json();

            $this->access_token = Cache::remember(
                'twitch:accessToken',
                now()->addMillis($response['expires_in']),
                fn() => $response['access_token'] ?? null,
            );
        }
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function getBroadcasterId(string $username): ?string
    {
        return Cache::rememberForever(
            "twitch:broadcaster:$username",
            fn() => $this->getRequest()->get(self::API_URL.'users', [
                'login' => $username,
            ])->json('data.0.id')
        );
    }

    protected function createSubscription(EventSub $eventSub, array $condition): array
    {
        return $this->getRequest()
            ->post(self::API_URL.'eventsub/subscriptions', [
                'type' => $eventSub->value,
                'version' => $eventSub->getVersion(),
                'condition' => $condition,
                'transport' => $this->getWebhookTransport($eventSub->value),
            ])->json('data.0');
    }

    public function subscribe(EventSub $eventSub, string $username): bool
    {
        $broadcasterId = $this->getBroadcasterId($username);

        $response = $this->createSubscription($eventSub, [
            'broadcaster_user_id' => $broadcasterId,
        ]);

        Cache::rememberForever("twitch:$broadcasterId:subscriptions:$eventSub->value", fn() => $response['id']);

        return true;
    }

    protected function deleteSubscription(string $subscriptionId): bool
    {
        return $this->getRequest()
            ->delete(self::API_URL.'eventsub/subscriptions', [
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
