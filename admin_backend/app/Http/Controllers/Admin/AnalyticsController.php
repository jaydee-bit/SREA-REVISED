<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;

class AnalyticsController extends Controller
{
    public function index()
    {
        $allIncidents = Incident::all();

        // Avg response time (Pending -> Resolved), only from resolved incidents
        $resolved = $allIncidents->whereNotNull('resolved_at');
        $avgMinutes = $resolved->avg(fn ($i) => $i->reported_at->diffInMinutes($i->resolved_at));
        $avgResponseTime = $avgMinutes ? round($avgMinutes) . ' min' : 'N/A';

        // High risk barangays: top 3 by incident count
        $byBarangay = $allIncidents->groupBy('barangay')->map->count()->sortDesc();
        $highRiskNames = $byBarangay->take(3)->keys()->implode(', ');

        $stats = [
            'avg_response_time' => $avgResponseTime,
            'high_risk_barangays' => [
                'count' => $byBarangay->count(),
                'names' => $highRiskNames ?: 'No data yet',
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

        // Raw incident list for client-side date filtering + aggregation
        $incidents = $allIncidents->map(fn ($i) => [
            'barangay' => $i->barangay,
            'type' => $i->type,
            'date' => $i->reported_at->format('Y-m-d'),
            'month' => $i->reported_at->format('M'),
        ]);

        return view('admin.analytics.index', compact('stats', 'typeData', 'incidents'));
    }
}