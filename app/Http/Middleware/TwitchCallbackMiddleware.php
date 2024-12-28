<?php

namespace App\Http\Middleware;

use App\Services\TwitchService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This middleware handles and validates Twitch EventSub callback requests.
 * It ensures the request is authentic using a secret
 * and Twitch-provided headers.
 *
 * @link https://dev.twitch.tv/docs/eventsub/handling-webhook-events/#verifying-the-event-message
 */
class TwitchCallbackMiddleware
{
    private const TWITCH_MESSAGE_ID = 'twitch-eventsub-message-id';

    private const TWITCH_MESSAGE_TIMESTAMP = 'twitch-eventsub-message-timestamp';

    private const TWITCH_MESSAGE_SIGNATURE = 'twitch-eventsub-message-signature';

    public function handle(Request $request, Closure $next): Response
    {
        $secret = app(TwitchService::class)->getCallbackSecret();

        $messageId = $request->header(self::TWITCH_MESSAGE_ID);
        $timestamp = $request->header(self::TWITCH_MESSAGE_TIMESTAMP);
        $signature = $request->header(self::TWITCH_MESSAGE_SIGNATURE);

        if (! $messageId || ! $timestamp || ! $signature) {
            return response()->json([
                'error' => 'Missing required headers',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $rawBody = $request->getContent();
        $message = $messageId.$timestamp.$rawBody;

        $calculatedSignature = 'sha256='.hash_hmac('sha256', $message, $secret);

        if (! hash_equals($calculatedSignature, $signature)) {
            return response()->json([
                'error' => 'Invalid signature',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
