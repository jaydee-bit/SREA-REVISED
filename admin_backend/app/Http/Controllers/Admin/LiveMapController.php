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
        $incidents = Incident::with(['reporter', 'assignedTo.responderProfile'])
            ->whereIn('status', ['Pending', 'Responding', 'Escalated'])
            ->orderByDesc('reported_at')
            ->get();

        $incidents->each(function ($incident) {
            $incident->nearby_count = $incident->findNearbyReports()->count();
        });

        $barangays = Barangay::all()->map(function ($b) use ($incidents) {
            return [
                'name' => $b->name,
                'lat' => (float) $b->latitude,
                'lng' => (float) $b->longitude,
                'incident_count' => $incidents->where('barangay', $b->name)
                    ->whereIn('status', ['Pending', 'Responding', 'Escalated'])
                    ->count(),
            ];
        });

        $responders = User::where('role', 'responder')->with('responderProfile')->get();

        return view('admin.live-map.index', compact('incidents', 'barangays', 'responders'));
    }
}   