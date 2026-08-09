<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Prefer not to say',
            'birth_date' => 'nullable|date',
            'profile_image' => 'nullable|string|max:255',
            // Complete profile fields
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'valid_id_type' => 'nullable|string|max:255',
            'valid_id_photo' => 'nullable|string|max:255',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $file = $request->file('image');
        $filename = 'profile_' . $request->user()->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profile_images', $filename, 'public');
        $url = url(Storage::url($path));

        $request->user()->update(['profile_image' => $url]);

        return response()->json(['profile_image' => $url]);
    }

    // ========== CHANGE PASSWORD ==========
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.'
        ]);
    }
}
