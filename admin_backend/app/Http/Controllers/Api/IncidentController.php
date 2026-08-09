<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * List all incidents – with anonymous reporter handling.
     * GET /api/responder/incidents
     */
    public function index(Request $request)
    {
        $incidents = Incident::with(['assignedTo', 'reporter'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $incidents->map(function ($incident) {
            $item = $incident->toArray();

            // ✅ FIXED: Use reporter_name if available, otherwise fallback to "Anonymous"
            if ($incident->user_id === null) {
                $reporterName = $incident->reporter_name ?? 'Anonymous';
                $item['reporter'] = [
                    'name' => $reporterName,
                    'role' => null,
                    'is_verified' => false,
                ];
                // Also include the raw reporter_name field for direct access
                $item['reporter_name'] = $reporterName;
            }

            return $item;
        });

        return response()->json($data);
    }

    /**
     * Show an incident – with anonymous reporter handling.
     * GET /api/responder/incidents/{uuid}
     */
    public function show($uuid)
    {
        $incident = Incident::with(['assignedTo', 'reporter'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $incident->toArray();

        // ✅ FIXED: Use reporter_name if available, otherwise fallback to "Anonymous"
        if ($incident->user_id === null) {
            $reporterName = $incident->reporter_name ?? 'Anonymous';
            $data['reporter'] = [
                'name' => $reporterName,
                'role' => null,
                'is_verified' => false,
            ];
            // Also include the raw reporter_name field for direct access
            $data['reporter_name'] = $reporterName;
        }

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
     * Reassign an incident to another responder.
     * POST /api/responder/incidents/{uuid}/reassign
     */
    public function reassign(Request $request, $uuid)
    {
        $validated = $request->validate([
            'responder_id' => 'required|exists:users,id',
        ]);

        $incident = Incident::where('uuid', $uuid)->firstOrFail();
        $incident->assigned_to = $validated['responder_id'];
        $incident->save();

        return response()->json([
            'message' => 'Incident reassigned successfully',
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

        // Update incident details
        $incident->type = $validated['type'];
        $incident->description = $validated['description'];
        $incident->resolution_notes = $validated['resolution_notes'];
        $incident->reporter_name = $validated['reporter_name'] ?? null;

        // Mark as resolved
        $incident->status = 'Resolved';
        $incident->resolved_at = now();
        $incident->save();

        return response()->json([
            'message' => 'Incident resolved successfully',
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
}
