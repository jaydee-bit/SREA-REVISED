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
                    <div class="h1 mb-1">12</div>
                    <div class="small text-success">▲ +3 today</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Emergency Calls</div>
                    <div class="h1 mb-1">5</div>
                    <div class="small text-danger">▼ 2 ongoing</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Deployed Rescuers</div>
                    <div class="h1 mb-1">7</div>
                    <div class="small text-success">▲ 3 standby</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Resolved This Week</div>
                    <div class="h1 mb-1">28</div>
                    <div class="small text-success">▲ 92% rate</div>
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
                    <div class="h1 mb-1">1,240</div>
                    <div class="small text-success">342 active this week</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Traffic Advisories</div>
                    <div class="h1 mb-1">3</div>
                    <div class="small text-muted">active now</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Barangays Covered</div>
                    <div class="h1 mb-1">34</div>
                    <div class="small text-muted">San Rafael</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="row row-cards mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Incidents Per Month</h3>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-success">Monthly</button>
                        <button type="button" class="btn btn-outline-secondary">Weekly</button>
                    </div>
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
                    <canvas id="byTypeChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Table + Rescue Teams row --}}
    <div class="row row-cards">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Incidents Per Month</h3>
                    <span class="text-muted small">All ▾</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-vcenter mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>ID</th>
                                <th>Type</th>
                                <th>Barangay</th>
                                <th>Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-success">INC-001</td>
                                <td>Flood</td>
                                <td>Banca-Banca</td>
                                <td><span class="badge bg-red text-white">Critical</span></td>
                                <td><span class="badge bg-red-lt">Responding</span></td>
                            </tr>
                            <tr>
                                <td class="text-success">INC-002</td>
                                <td>Landslide</td>
                                <td>Sampaloc</td>
                                <td><span class="badge bg-red text-white">High</span></td>
                                <td><span class="badge bg-red-lt">Responding</span></td>
                            </tr>
                            <tr>
                                <td class="text-success">INC-003</td>
                                <td>Accident</td>
                                <td>Maronquillo</td>
                                <td><span class="badge bg-yellow text-white">Medium</span></td>
                                <td><span class="badge bg-yellow-lt">Waiting for Response</span></td>
                            </tr>
                            <tr>
                                <td class="text-success">INC-004</td>
                                <td>Flood</td>
                                <td>Tambubong</td>
                                <td><span class="badge bg-green text-white">Low</span></td>
                                <td><span class="badge bg-green-lt">Waiting for Response</span></td>
                            </tr>
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
                    <div class="d-flex align-items-center border rounded p-2">
                        <span class="badge bg-red rounded-circle p-1 me-2">&nbsp;</span> Team Alpha
                    </div>
                    <div class="d-flex align-items-center border rounded p-2">
                        <span class="badge bg-blue rounded-circle p-1 me-2">&nbsp;</span> Team Bravo
                    </div>
                    <div class="d-flex align-items-center border rounded p-2">
                        <span class="badge bg-secondary rounded-circle p-1 me-2">&nbsp;</span> Team Charlie
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    new Chart(document.getElementById('incidentsPerMonthChart'), {
        type: 'bar',
        data: {
            labels: ['Jan','Feb','March','Apr','May','June','July','Aug','Sep','Oct','Nov','Dec'],
            datasets: [{
                label: 'Incidents',
                data: [78, 45, 40, 55, 70, 42, 35, 88, 45, 72, 35, 78],
                backgroundColor: '#9AB2FF',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });

    new Chart(document.getElementById('byTypeChart'), {
        type: 'doughnut',
        data: {
            labels: ['Flood','Fire','Landslide','Typhoon','Accident'],
            datasets: [{
                data: [30, 20, 10, 25, 15],
                backgroundColor: ['#6C63FF', '#FF6B81', '#3EC6D9', '#FFB648', '#4C6FFF']
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: { legend: { position: 'right' } }
        }
    });
</script>
@endsection