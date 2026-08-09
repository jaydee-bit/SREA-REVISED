<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockResponderReports;

class ResponderReportController extends Controller
{
    public function index()
    {
        $reports = MockResponderReports::all();

        return view('admin.responder-reports.index', compact('reports'));
    }
}