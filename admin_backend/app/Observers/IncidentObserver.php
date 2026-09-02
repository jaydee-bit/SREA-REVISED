<?php

namespace App\Observers;

use App\Models\Incident;
use App\Events\NewIncidentReported;

class IncidentObserver
{
    public function created(Incident $incident): void
    {
        $incident->loadMissing('assignedTo.responderProfile', 'escalatedBy');

        broadcast(new NewIncidentReported([
            'id' => $incident->id,
            'type' => $incident->type,
            'barangay' => $incident->barangay,
            'latitude' => (float) $incident->latitude,
            'longitude' => (float) $incident->longitude,
            'status' => $incident->status,
            'time' => 'Just now',
            'address' => $incident->address,
            'description' => $incident->description,
            'photo_path' => $incident->photo_path,
            'video_path' => $incident->video_path,
            'reporter_name' => $incident->reporter_name,
            'responder_notes' => $incident->responder_notes,
            'escalation_reason' => $incident->escalation_reason,
            'escalated_by' => $incident->escalatedBy?->name,
            'assigned_to' => $incident->assignedTo ? [
                'name' => $incident->assignedTo->name,
                'responder_profile' => $incident->assignedTo->responderProfile,
            ] : null,
            'nearby_count' => $incident->findNearbyReports()->count(),
        ]));
    }
}