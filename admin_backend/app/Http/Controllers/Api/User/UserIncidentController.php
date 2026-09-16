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
            'latitude'         => 'required|numeric',
            'longitude'        => 'required|numeric',
            'barangay'         => 'nullable|string|max:255',
            'address'          => 'required|string',
            'location_details' => 'nullable|string',
            'reporter_image'   => 'nullable|string',
            'reporter_video'   => 'nullable|string',
            // ✅ NEW – reporter identity fields sent by the Flutter app.
            // Kept nullable so anonymous submissions still work.
            'reporter_name'    => 'nullable|string|max:255',
            'contact_number'   => 'nullable|string|max:20',
        ]);

        $incident = Incident::create([
            'user_id'          => null,
            'type'             => 'Emergency',
            'description'      => 'Emergency report with selfie and video',
            'barangay'         => $validated['barangay'] ?? '',
            'latitude'         => $validated['latitude'],
            'longitude'        => $validated['longitude'],
            'address'          => $validated['address'],
            'location_details' => $validated['location_details'] ?? null,
            'photo_path'       => $validated['reporter_image'] ?? null,
            'video_path'       => $validated['reporter_video'] ?? null,
            // ✅ NEW – persist reporter info so the detail screen can
            // display "Reported by" and "Contact" instead of Anonymous.
            'reporter_name'    => $validated['reporter_name'] ?? null,
            'contact_number'   => $validated['contact_number'] ?? null,
            'status'           => 'Pending',
            'reported_at'      => now(),
        ]);

        // ✅ Reload with relation so assigned_to serializes correctly.
        $incident->load('assignedTo');

        // ✅ Duplicate detection – same logic as show() so the
        // duplicate banner also appears immediately after submission.
        $duplicateCount = $this->getPotentialDuplicateCount($incident);

        $data = $incident->toArray();
        $data['assigned_to']              = $incident->assignedTo
            ? ['name' => $incident->assignedTo->name]
            : null;
        $data['is_potential_duplicate']   = $duplicateCount > 0;
        $data['potential_duplicate_count'] = $duplicateCount;
        $data['escalated_by_name']        = optional($incident->escalatedBy)->name;

        return response()->json([
            'id'       => $incident->uuid,
            'incident' => $data,
        ], 201);
    }

    /**
     * Get a single incident by UUID – for resident app.
     * GET /api/public/incidents/{uuid}
     */
    public function show($uuid)
    {
        $incident = Incident::with(['assignedTo', 'escalatedBy'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $incident->toArray();

        // ✅ Ensure the Flutter model can read these keys:
        //    json['assigned_to']?['name']  and  json['escalated_by_name']
        $data['assigned_to'] = $incident->assignedTo
            ? ['name' => $incident->assignedTo->name]
            : null;
        $data['escalated_by_name'] = optional($incident->escalatedBy)->name;

        // ✅ Duplicate detection for residents
        $duplicateCount = $this->getPotentialDuplicateCount($incident);
        $data['is_potential_duplicate']    = $duplicateCount > 0;
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
            $timeWindow  = 15;      // minutes

            $lat1 = deg2rad($incident->latitude);
            $lon1 = deg2rad($incident->longitude);

            // ✅ Match the statuses the mobile app actually uses.
            // The app treats 'in_progress' and 'responding' as
            // synonyms, and adds 'escalated' as a live status.
            $activeStatuses = ['Pending', 'Responding', 'In Progress', 'Escalated'];

            return Incident::where('id', '!=', $incident->id)
                ->where('barangay', $incident->barangay)
                ->whereIn('status', $activeStatuses)
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