<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        Device::updateOrCreate(
            ['fcm_token' => $request->fcm_token],
            []
        );

        return response()->json(['message' => 'Device registered']);
    }
}