<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrafficAdvisory;
use Illuminate\Http\Request;

class TrafficAdvisoryController extends Controller
{
    public function index()
    {
        $advisories = TrafficAdvisory::with('creator')->latest()->get();

        return view('admin.alerts.traffic-advisory', compact('advisories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        TrafficAdvisory::create([
            'title' => $request->title,
            'location' => $request->location,
            'description' => $request->description,
            'is_active' => true,
            'created_by' => backpack_user()->id,
        ]);

        return response()->json(['ok' => true]);
    }
}