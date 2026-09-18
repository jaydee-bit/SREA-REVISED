<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;

class ResponseMonitorController extends Controller
{
    public function index()
    {
        $isSuperAdmin = backpack_user()->isSuperAdmin();
        $barangay = backpack_user()->barangay;

        $incidentQuery = Incident::with(['reporter', 'assignedTo.responderProfile'])
            ->orderByDesc('reported_at');

        if (!$isSuperAdmin) {
            $incidentQuery->where('barangay', $barangay);
        }

        $incidents = $incidentQuery->get();

        $waiting = $incidents->where('status', 'Escalated')->values();
        $active = $incidents->where('status', 'Responding')->values();

        // A barangay admin should only be offered responders they could
        // actually dispatch (see the same barangay check in dispatch()
        // below) — otherwise the dropdown would list every standby
        // responder municipality-wide, most of whom this admin has no
        // authority to assign.
        $standbyQuery = User::where('role', 'responder')
            ->whereHas('responderProfile', fn ($q) => $q->where('current_status', 'Standby'))
            ->with('responderProfile');

        if (!$isSuperAdmin) {
            $standbyQuery->where('barangay', $barangay);
        }

        $standbyResponders = $standbyQuery->get();

        return view('admin.response-monitor.index', compact('incidents', 'waiting', 'active', 'standbyResponders'));
    }

    public function dispatch(Request $request, Incident $incident)
    {
        if (!backpack_user()->isSuperAdmin() && $incident->barangay !== backpack_user()->barangay) {
            return response()->json(['message' => 'Unauthorized. This incident is outside your barangay.'], 403);
        }

        $request->validate([
            'responder_id' => 'required|exists:users,id',
        ]);

        // Also confirm the chosen responder actually belongs to this
        // admin's barangay — without this, the request-level check above
        // only stops the wrong INCIDENT, not a barangay admin dispatching
        // some other barangay's responder via a crafted request.
        if (!backpack_user()->isSuperAdmin()) {
            $responderInScope = User::where('id', $request->responder_id)
                ->where('barangay', backpack_user()->barangay)
                ->exists();

            if (!$responderInScope) {
                return response()->json(['message' => 'Unauthorized. This responder is outside your barangay.'], 403);
            }
        }

        $incident->update([
            'assigned_to' => $request->responder_id,
            'status' => 'Responding',
        ]);

        $responder = User::find($request->responder_id);
        $responder->responderProfile()->update(['current_status' => 'Deployed']);

        // Broadcast the same event/shape IncidentController::respond() uses
        // when a responder accepts in-app, so admin-side dispatch behaves
        // identically: the Live Map gets the responder's pin immediately,
        // and the global siren for this incident stops — instead of silently
        // drifting out of sync until someone refreshes the page.
        $incident->load('assignedTo.responderProfile');
        event(new \App\Events\ResponderAssigned($incident->toArray()));

        // Two different audiences, two different messages — the responder
        // needs to know they now have a new job (their incidents list has
        // no other way to find out except this push or a manual refresh),
        // while the reporter just needs a status update.
        \App\Services\FcmNotifier::send(
            $responder->fcm_token,
            'New Incident Assigned',
            "You've been assigned to incident #{$incident->id} in {$incident->barangay}.",
            ['incident_uuid' => $incident->uuid, 'status' => $incident->status]
        );

        \App\Services\FcmNotifier::send(
            $incident->reporter?->fcm_token,
            'Responder Dispatched',
            "A responder is on the way for your report (#{$incident->id}).",
            ['incident_uuid' => $incident->uuid, 'status' => $incident->status]
        );

        return response()->json(['ok' => true]);
    }
}