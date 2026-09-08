<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\IncidentController as ResponderIncidentController;
use App\Http\Controllers\Api\User\AlertController;
use App\Http\Controllers\Api\User\AnnouncementController;
use App\Http\Controllers\Api\User\TrafficController;
use App\Http\Controllers\Api\User\EmergencyCallController;
use App\Http\Controllers\Api\User\UserIncidentController;
use App\Http\Controllers\Api\User\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ─── PUBLIC ROUTES (NO AUTHENTICATION) ──────────────────────────────

// Auth endpoints (login, register, forgot password, reset password)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/verify-reset-token', [AuthController::class, 'verifyResetToken']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// ✅ PUBLIC CONTENT (no auth required – for anonymous resident app)
Route::get('/user/alerts', [AlertController::class, 'index']);
Route::get('/user/alerts/{id}', [AlertController::class, 'show']);
Route::get('/user/announcements', [AnnouncementController::class, 'index']);
Route::get('/user/announcements/{id}', [AnnouncementController::class, 'show']);
Route::get('/user/traffic', [TrafficController::class, 'index']);
Route::get('/user/traffic/{id}', [TrafficController::class, 'show']);

// ✅ ANONYMOUS INCIDENT ENDPOINTS
Route::post('/public/incidents', [UserIncidentController::class, 'store']);
Route::get('/public/incidents/{id}', [UserIncidentController::class, 'show']);
Route::post('/public/upload-reporter-media', [UploadController::class, 'store']);
Route::post('/public/devices/register', [App\Http\Controllers\Api\Public\DeviceController::class, 'register']);

// ─── PROTECTED ROUTES (REQUIRE AUTHENTICATION) ──────────────────────
Route::middleware('auth:sanctum')->group(function () {
    // Common auth endpoints
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/responder/fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::post('/responder/location', [App\Http\Controllers\Api\IncidentController::class, 'updateLocation']);

    // Profile management
    Route::post('/user/upload-image', [UploadController::class, 'uploadImage']);
    Route::post('/user/upload-profile-image', [App\Http\Controllers\Api\UserController::class, 'uploadProfileImage']);
    Route::get('/user/profile', [App\Http\Controllers\Api\UserController::class, 'profile']);
    Route::put('/user/profile', [App\Http\Controllers\Api\UserController::class, 'update']);
    Route::post('/user/change-password', [App\Http\Controllers\Api\UserController::class, 'changePassword']);

    // Emergency calls (optional – can stay protected or move to public)
    Route::post('/user/emergency-calls', [EmergencyCallController::class, 'store']);
    Route::get('/user/emergency-calls', [EmergencyCallController::class, 'myHistory']);

    // ==================== RESPONDER APP ENDPOINTS ====================
    Route::prefix('responder')->group(function () {
        Route::get('/incidents', [ResponderIncidentController::class, 'index']);
        Route::get('/incidents/{id}', [ResponderIncidentController::class, 'show']);
        Route::post('/incidents/{id}/respond', [ResponderIncidentController::class, 'respond']);
        Route::post('/incidents/{id}/reassign', [ResponderIncidentController::class, 'reassign']);
        Route::post('/incidents/{id}/resolve', [ResponderIncidentController::class, 'resolve']);
        Route::post('/incidents/{id}/reject', [ResponderIncidentController::class, 'reject']);
        Route::post('/incidents/{id}/notes', [ResponderIncidentController::class, 'updateNotes']);
    });

    // ==================== DEPRECATED USER APP ENDPOINTS ====================
    // These are kept for backward compatibility but no longer used
    Route::prefix('user')->group(function () {
        // Incidents – only myIncidents remains (deprecated)
        Route::get('/incidents', [UserIncidentController::class, 'myIncidents']);
        // ❌ store and show removed – now public
    });
});
