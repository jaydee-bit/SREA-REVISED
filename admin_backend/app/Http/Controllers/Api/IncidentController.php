<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * List all incidents – with anonymous reporter handling and duplicate detection.
     * GET /api/responder/incidents
     */
    public function index(Request $request)
    {
        // ✅ Added 'escalatedBy' to eager load the escalator's details
        $incidents = Incident::with(['assignedTo', 'reporter', 'escalatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $incidents->map(function ($incident) {
            $item = $incident->toArray();

            if ($incident->user_id === null) {
                $reporterName = $incident->reporter_name ?? 'Anonymous';
                $item['reporter'] = [
                    'name' => $reporterName,
                    'role' => null,
                    'is_verified' => false,
                ];
                $item['reporter_name'] = $reporterName;
            }

            // Duplicate detection
            $duplicateCount = $this->getPotentialDuplicateCount($incident);
            $item['is_potential_duplicate'] = $duplicateCount > 0;
            $item['potential_duplicate_count'] = $duplicateCount;

            return $item;
        });

        return response()->json($data);
    }

    /**
     * Show an incident – with anonymous reporter handling and duplicate detection.
     * GET /api/responder/incidents/{uuid}
     */
    public function show($uuid)
    {
        // ✅ Added 'escalatedBy' to eager load the escalator's details
        $incident = Incident::with(['assignedTo', 'reporter', 'escalatedBy'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $incident->toArray();

        if ($incident->user_id === null) {
            $reporterName = $incident->reporter_name ?? 'Anonymous';
            $data['reporter'] = [
                'name' => $reporterName,
                'role' => null,
                'is_verified' => false,
            ];
            $data['reporter_name'] = $reporterName;
        }

        // Duplicate detection
        $duplicateCount = $this->getPotentialDuplicateCount($incident);
        $data['is_potential_duplicate'] = $duplicateCount > 0;
        $data['potential_duplicate_count'] = $duplicateCount;

        return response()->json($data);
    }

    /**
     * Assign a responder to an incident.
     * POST /api/responder/incidents/{uuid}/respond
     */
    public function respond(Request $request, $uuid)
    {
        $validated = $request->validate([
            'responder_id' => 'required|exists:users,id',
        ]);

        $incident = Incident::where('uuid', $uuid)->firstOrFail();
        $incident->assigned_to = $validated['responder_id'];
        $incident->status = 'Responding';
        $incident->save();

        return response()->json([
            'message' => 'Incident assigned successfully',
            'incident' => $incident,
        ]);
    }

    /**
     * Reassign an incident to admin (unassign) with a reason.
     * POST /api/responder/incidents/{uuid}/reassign
     */
    public function reassign(Request $request, $uuid)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $incident = Incident::where('uuid', $uuid)->firstOrFail();

        // Unassign the incident and escalate
        $incident->assigned_to = null;
        $incident->status = 'Escalated';
        $incident->escalation_reason = $validated['reason'];
        $incident->escalated_by = $request->user()->id;
        $incident->escalated_at = now();
        $incident->save();

        return response()->json([
            'message' => 'Incident reassigned to admin successfully',
            'incident' => $incident,
        ]);
    }

    /**
     * Resolve an incident – with type, description, notes, and optional reporter name.
     * POST /api/responder/incidents/{uuid}/resolve
     */
    public function resolve(Request $request, $uuid)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:Fire,Medical,Flood,Accident,Calamity,Crime,Traffic,Other',
            'description' => 'required|string|min:10',
            'resolution_notes' => 'required|string|min:10',
            'reporter_name' => 'nullable|string|max:255',
        ]);

        $incident = Incident::where('uuid', $uuid)->firstOrFail();

        $incident->type = $validated['type'];
        $incident->description = $validated['description'];
        $incident->resolution_notes = $validated['resolution_notes'];
        $incident->reporter_name = $validated['reporter_name'] ?? null;

        $incident->status = 'Resolved';
        $incident->resolved_at = now();
        $incident->save();

        return response()->json([
            'message' => 'Incident resolved successfully',
            'incident' => $incident,
        ]);
    }

    /**
     * Reject an incident – Admin only.
     * POST /api/responder/incidents/{uuid}/reject
     */
    public function reject(Request $request, $uuid)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $incident = Incident::where('uuid', $uuid)->firstOrFail();

        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Only admins can reject incidents.',
            ], 403);
        }

        $incident->status = 'Rejected';
        $incident->resolution_notes = $validated['reason'];
        $incident->resolved_at = now();
        $incident->save();

        return response()->json([
            'message' => 'Incident rejected successfully',
            'incident' => $incident,
        ]);
    }

    /**
     * Add notes to an incident.
     * POST /api/responder/incidents/{uuid}/notes
     */
    public function updateNotes(Request $request, $uuid)
    {
        $validated = $request->validate([
            'responder_notes' => 'required|string',
        ]);

        $incident = Incident::where('uuid', $uuid)->firstOrFail();
        $incident->responder_notes = $validated['responder_notes'];
        $incident->save();

        return response()->json([
            'message' => 'Notes added successfully',
            'incident' => $incident,
        ]);
    }

    // ─── Helper: Count potential duplicates ──────────────────────────────

    /**
     * Count similar incidents nearby using Haversine formula.
     * Same barangay, within 100m, within 15 min.
     */
    protected function getPotentialDuplicateCount($incident)
    {
        try {
            $earthRadius = 6371000; // meters
            $timeWindow = 30; // minutes

            $lat1 = deg2rad($incident->latitude);
            $lon1 = deg2rad($incident->longitude);

            return Incident::where('id', '!=', $incident->id)
                ->where('barangay', $incident->barangay)
                ->whereIn('status', ['Pending', 'Responding'])
                ->whereRaw(
                    "
                    (
                        {$earthRadius} * acos(
                            cos({$lat1}) * cos(radians(latitude)) * 
                            cos(radians(longitude) - {$lon1}) + 
                            sin({$lat1}) * sin(radians(latitude))
                        )
                    ) < 100
                    "
                )
                ->where('reported_at', '>=', now()->subMinutes($timeWindow))
                ->count();
        } catch (\Exception $e) {
            logger()->error('Duplicate detection failed: ' . $e->getMessage());
            return 0;
        }
    }
}
