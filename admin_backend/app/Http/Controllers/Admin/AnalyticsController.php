<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;

class AnalyticsController extends Controller
{
    public function index()
    {
        $allIncidents = Incident::all();

        // Median response time (Pending -> Resolved), only from resolved incidents.
        // Median instead of mean so a single stuck/delayed incident can't blow out
        // the whole figure, especially in months with few incidents.
        $resolved = $allIncidents->whereNotNull('resolved_at');
        $responseMinutes = $resolved
            ->map(fn($i) => $i->reported_at->diffInMinutes($i->resolved_at))
            ->sort()
            ->values();
        $medianMinutes = null;
        if ($responseMinutes->count()) {
            $mid = intdiv($responseMinutes->count(), 2);
            $medianMinutes = $responseMinutes->count() % 2 !== 0
                ? $responseMinutes[$mid]
                : ($responseMinutes[$mid - 1] + $responseMinutes[$mid]) / 2;
        }
        $medianResponseTime = $medianMinutes !== null ? round($medianMinutes) . ' min' : 'N/A';

        // Top 5 barangays by incident count. Count and names are always the
        // same set (min(5, distinct barangays)), so there's no mismatch
        // between the headline number and the names shown under it.
        $byBarangay = $allIncidents->groupBy('barangay')->map->count()->sortDesc();
        $topBarangays = $byBarangay->take(5);
        $topBarangayNames = $topBarangays->keys()->implode(', ');

        $stats = [
            'median_response_time' => $medianResponseTime,
            'top_barangays' => [
                'count' => $topBarangays->count(),
                'names' => $topBarangayNames ?: 'No data yet',
            ],
        ];

        // Type distribution — only classified (resolved) incidents have a real type
        $classified = $allIncidents->where('type', '!=', 'Emergency');
        $totalClassified = $classified->count();
        $colors = ['#2C6BE0', '#D63939', '#C9A227', '#6C63FF', '#F76707', '#2FB344'];
        $typeData = $classified->groupBy('type')->map->count()->map(function ($count, $type) use ($totalClassified, $colors, $classified) {
            static $i = 0;
            return [
                'type' => $type,
                'percent' => $totalClassified > 0 ? round(($count / $totalClassified) * 100) : 0,
                'color' => $colors[$i++ % count($colors)],
            ];
        })->values();

        // Raw incident list for client-side filtering (day/month/year) + aggregation
        $incidents = $allIncidents->map(fn($i) => [
            'barangay' => $i->barangay,
            'type' => $i->type,
            'date' => $i->reported_at->format('Y-m-d'),
            'month' => $i->reported_at->format('M'),
            'month_num' => (int) $i->reported_at->format('n'),
            'year' => (int) $i->reported_at->format('Y'),
            'response_minutes' => $i->resolved_at
                ? $i->reported_at->diffInMinutes($i->resolved_at)
                : null,
        ]);

        // Years present in the data, for populating the Year filter dropdown
        $availableYears = $incidents->pluck('year')->unique()->sortDesc()->values();

        return view('admin.analytics.index', compact('stats', 'typeData', 'incidents', 'availableYears'));
    }
}