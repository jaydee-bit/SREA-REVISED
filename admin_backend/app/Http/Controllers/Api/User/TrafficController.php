<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\TrafficAdvisory;
use Illuminate\Http\Request;

class TrafficController extends Controller
{
    public function index()
    {
        $advisories = TrafficAdvisory::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($advisories);
    }

    public function show($id)
    {
        $advisory = TrafficAdvisory::where('is_active', true)->findOrFail($id);
        return response()->json($advisory);
    }
}