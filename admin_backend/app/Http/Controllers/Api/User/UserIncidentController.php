<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class UserIncidentController extends Controller
{
    /**
     * Store a new anonymous emergency report.
     * POST /api/public/incidents
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'barangay' => 'required|string|max:255',
            'address' => 'required|string',
            'location_details' => 'nullable|string',
            'reporter_image' => 'nullable|string',
            'reporter_video' => 'nullable|string',
        ]);

        $incident = Incident::create([
            'user_id' => null,
            'type' => 'Emergency',
            'description' => 'Emergency report with selfie and video',
            'barangay' => $validated['barangay'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'address' => $validated['address'],
            'location_details' => $validated['location_details'] ?? null,

            // ✅ Now stores the uploaded file paths
            'photo_path' => $validated['reporter_image'] ?? null,
            'video_path' => $validated['reporter_video'] ?? null,

            'status' => 'Pending',
            'reported_at' => now(),
        ]);

        return response()->json([
            'id' => $incident->uuid,
            'incident' => $incident,
        ], 201);
    }

    /**
     * Get a single incident by UUID – for resident app.
     */
    public function show($uuid)
    {
        $incident = Incident::with(['assignedTo'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($incident);
    }

    /**
     * Legacy endpoint – returns empty list.
     */
    public function myIncidents(Request $request)
    {
        return response()->json([]);
    }
}
