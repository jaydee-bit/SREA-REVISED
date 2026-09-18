<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;

class ResponderReportController extends Controller
{
    public function index()
    {
        $admin = backpack_user();

        $query = Incident::with(['assignedTo', 'escalatedBy'])
            ->where(function ($query) {
                $query->whereNotNull('responder_notes')
                    ->orWhereNotNull('resolution_notes');
            });

        if (!$admin->is_super_admin) {
            $query->where('barangay', $admin->barangay);
        }

        $reports = $query->orderByDesc('reported_at')->get();

        return view('admin.responder-reports.index', compact('reports'));
    }
}