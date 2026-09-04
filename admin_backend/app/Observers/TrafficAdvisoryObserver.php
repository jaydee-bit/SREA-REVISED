<?php

namespace App\Observers;

use App\Models\TrafficAdvisory;
use App\Models\Device;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class TrafficAdvisoryObserver
{
    public function created(TrafficAdvisory $advisory): void
    {
        $messaging = app('firebase.messaging');

        $devices = Device::whereNotNull('fcm_token')->get();

        foreach ($devices as $device) {
            try {
                $message = CloudMessage::new()
                    ->withToken($device->fcm_token)
                    ->withNotification(Notification::create(
                        $advisory->title,
                        $advisory->description
                    ))
                    ->withData(['type' => 'traffic', 'advisory_id' => (string) $advisory->id]);

                $messaging->send($message);
            } catch (\Throwable $e) {
                Log::warning("Traffic advisory push failed for device {$device->id}: " . $e->getMessage());
            }
        }
    }
}