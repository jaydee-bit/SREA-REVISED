<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\User;
use App\Events\NewIncidentReported;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class IncidentObserver
{
    public function created(Incident $incident): void
    {
        broadcast(new NewIncidentReported([
            'id' => $incident->id,
            'type' => $incident->type,
            'barangay' => $incident->barangay,
            'latitude' => (float) $incident->latitude,
            'longitude' => (float) $incident->longitude,
            'status' => $incident->status,
            'time' => 'Just now',
        ]));

        $this->notifyResponders($incident);
    }

    protected function notifyResponders(Incident $incident): void
    {
        $messaging = app('firebase.messaging');

        $responders = User::where('role', 'responder')
            ->whereNotNull('fcm_token')
            ->get();

        foreach ($responders as $responder) {
            try {
                 $message = CloudMessage::new()
                     ->withToken($responder->fcm_token)
                     ->withNotification(Notification::create(
                         'New Incident Reported',
                         "{$incident->type} in {$incident->barangay}"
                    ))
                    ->withData(['incident_id' => (string) $incident->id]);

                 $messaging->send($message);
            } catch (\Throwable $e) {
                Log::warning("FCM send failed for responder {$responder->id}: " . $e->getMessage());
            }
        }
    }
}