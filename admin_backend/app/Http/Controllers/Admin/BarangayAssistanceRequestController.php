<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\BarangayAssistanceRequest;
use App\Models\Incident;
use App\Models\User;
use App\Services\FcmNotifier;
use Illuminate\Http\Request;

class BarangayAssistanceRequestController extends Controller
{
    /**
     * Incoming = requests directed at this admin's own barangay.
     * Sent = requests this admin's barangay has made to others.
     * A super admin sees everything incoming (oversight), and nothing
     * of their own to send — they aren't scoped to one barangay.
     */
    public function index()
    {
        $user = backpack_user();

        if ($user->isSuperAdmin()) {
            $incoming = BarangayAssistanceRequest::with(['incident', 'requester'])
                ->orderByDesc('created_at')
                ->get();
            $sent = collect();
        } else {
            $incoming = BarangayAssistanceRequest::with(['incident', 'requester'])
                ->where('target_barangay', $user->barangay)
                ->orderByDesc('created_at')
                ->get();

            $sent = BarangayAssistanceRequest::with(['incident', 'responder'])
                ->where('requesting_barangay', $user->barangay)
                ->orderByDesc('created_at')
                ->get();
        }

        // Incidents this admin could plausibly ask for help on — anything
        // still open in their own barangay. Resolved/Rejected incidents
        // are done, so they're excluded rather than cluttering the picker.
        $ownOpenIncidents = $user->isSuperAdmin()
            ? collect()
            : Incident::where('barangay', $user->barangay)
                ->whereIn('status', ['Pending', 'Responding', 'Escalated'])
                ->orderByDesc('reported_at')
                ->get();

        $otherBarangays = $user->isSuperAdmin()
            ? collect()
            : Barangay::where('name', '!=', $user->barangay)->orderBy('name')->pluck('name');

        return view('admin.assistance-requests.index', compact(
            'incoming', 'sent', 'ownOpenIncidents', 'otherBarangays'
        ));
    }

    /**
     * Create a new assistance request for one of this admin's own
     * incidents, targeting a specific other barangay.
     */
    public function store(Request $request, Incident $incident)
    {
        $user = backpack_user();

        if (!$user->isSuperAdmin() && $incident->barangay !== $user->barangay) {
            return response()->json(['message' => 'Unauthorized. This incident is outside your barangay.'], 403);
        }

        $validated = $request->validate([
            'target_barangay' => 'required|string',
        ]);

        if ($validated['target_barangay'] === $incident->barangay) {
            return response()->json(['message' => 'You cannot request assistance from your own barangay.'], 422);
        }

        // Don't allow a second pending request to stack on top of one
        // that hasn't been answered yet — the admin should wait for a
        // decline (and can then pick a different barangay) rather than
        // firing off several simultaneous asks for the same incident.
        $alreadyPending = BarangayAssistanceRequest::where('incident_id', $incident->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return response()->json(['message' => 'This incident already has a pending assistance request.'], 422);
        }

        $assistanceRequest = BarangayAssistanceRequest::create([
            'incident_id' => $incident->id,
            'requesting_barangay' => $incident->barangay,
            'target_barangay' => $validated['target_barangay'],
            'requested_by' => $user->id,
            'status' => 'pending',
        ]);

        $targetAdmins = User::where('role', 'admin')
            ->where('is_super_admin', false)
            ->where('barangay', $validated['target_barangay'])
            ->get();

        foreach ($targetAdmins as $admin) {
            FcmNotifier::send(
                $admin->fcm_token,
                'Assistance Requested',
                "{$incident->barangay} is requesting help with incident #{$incident->id} ({$incident->type}).",
                ['assistance_request_id' => (string) $assistanceRequest->id]
            );
        }

        return response()->json(['ok' => true, 'request' => $assistanceRequest]);
    }

    public function accept(Request $request, BarangayAssistanceRequest $assistance_request)
    {
        $user = backpack_user();

        // Accepting means "my barangay will take this on" — that's only a
        // meaningful action for the barangay admin the request was actually
        // sent to. A super admin has no barangay to accept on behalf of, so
        // this is blocked outright rather than just hidden in the UI (the
        // monitoring table never renders an Accept button, but this closes
        // the gap for anyone hitting the endpoint directly).
        if ($user->isSuperAdmin()) {
            return response()->json(['message' => 'Super admins cannot accept requests on behalf of a barangay.'], 403);
        }

        if ($assistance_request->target_barangay !== $user->barangay) {
            return response()->json(['message' => 'Unauthorized. This request was not sent to your barangay.'], 403);
        }

        if ($assistance_request->status !== 'pending') {
            return response()->json(['message' => 'This request has already been responded to.'], 409);
        }

        $assistance_request->update([
            'status' => 'accepted',
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        // This is the actual handoff: reassigning the incident's own
        // barangay to the accepting admin's barangay. Every controller
        // we've scoped (Incidents, Response Monitor, Live Map, Analytics,
        // Audit Trail) filters purely on incidents.barangay, so this one
        // change is what makes the incident "behave like any other
        // incident scoped to them" — no special-casing needed elsewhere.
        $incident = $assistance_request->incident;
        $incident->update(['barangay' => $assistance_request->target_barangay]);

        $requestingAdmins = User::where('role', 'admin')
            ->where('is_super_admin', false)
            ->where('barangay', $assistance_request->requesting_barangay)
            ->get();

        foreach ($requestingAdmins as $admin) {
            FcmNotifier::send(
                $admin->fcm_token,
                'Assistance Accepted',
                "{$assistance_request->target_barangay} accepted your request for incident #{$incident->id}.",
                ['incident_uuid' => $incident->uuid, 'status' => $incident->status]
            );
        }

        return response()->json(['ok' => true]);
    }

    public function decline(Request $request, BarangayAssistanceRequest $assistance_request)
    {
        $user = backpack_user();

        // Unlike accept(), a super admin IS allowed here — this is the
        // "Decline (Wrong Barangay)" correction path from the monitoring
        // table, letting them close out a misrouted request on behalf of
        // whichever barangay it was mistakenly sent to.
        if (!$user->isSuperAdmin() && $assistance_request->target_barangay !== $user->barangay) {
            return response()->json(['message' => 'Unauthorized. This request was not sent to your barangay.'], 403);
        }

        if ($assistance_request->status !== 'pending') {
            return response()->json(['message' => 'This request has already been responded to.'], 409);
        }

        // Same min:10 pattern already used by Incidents' Reject flow —
        // keeping validation consistent across every "explain why" action
        // in the app rather than inventing a different rule here.
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $assistance_request->update([
            'status' => 'declined',
            'decline_reason' => $validated['reason'],
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        $requestingAdmins = User::where('role', 'admin')
            ->where('is_super_admin', false)
            ->where('barangay', $assistance_request->requesting_barangay)
            ->get();

        $incident = $assistance_request->incident;

        foreach ($requestingAdmins as $admin) {
            FcmNotifier::send(
                $admin->fcm_token,
                'Assistance Declined',
                "{$assistance_request->target_barangay} declined your request for incident #{$incident->id}: {$validated['reason']}",
                ['incident_uuid' => $incident->uuid]
            );
        }

        return response()->json(['ok' => true]);
    }
}