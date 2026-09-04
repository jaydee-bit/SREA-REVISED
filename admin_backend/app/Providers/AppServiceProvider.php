<?php

namespace App\Providers;

use App\Models\Barangay;
use App\Models\Alert;
use App\Models\Incident;
use App\Models\TrafficAdvisory;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Observers\IncidentObserver;
use App\Observers\AlertObserver;
use App\Observers\TrafficAdvisoryObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Incident::observe(IncidentObserver::class);
        Alert::observe(AlertObserver::class);
        TrafficAdvisory::observe(TrafficAdvisoryObserver::class);
        View::composer('backpack.theme-tabler::dashboard', function ($view) {
            $activeIncidents = Incident::whereIn('status', ['Pending', 'Responding', 'Escalated'])->count();
            $newToday = Incident::whereDate('created_at', today())->count();
            $emergencyCalls = Incident::whereDate('reported_at', today())->count();
            $deployedRescuers = User::where('role', 'responder')
                ->whereHas('responderProfile', fn ($q) => $q->where('current_status', 'Deployed'))
                ->count();
            $standbyRescuers = User::where('role', 'responder')
                ->whereHas('responderProfile', fn ($q) => $q->where('current_status', 'Standby'))
                ->count();
            $resolvedThisWeek = Incident::where('status', 'Resolved')
                ->where('resolved_at', '>=', now()->subWeek())
                ->count();

            $totalUsers = User::count();
            $activeAdvisories = TrafficAdvisory::where('is_active', true)->count();
            $barangayCount = Barangay::count();

            $incidentsPerMonth = Incident::selectRaw('MONTH(reported_at) as month, COUNT(*) as total')
                ->whereYear('reported_at', now()->year)
                ->groupBy('month')
                ->pluck('total', 'month');

            $byType = Incident::where('type', '!=', 'Emergency')
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            $recentIncidents = Incident::with('assignedTo')->latest('reported_at')->take(4)->get();

            $teams = User::where('role', 'responder')
                ->with('responderProfile')
                ->get()
                ->pluck('responderProfile.team')
                ->filter()
                ->unique()
                ->values();

            $view->with(compact(
                'activeIncidents', 'newToday', 'emergencyCalls', 'deployedRescuers', 'standbyRescuers',
                'resolvedThisWeek', 'totalUsers', 'activeAdvisories', 'barangayCount',
                'incidentsPerMonth', 'byType', 'recentIncidents', 'teams'
            ));
        
        });
    
    }
}