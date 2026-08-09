<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockIncidents;

class ResponseMonitorController extends Controller
{
    public function index()
    {
        $incidents = MockIncidents::all();
        $waiting = collect($incidents)->where('status', 'waiting')->values();
        $active = collect($incidents)->where('status', 'responding')->values();

        return view('admin.response-monitor.index', compact('incidents', 'waiting', 'active'));
    }
}