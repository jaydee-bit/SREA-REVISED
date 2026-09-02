<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;

class IncidentPageController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['reporter', 'assignedTo'])
            ->orderByDesc('reported_at')
            ->get();

        $incidents->each(function ($incident) {
            $incident->nearby_count = $incident->findNearbyReports()->count();
        });

        return view('admin.incidents.index', compact('incidents'));
    }

    public function reject(Request $request, Incident $incident)
    {
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
        $incidents = Incident::orderByDesc('reported_at')->get();

        $filename = 'srea-incidents-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($incidents) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Type', 'Barangay', 'Reporter', 'Status', 'Reported At']);
            foreach ($incidents as $inc) {
                fputcsv($file, [
                    $inc->id,
                    $inc->type,
                    $inc->barangay,
                    $inc->reporter_name ?? 'Anonymous',
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
        $incidents = Incident::orderByDesc('reported_at')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.incidents.export-pdf', compact('incidents'));

        return $pdf->download('srea-incidents-' . now()->format('Y-m-d') . '.pdf');
    }
}