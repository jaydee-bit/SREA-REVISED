<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MockAnnouncements;
use App\Support\MockTrafficAdvisories;

class SendAlertController extends Controller
{
    public function index()
    {
        $announcements = MockAnnouncements::all();
        $recentAdvisories = array_slice(MockTrafficAdvisories::all(), 0, 2);

        $stats = [
            'total_reached' => 1240,
            'sent_today' => 3,
            'open_rate' => 87,
        ];

        return view('admin.alerts.send', compact('announcements', 'recentAdvisories', 'stats'));
    }
}