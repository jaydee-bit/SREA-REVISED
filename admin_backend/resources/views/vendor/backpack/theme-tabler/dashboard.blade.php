@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        'SREA' => backpack_url('dashboard'),
        'Dashboard' => false,
    ];
@endphp

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-4 pb-3" style="border-bottom: 1px solid #E4E7ED;">
        <div>
            <h2 class="mb-1">Overview</h2>
            <div class="text-muted">Real-time situational awareness — San Rafael, Bulacan</div>
        </div>
        <div>
            <a href="{{ backpack_url('send-alert') }}" class="btn btn-outline-secondary me-2">Broadcast</a>
            <a href="{{ backpack_url('incident') }}" class="btn" style="background-color:#1CA97B; color:#fff;">View Incidents</a>
        </div>
    </div>

    {{-- Stat cards row 1 --}}
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Active Incidents</div>
                    <div class="h1 mb-1">{{ $activeIncidents }}</div>
                    <div class="small text-success">▲ +{{ $newToday }} today</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Reports Today</div>
                    <div class="h1 mb-1">{{ $emergencyCalls }}</div>
                    <div class="small text-muted">reported today</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Deployed Rescuers</div>
                    <div class="h1 mb-1">{{ $deployedRescuers }}</div>
                    <div class="small text-success">▲ {{ $standbyRescuers }} standby</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Resolved This Week</div>
                    <div class="h1 mb-1">{{ $resolvedThisWeek }}</div>
                    <div class="small text-success">this week</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards row 2 --}}
    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total App Users</div>
                    <div class="h1 mb-1">{{ number_format($totalUsers) }}</div>
                    <div class="small text-muted">registered accounts</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Traffic Advisories</div>
                    <div class="h1 mb-1">{{ $activeAdvisories }}</div>
                    <div class="small text-muted">active now</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Barangays Covered</div>
                    <div class="h1 mb-1">{{ $barangayCount }}</div>
                    <div class="small text-muted">San Rafael</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="row row-cards mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Incidents Per Month</h3>
                </div>
                <div class="card-body">
                    <canvas id="incidentsPerMonthChart" height="90"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">By Type</h3>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    @if ($byType->isEmpty())
                        <div class="text-muted small text-center py-4">No classified incidents yet — types are set when a responder resolves a report.</div>
                    @else
                        <canvas id="byTypeChart" height="180"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Table + Rescue Teams row --}}
    <div class="row row-cards">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Recent Incidents</h3>
                    <a href="{{ backpack_url('incidents') }}" class="small">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-vcenter mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>ID</th>
                                <th>Type</th>
                                <th>Barangay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentIncidents as $incident)
                                <tr>
                                    <td class="text-success">#{{ $incident->id }}</td>
                                    <td>{{ $incident->type }}</td>
                                    <td>{{ $incident->barangay }}</td>
                                    <td><span class="badge bg-red-lt">{{ $incident->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted small py-3">No incidents yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rescue Teams</h3>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    @forelse ($teams as $team)
                        <div class="d-flex align-items-center border rounded p-2">
                            <span class="badge bg-blue rounded-circle p-1 me-2">&nbsp;</span> {{ $team }}
                        </div>
                    @empty
                        <div class="text-muted small">No teams assigned yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const incidentsPerMonth = @json($incidentsPerMonth);
    const byType = @json($byType);

    const monthLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const monthData = monthLabels.map((_, i) => incidentsPerMonth[i + 1] ?? 0);

    new Chart(document.getElementById('incidentsPerMonthChart'), {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Incidents',
                data: monthData,
                backgroundColor: '#9AB2FF',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    const byTypeCanvas = document.getElementById('byTypeChart');
    if (byTypeCanvas) {
        new Chart(byTypeCanvas, {
            type: 'doughnut',
            data: {
                labels: Object.keys(byType),
                datasets: [{
                    data: Object.values(byType),
                    backgroundColor: ['#6C63FF', '#FF6B81', '#3EC6D9', '#FFB648', '#4C6FFF', '#2FB344']
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: { legend: { position: 'right' } }
            }
        });
    }
</script>
@endsection