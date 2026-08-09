<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\IncidentPageController;
use App\Http\Controllers\Admin\ResponseMonitorController;
use App\Http\Controllers\Admin\LiveMapController;
use App\Http\Controllers\Admin\ResponderPageController;
// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::get('incidents', [\App\Http\Controllers\Admin\IncidentPageController::class, 'index'])->name('incidents.index');
    Route::get('response-monitor', [\App\Http\Controllers\Admin\ResponseMonitorController::class, 'index'])->name('response-monitor.index');
    Route::get('live-map', [\App\Http\Controllers\Admin\LiveMapController::class, 'index'])->name('live-map.index');
    Route::get('responders', [\App\Http\Controllers\Admin\ResponderPageController::class, 'index'])->name('responders.index');
    Route::get('responders-reports', [\App\Http\Controllers\Admin\ResponderReportController::class, 'index'])->name('responders-reports.index');
    Route::get('send-alert', [\App\Http\Controllers\Admin\SendAlertController::class, 'index'])->name('send-alert.index');
Route::get('traffic-advisory', [\App\Http\Controllers\Admin\TrafficAdvisoryController::class, 'index'])->name('traffic-advisory.index');
Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
