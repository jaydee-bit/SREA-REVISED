<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     * Public endpoint – works with or without authentication.
     */
    public function index(Request $request)
    {
        $query = Announcement::where('is_published', true)
            ->orderBy('published_at', 'desc');

        // If user is authenticated and has a barangay, filter by it
        $user = $request->user();
        if ($user && $user->barangay) {
            $query->where(function ($q) use ($user) {
                $q->where('barangay', $user->barangay)->orWhereNull('barangay');
            });
        }

        $announcements = $query->get();
        return response()->json($announcements);
    }

    /**
     * Display a specific announcement.
     */
    public function show($id)
    {
        $announcement = Announcement::where('is_published', true)->findOrFail($id);
        return response()->json($announcement);
    }
}
