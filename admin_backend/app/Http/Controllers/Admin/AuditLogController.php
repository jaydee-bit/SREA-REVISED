<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index()
    {
        $query = Activity::with('causer', 'subject')->latest();

        // Activity has no barangay column of its own — it's a generic log
        // over any model, keyed by polymorphic causer/subject. So "their
        // own barangay's staff/actions" means: either the person who DID
        // something belongs to this barangay, or the person something was
        // DONE TO belongs to this barangay (e.g. a super admin editing a
        // barangay admin's account should still show up for that admin).
        if (!backpack_user()->isSuperAdmin()) {
            $barangay = backpack_user()->barangay;

            $query->where(function ($q) use ($barangay) {
                $q->whereHasMorph('causer', [\App\Models\User::class], function ($causerQuery) use ($barangay) {
                    $causerQuery->where('barangay', $barangay);
                })->orWhereHasMorph('subject', [\App\Models\User::class], function ($subjectQuery) use ($barangay) {
                    $subjectQuery->where('barangay', $barangay);
                });
            });
        }

        $logs = $query->take(200)->get();

        return view('admin.audit-log.index', compact('logs'));
    }
}