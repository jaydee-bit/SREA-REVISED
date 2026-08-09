<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockTrafficAdvisories;

class TrafficAdvisoryController extends Controller
{
    public function index()
    {
        $advisories = MockTrafficAdvisories::all();

        return view('admin.alerts.traffic-advisory', compact('advisories'));
    }
}