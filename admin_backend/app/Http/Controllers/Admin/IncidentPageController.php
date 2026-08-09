<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockIncidents;

class IncidentPageController extends Controller
{
    public function index()
    {
        $incidents = MockIncidents::all();

        return view('admin.incidents.index', compact('incidents'));
    }
}