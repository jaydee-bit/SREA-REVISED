<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;

class ResponseMonitorController extends Controller
{
    public function index()
    {
        $incidents = Incident::with(['reporter', 'assignedTo.responderProfile'])
            ->orderByDesc('reported_at')
            ->get();

        $waiting = $incidents->where('status', 'Escalated')->values();
        $active = $incidents->where('status', 'Responding')->values();

        $standbyResponders = User::where('role', 'responder')
            ->whereHas('responderProfile', fn ($q) => $q->where('current_status', 'Standby'))
            ->with('responderProfile')
            ->get();

        return view('admin.response-monitor.index', compact('incidents', 'waiting', 'active', 'standbyResponders'));
    }

    public function dispatch(Request $request, Incident $incident)
    {
        $request->validate([
            'responder_id' => 'required|exists:users,id',
        ]);

        $incident->update([
            'assigned_to' => $request->responder_id,
            'status' => 'Responding',
        ]);

        $responder = User::find($request->responder_id);
        $responder->responderProfile()->update(['current_status' => 'Deployed']);

        return response()->json(['ok' => true]);
    }
}