<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockBarangays;
use App\Support\MockIncidents;
use App\Support\MockResponders;

class ResponseMonitorController extends Controller
{
    public function index()
    {
        $incidents = MockIncidents::all();
        $barangays = MockBarangays::withIncidentCounts();
        $responders = MockResponders::all();
        $waiting = collect($incidents)->where('status', 'waiting')->values();
        $active = collect($incidents)->where('status', 'responding')->values();

        return view('admin.response-monitor.index', compact('incidents', 'barangays', 'responders', 'waiting', 'active'));
    }
}