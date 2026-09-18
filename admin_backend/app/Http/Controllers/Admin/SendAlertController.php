<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\TrafficAdvisory;
use Illuminate\Http\Request;

class SendAlertController extends Controller
{
    public function index()
    {
        $admin = backpack_user();

        $recentAdvisories = TrafficAdvisory::with('creator')->latest()->take(2)->get();

        $alertQuery = Alert::query();
        if (!$admin->is_super_admin) {
            $alertQuery->where('barangay', $admin->barangay);
        }

        $stats = [
            'total_alerts' => (clone $alertQuery)->count(),
            'sent_today' => (clone $alertQuery)->whereDate('created_at', today())->count(),
        ];

        return view('admin.alerts.send', compact('recentAdvisories', 'stats'));
    }

    public function store(Request $request)
    {
        $admin = backpack_user();

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'barangay' => 'nullable|string',
        ]);

        // A barangay-scoped admin can only ever alert their own barangay —
        // the submitted value is ignored entirely for non-super-admins,
        // so a tampered request (null, "All Barangays", or another
        // barangay's name) can't broadcast outside their authority.
        if ($admin->is_super_admin) {
            $barangay = $request->barangay === 'All Barangays - San Rafael'
                ? null
                : $request->barangay;
        } else {
            $barangay = $admin->barangay;
        }

        Alert::create([
            'title' => $request->title,
            'description' => $request->message,
            'barangay' => $barangay,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        return response()->json(['ok' => true]);
    }
}