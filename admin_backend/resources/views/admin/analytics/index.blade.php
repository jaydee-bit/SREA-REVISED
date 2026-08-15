@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Analytics' => false];
@endphp

@section('content')
<style>
    .type-bar-track { background:#EEF0F4; border-radius:4px; height:10px; flex:1; }
    .type-bar-fill { height:10px; border-radius:4px; }
</style>

<div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
    <div>
        <h2 class="mb-1">Analytics & Reports</h2>
        <div class="text-muted">Registered public mobile app users</div>
    </div>
    <div class="d-flex align-items-end gap-2">
        <div>
            <div class="small text-muted">From</div>
            <input type="date" id="fromDate" class="form-control form-control-sm" value="2026-01-16">
        </div>
        <div>
            <div class="small text-muted">To</div>
            <input type="date" id="toDate" class="form-control form-control-sm" value="2026-03-17">
        </div>
        <button class="btn btn-sm btn-outline-secondary" onclick="applyDateFilter()">Filter</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="exportCsv()">Export</button>
    </div>
</div>
  
<div class="row row-cards my-3">
    <div class="col-sm-6">
        <div class="card"><div class="card-body">
            <div class="text-muted small mb-1">Avg Response Time</div>
            <div class="h1 mb-0 text-success">{{ $stats['avg_response_time'] }}</div>
            <div class="small text-success">▼ Improved {{ $stats['avg_response_improved'] }}</div>
        </div></div>
    </div>
    <div class="col-sm-6">
        <div class="card"><div class="card-body">
            <div class="text-muted small mb-1">High Risk Barangays</div>
            <div class="h1 mb-0 text-danger">{{ $stats['high_risk_barangays']['count'] }}</div>
            <div class="small text-muted">{{ $stats['high_risk_barangays']['names'] }}</div>
        </div></div>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Incidents Per Month</h6></div>
            <div class="card-body">
                <canvas id="barangayChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Incident Types Distribution</h6></div>
            <div class="card-body" id="typeDistributionBody">
                @foreach ($typeData as $t)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:70px;" class="small">{{ $t['type'] }}</div>
                        <div class="type-bar-track">
                            <div class="type-bar-fill" style="width:{{ $t['percent'] }}%; background:{{ $t['color'] }};"></div>
                        </div>
                        <div style="width:36px;" class="small text-end">{{ $t['percent'] }}%</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
        <div class="card-header"><h6 class="mb-0">Monthly Incident Trend</h6></div>
        <div class="card-body">
        <canvas id="trendChart" height="90"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const allBarangayData = @json($barangayData);
    const allTrendData = @json($trendData);

    let barangayChart, trendChart;

    function renderCharts(barangayRows, trendRows) {
        if (barangayChart) barangayChart.destroy();
        if (trendChart) trendChart.destroy();

        barangayChart = new Chart(document.getElementById('barangayChart'), {
            type: 'bar',
            data: {
                labels: barangayRows.map(r => r.barangay),
                datasets: [{ label: 'Incidents', data: barangayRows.map(r => r.count), backgroundColor: '#8C9EFF', borderRadius: 4 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });

        trendChart = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendRows.map(r => r.month),
                datasets: [
                    { label: 'Actual Incidents', data: trendRows.map(r => r.actual), borderColor: '#E0554F', backgroundColor: '#E0554F', tension: 0.3, fill: false }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' } } }
        });
    }

    function applyDateFilter() {
        const from = new Date(document.getElementById('fromDate').value);
        const to = new Date(document.getElementById('toDate').value);

        const filteredBarangay = allBarangayData.filter(r => {
            const d = new Date(r.date);
            return d >= from && d <= to;
        });
        const filteredTrend = allTrendData.filter(r => {
            const d = new Date(r.date);
            return d >= from && d <= to;
        });

        renderCharts(filteredBarangay.length ? filteredBarangay : allBarangayData, filteredTrend.length ? filteredTrend : allTrendData);
    }

    function exportCsv() {
        let csv = 'Barangay,Incident Count\n';
        allBarangayData.forEach(r => csv += `${r.barangay},${r.count}\n`);
        csv += '\nMonth,Actual\n';
        allTrendData.forEach(r => csv += `${r.month},${r.actual ?? ''}\n`);

        const blob = new Blob([csv], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'srea-analytics.csv';
        link.click();
    }

    renderCharts(allBarangayData, allTrendData);
</script>
@endsection