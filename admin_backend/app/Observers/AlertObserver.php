<?php

namespace App\Observers;

use App\Models\Alert;
use App\Models\Device;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class AlertObserver
{
    public function created(Alert $alert): void
    {
        $messaging = app('firebase.messaging');

        $devices = Device::whereNotNull('fcm_token')->get();

        foreach ($devices as $device) {
            try {
                $message = CloudMessage::new()
                    ->withToken($device->fcm_token)
                    ->withNotification(Notification::create(
                        $alert->title,
                        $alert->description
                    ))
                    ->withData(['type' => 'alert', 'alert_id' => (string) $alert->id]);

                $messaging->send($message);
            } catch (\Throwable $e) {
                Log::warning("Alert push failed for device {$device->id}: " . $e->getMessage());
            }
        }
    }
} 