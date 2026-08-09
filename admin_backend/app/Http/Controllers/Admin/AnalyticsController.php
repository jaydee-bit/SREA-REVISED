<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockAnalytics;

class AnalyticsController extends Controller
{
    public function index()
    {
        $stats = MockAnalytics::stats();
        $barangayData = MockAnalytics::incidentsPerBarangay();
        $typeData = MockAnalytics::typeDistribution();
        $trendData = MockAnalytics::monthlyTrend();

        return view('admin.analytics.index', compact('stats', 'barangayData', 'typeData', 'trendData'));
    }
}