<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = Activity::with('causer', 'subject')
            ->latest()
            ->take(200)
            ->get();

        return view('admin.audit-log.index', compact('logs'));
    }
}