<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $file = $request->file('image');
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('incident_photos', $filename, 'public');

        return response()->json([
            'photo_path' => Storage::url($path),
        ], 201);
    }

    /**
     * Upload a resident's selfie or video for an anonymous report.
     * POST /api/public/upload-reporter-media
     *
     * ✅ Added — routes/api.php pointed at this method but it didn't
     * exist, causing a 500 ("Call to undefined method ...::store()")
     * every time the resident app tried to upload the selfie/video
     * before submitting a report.
     *
     * Accepts either an image or a video under the 'media' field
     * (matches ApiService.uploadReporterMedia in the Flutter app,
     * which sends a MultipartFile under that key) and returns
     * 'media_path', which is what the Flutter side reads back.
     */
    public function store(Request $request)
    {
        $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:51200', // 50MB
        ]);

        $file = $request->file('media');
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('reporter_media', $filename, 'public');

        return response()->json([
            'media_path' => Storage::url($path),
        ], 201);
    }
}
