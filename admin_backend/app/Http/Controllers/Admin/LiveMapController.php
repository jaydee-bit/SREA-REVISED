<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use App\Models\User;

class LiveMapController extends Controller
{
    public function index()
    {
        $isSuperAdmin = backpack_user()->isSuperAdmin();
        $barangay = backpack_user()->barangay;

        $incidentQuery = Incident::with(['reporter', 'assignedTo.responderProfile'])
            ->whereIn('status', ['Pending', 'Responding', 'Escalated'])
            ->orderByDesc('reported_at');

        if (!$isSuperAdmin) {
            $incidentQuery->where('barangay', $barangay);
        }

        $incidents = $incidentQuery->get();

        $incidents->each(function ($incident) {
            $incident->nearby_count = $incident->findNearbyReports()->count();
        });

        // A barangay admin only needs their own pin/boundary on the map —
        // showing every barangay's marker (almost all reading zero) just
        // clutters a view that's supposed to be scoped to their own turf.
        $barangayQuery = Barangay::query();
        if (!$isSuperAdmin) {
            $barangayQuery->where('name', $barangay);
        }

        $barangays = $barangayQuery->get()->map(function ($b) use ($incidents) {
            return [
                'name' => $b->name,
                'lat' => (float) $b->latitude,
                'lng' => (float) $b->longitude,
                'incident_count' => $incidents->where('barangay', $b->name)
                    ->whereIn('status', ['Pending', 'Responding', 'Escalated'])
                    ->count(),
            ];
        });

        $responderQuery = User::where('role', 'responder')->with('responderProfile');
        if (!$isSuperAdmin) {
            $responderQuery->where('barangay', $barangay);
        }
        $responders = $responderQuery->get();

        return view('admin.live-map.index', compact('incidents', 'barangays', 'responders'));
    }
}