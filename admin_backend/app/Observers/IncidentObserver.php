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
            'photo_path' => $incident->photo_path,
            'video_path' => $incident->video_path,
            'reporter_name' => $incident->reporter_name,
            'contact_number' => $incident->contact_number,
            'address' => $incident->address,
            'description' => $incident->description,
            'nearby_count' => $incident->findNearbyReports()->count(),
            // Brand-new incident — never assigned or annotated yet, so
            // these stay null rather than being omitted, matching the
            // shape openIncident() in index.blade.php expects.
            'assigned_to' => null,
            'responder_notes' => null,

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
                    ->withData([
                        'incident_uuid' => $incident->uuid,
                        'status' => $incident->status,
                    ]);

                 $messaging->send($message);
            } catch (\Throwable $e) {
                Log::warning("FCM send failed for responder {$responder->id}: " . $e->getMessage());
            }
        }
    }
}