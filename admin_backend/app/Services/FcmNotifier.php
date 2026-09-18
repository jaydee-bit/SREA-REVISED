<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmNotifier
{
    /**
     * Send a push to a single device token.
     *
     * Silently no-ops if the token is missing — plenty of accounts
     * (especially anonymous reporters who never registered a device, or
     * a responder who hasn't logged in on a new phone yet) simply won't
     * have one. That's fine; the resident app's existing polling remains
     * the fallback for those, this is a live-update improvement on top,
     * not a replacement that can leave someone with no update path.
     *
     * $data values are cast to strings because FCM's data payload only
     * accepts string values, even for things like an incident ID.
     */
    public static function send(?string $token, string $title, string $body, array $data = []): void
    {
        if (!$token) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->toToken($token)
                ->withNotification(FcmNotification::create($title, $body))
                ->withData(array_map('strval', $data));

            Firebase::messaging()->send($message);
        } catch (\Throwable $e) {
            // A dead/expired token, a malformed token, or a Firebase
            // outage shouldn't break the actual incident action (accept/
            // resolve/reject/dispatch) that triggered this push — log it
            // and let the real action's response still succeed.
            logger()->warning('FCM send failed: ' . $e->getMessage());
        }
    }
}