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
     * GET /api/public/incidents/{uuid}
     */
    public function show($uuid)
    {
        $incident = Incident::with(['assignedTo'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $incident->toArray();

        // ✅ Add duplicate detection for residents
        $duplicateCount = $this->getPotentialDuplicateCount($incident);
        $data['is_potential_duplicate'] = $duplicateCount > 0;
        $data['potential_duplicate_count'] = $duplicateCount;

        return response()->json($data);
    }

    /**
     * Legacy endpoint – returns empty list.
     */
    public function myIncidents(Request $request)
    {
        return response()->json([]);
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
            $timeWindow = 15; // minutes

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
