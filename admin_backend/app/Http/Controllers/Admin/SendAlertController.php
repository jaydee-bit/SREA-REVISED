<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Announcement;
use App\Models\TrafficAdvisory;
use Illuminate\Http\Request;

class SendAlertController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')->latest()->get();
        $recentAdvisories = TrafficAdvisory::with('creator')->latest()->take(2)->get();

        $stats = [
            'total_alerts' => Alert::count(),
            'sent_today' => Alert::whereDate('created_at', today())->count(),
        ];

        return view('admin.alerts.send', compact('announcements', 'recentAdvisories', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'barangay' => 'nullable|string',
        ]);

        Alert::create([
            'title' => $request->title,
            'description' => $request->message,
            'barangay' => $request->barangay === 'All Barangays - San Rafael' ? null : $request->barangay,
            'is_active' => true,
            'created_by' => backpack_user()->id,
        ]);

        return response()->json(['ok' => true]);
    }
}