<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display a listing of alerts.
     * Public endpoint – works with or without authentication.
     */
    public function index(Request $request)
    {
        $query = Alert::where('is_active', true);

        // If user is authenticated and has a barangay, filter by it
        $user = $request->user();
        if ($user && $user->barangay) {
            $query->where(function ($q) use ($user) {
                $q->where('barangay', $user->barangay)->orWhereNull('barangay');
            });
        }

        $alerts = $query->orderBy('created_at', 'desc')->get();
        return response()->json($alerts);
    }

    /**
     * Display a specific alert.
     */
    public function show($id)
    {
        $alert = Alert::where('is_active', true)->findOrFail($id);
        return response()->json($alert);
    }
}
