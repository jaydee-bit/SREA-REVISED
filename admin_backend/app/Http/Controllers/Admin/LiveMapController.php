<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockBarangays;
use App\Support\MockIncidents;

class LiveMapController extends Controller
{
    public function index()
    {
        $barangays = MockBarangays::withIncidentCounts();
        $incidents = MockIncidents::all();

        return view('admin.live-map.index', compact('barangays', 'incidents'));
    }
}