<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockBarangays;
use App\Support\MockIncidents;
use App\Support\MockResponders;

class LiveMapController extends Controller
{
    public function index()
    {
        $barangays = MockBarangays::withIncidentCounts();
        $incidents = MockIncidents::all();
        $responders = MockResponders::all();

        return view('admin.live-map.index', compact('barangays', 'incidents', 'responders'));
    }
}