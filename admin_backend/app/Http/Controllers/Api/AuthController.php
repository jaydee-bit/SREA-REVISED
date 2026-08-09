<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ==================== REGISTER ====================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string',
            'role' => 'sometimes|in:resident,non_resident',
            'barangay' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'resident',
            'barangay' => $request->barangay,
            'phone' => $request->phone,
            'is_verified' => false,
        ]);

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_verified' => $user->is_verified,
            ],
        ], 201);
    }

    // ==================== LOGIN ====================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'client_type' => 'required|in:user,responder,admin',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $allowedRoles = match ($request->client_type) {
            'user' => ['resident', 'non_resident'],
            'responder' => ['responder', 'admin'],
            'admin' => ['admin'],
            default => [],
        };

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => 'This account is not authorized for this application.'
            ], 403);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'barangay' => $user->barangay,
                'is_verified' => $user->is_verified,
            ]
        ]);
    }

    // ==================== LOGOUT ====================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // ==================== USER PROFILE (with incident counts) ====================
    public function user(Request $request)
    {
        $user = $request->user();

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'barangay' => $user->barangay,
            'is_verified' => $user->is_verified,
            'street' => $user->street,
            'province' => $user->province,
            'municipality' => $user->municipality,
            'valid_id_photo' => $user->valid_id_photo,
            'valid_id_type' => $user->valid_id_type,
            'profile_image' => $user->profile_image,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'birth_date' => $user->birth_date,
        ];

        // Always include incident counts (0 if none)
        $data['incidents_handled'] = Incident::where('assigned_to', $user->id)
            ->where('status', 'Resolved')
            ->count();

        $data['active_incidents'] = Incident::where('assigned_to', $user->id)
            ->whereIn('status', ['Pending', 'Under Review'])
            ->count();

        return response()->json($data);
    }

    // ==================== FORGOT PASSWORD ====================
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'We cannot find a user with that email.'], 404);
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => hash('sha256', $token),
            'created_at' => now(),
        ]);

        $resetUrl = url("/reset-password?token={$token}&email=" . urlencode($request->email));
        Log::info('Password reset link: ' . $resetUrl);

        return response()->json(['message' => 'Reset link sent. Check storage/logs/laravel.log for the URL.']);
    }

    // ==================== VERIFY RESET TOKEN ====================
    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', hash('sha256', $request->token))
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        if (now()->diffInMinutes($reset->created_at) > 60) {
            return response()->json(['message' => 'Token expired.'], 400);
        }

        return response()->json(['message' => 'Token is valid.']);
    }

    // ==================== RESET PASSWORD ====================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', hash('sha256', $request->token))
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        if (now()->diffInMinutes($reset->created_at) > 60) {
            return response()->json(['message' => 'Token expired.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
