<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentPageController extends Controller
{
    public function index()
    {
        $user = backpack_user();

        // Options for the "Request Assistance" barangay picker. A super
        // admin isn't scoped to one barangay and can request on behalf
        // of any incident's own barangay (see BarangayAssistanceRequestController::store,
        // which only blocks a non-super-admin from targeting their own
        // barangay), so they get the full list rather than "all but mine".
        $barangays = $user->isSuperAdmin()
            ? Barangay::orderBy('name')->pluck('name')
            : Barangay::where('name', '!=', $user->barangay)->orderBy('name')->pluck('name');

        return view('admin.incidents.index', compact('barangays'));
    }

    /**
     * JSON data endpoint for the Incidents table — powers AJAX
     * pagination, status filtering, and search without a full
     * page reload.
     * GET /admin/incidents/data
     */
    public function data(Request $request)
    {
        $query = Incident::with(['reporter', 'assignedTo'])
            ->orderByDesc('reported_at');

        if (!backpack_user()->isSuperAdmin()) {
            $query->where('barangay', backpack_user()->barangay);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('type', 'like', "%{$term}%")
                  ->orWhere('barangay', 'like', "%{$term}%")
                  ->orWhere('reporter_name', 'like', "%{$term}%")
                  ->orWhere('id', 'like', "%{$term}%");
            });
        }

        $incidents = $query->paginate(10);

        $incidents->getCollection()->each(function ($incident) {
            $incident->nearby_count = $incident->findNearbyReports()->count();
        });

        return response()->json($incidents);
    
    }

    public function reject(Request $request, Incident $incident)
    {
        if (!backpack_user()->isSuperAdmin() && $incident->barangay !== backpack_user()->barangay) {
            return response()->json(['message' => 'Unauthorized. This incident is outside your barangay.'], 403);
        }

        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        $incident->update([
            'status' => 'Rejected',
            'resolution_notes' => 'Rejected: ' . $request->reason,
        ]);

        return response()->json(['ok' => true]);
    }

    public function exportCsv()
    {
        $query = Incident::orderByDesc('reported_at');
        if (!backpack_user()->isSuperAdmin()) {
            $query->where('barangay', backpack_user()->barangay);
        }
        $incidents = $query->get();

        $filename = 'srea-incidents-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($incidents) {
            $file = fopen('php://output', 'w');
           fputcsv($file, ['ID', 'Type', 'Barangay', 'Reporter', 'Contact', 'Status', 'Reported At']);
           foreach ($incidents as $inc) {
               fputcsv($file, [
                   $inc->id,
                   $inc->type,
                   $inc->barangay,
                   $inc->reporter_name ?? 'Anonymous',
                   $inc->contact_number ?? '-',
                   $inc->status,
                   $inc->reported_at->format('M j, Y g:i A'),
               ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $query = Incident::orderByDesc('reported_at');
        if (!backpack_user()->isSuperAdmin()) {
            $query->where('barangay', backpack_user()->barangay);
        }
        $incidents = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.incidents.export-pdf', compact('incidents'));

        return $pdf->download('srea-incidents-' . now()->format('Y-m-d') . '.pdf');
    }
}