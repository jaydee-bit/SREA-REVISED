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
            $admin = backpack_user();
            $isSuper = $admin->is_super_admin;

            $incidentQuery = fn () => Incident::query()
                ->when(!$isSuper, fn ($q) => $q->where('barangay', $admin->barangay));

            $responderQuery = fn () => User::where('role', 'responder')
                ->when(!$isSuper, fn ($q) => $q->where('barangay', $admin->barangay));

            $activeIncidents = $incidentQuery()->whereIn('status', ['Pending', 'Responding', 'Escalated'])->count();
            $newToday = $incidentQuery()->whereDate('created_at', today())->count();
            $emergencyCalls = $incidentQuery()->whereDate('reported_at', today())->count();
            $deployedRescuers = $responderQuery()
                ->whereHas('responderProfile', fn ($q) => $q->where('current_status', 'Deployed'))
                ->count();
            $standbyRescuers = $responderQuery()
                ->whereHas('responderProfile', fn ($q) => $q->where('current_status', 'Standby'))
                ->count();
            $resolvedThisWeek = $incidentQuery()
                ->where('status', 'Resolved')
                ->where('resolved_at', '>=', now()->subWeek())
                ->count();

            // "Total App Users" — counts staff accounts (admins + responders),
            // since residents are anonymous and have no accounts. Scoped for
            // a barangay admin so they see their own barangay's staff count,
            // not the whole municipality's org size.
            $totalUsers = $isSuper
                ? User::count()
                : User::where('barangay', $admin->barangay)->count();

            // Traffic advisories have no barangay column yet (open design
            // question — see prior discussion), so left unscoped for now.
            $activeAdvisories = TrafficAdvisory::where('is_active', true)->count();

            // "Barangays Covered" describes the municipality as a whole,
            // not the admin's own scope, so this stays global for everyone.
            $barangayCount = Barangay::count();

            $incidentsPerMonth = $incidentQuery()
                ->selectRaw('MONTH(reported_at) as month, COUNT(*) as total')
                ->whereYear('reported_at', now()->year)
                ->groupBy('month')
                ->pluck('total', 'month');

            $byType = $incidentQuery()
                ->where('type', '!=', 'Emergency')
                ->selectRaw('type, COUNT(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type');

            $recentIncidents = $incidentQuery()->with('assignedTo')->latest('reported_at')->take(4)->get();

            $teams = $responderQuery()
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