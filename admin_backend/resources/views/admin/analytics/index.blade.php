@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Analytics' => false];
@endphp

@section('content')
    <style>
        .type-bar-track {
            background: #EEF0F4;
            border-radius: 4px;
            height: 10px;
            flex: 1;
        }

        .type-bar-fill {
            height: 10px;
            border-radius: 4px;
        }

        .filter-toggle {
            display: flex;
            border: 1px solid #D8DCE3;
            border-radius: 6px;
            overflow: hidden;
        }

        .filter-toggle button {
            border: none;
            background: #fff;
            padding: 6px 14px;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .filter-toggle button.active {
            background: #4C5FD5;
            color: #fff;
        }

        .filter-panel {
            display: none;
        }

        .filter-panel.active {
            display: flex;
        }

        .empty-state {
            color: #8A8F98;
            text-align: center;
            padding: 40px 0;
            font-size: 0.9rem;
        }
    </style>

    <div class="d-flex justify-content-between align-items-start mb-1 flex-wrap gap-2">
        <div>
            <h2 class="mb-1">Analytics & Reports</h2>
            <div class="text-muted">Incident trends and response performance</div>
        </div>

        <div class="d-flex align-items-end gap-2 flex-wrap">
            <div>
                <div class="small text-muted mb-1">Filter by</div>
                <div class="filter-toggle">
                    <button type="button" id="modeMonthBtn" class="active" onclick="setMode('month')">Month</button>
                    <button type="button" id="modeDayBtn" onclick="setMode('day')">Exact Date</button>
                </div>
            </div>

            <div class="filter-panel active gap-2" id="monthPanel">
                <div>
                    <div class="small text-muted">Year</div>
                    <select id="yearFilter" class="form-select form-select-sm">
                        <option value="">All years</option>
                        @foreach ($availableYears as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="small text-muted">Month</div>
                    <select id="monthFilter" class="form-select form-select-sm">
                        <option value="">All months</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
            </div>

            <div class="filter-panel gap-2" id="dayPanel">
                <div>
                    <div class="small text-muted">Date</div>
                    <input type="date" id="dayFilter" class="form-control form-control-sm" value="">
                </div>
            </div>

            <button class="btn btn-sm btn-primary" onclick="applyFilters()">Apply</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">Reset</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportCsv()">Export</button>
        </div>
    </div>

    <div class="small text-muted mb-2" id="filterSummary"></div>

    <div class="row row-cards my-3">
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">Avg Response Time</div>
                    <div class="h1 mb-0 text-success" id="avgResponseTimeCard">{{ $stats['avg_response_time'] }}</div>
                    <div class="small text-muted">based on resolved incidents in the selected period</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small mb-1">High Risk Barangays</div>
                    <div class="h1 mb-0 text-danger" id="highRiskCountCard">{{ $stats['high_risk_barangays']['count'] }}
                    </div>
                    <div class="small text-muted" id="highRiskNamesCard">{{ $stats['high_risk_barangays']['names'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Incidents Per Barangay</h6>
                </div>
                <div class="card-body">
                    <canvas id="barangayChart" height="180"></canvas>
                    <div class="empty-state" id="barangayEmpty" style="display:none;">No incidents match this filter.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Incident Types Distribution</h6>
                </div>
                <div class="card-body" id="typeDistributionBody"></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Monthly Incident Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="90"></canvas>
                    <div class="empty-state" id="trendEmpty" style="display:none;">No incidents match this filter.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        const allIncidents = @json($incidents);
        const TYPE_COLORS = ['#2C6BE0', '#D63939', '#C9A227', '#6C63FF', '#F76707', '#2FB344', '#0AA5A5', '#8C8C8C'];
        const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        let barangayChart, trendChart;
        let mode = 'month';

        function setMode(m) {
            mode = m;
            document.getElementById('modeMonthBtn').classList.toggle('active', m === 'month');
            document.getElementById('modeDayBtn').classList.toggle('active', m === 'day');
            document.getElementById('monthPanel').classList.toggle('active', m === 'month');
            document.getElementById('dayPanel').classList.toggle('active', m === 'day');
            if (m === 'month') {
                document.getElementById('dayFilter').value = '';
            } else {
                document.getElementById('yearFilter').value = '';
                document.getElementById('monthFilter').value = '';
            }
        }

        function aggregateBarangay(rows) {
            const counts = {};
            rows.forEach(r => counts[r.barangay] = (counts[r.barangay] || 0) + 1);
            return Object.entries(counts).map(([barangay, count]) => ({
                barangay,
                count
            })).sort((a, b) => b.count - a.count);
        }

        function aggregateMonthly(rows) {
            const counts = {};
            rows.forEach(r => counts[r.month] = (counts[r.month] || 0) + 1);
            return MONTH_NAMES.filter(m => counts[m] !== undefined).map(month => ({
                month,
                actual: counts[month]
            }));
        }

        function computeStats(rows) {
            const withResponse = rows.filter(r => r.response_minutes !== null && r.response_minutes !== undefined);
            const avgMinutes = withResponse.length ?
                withResponse.reduce((sum, r) => sum + r.response_minutes, 0) / withResponse.length :
                null;
            document.getElementById('avgResponseTimeCard').textContent = avgMinutes ? Math.round(avgMinutes) + ' min' :
                'N/A';

            const byBarangay = aggregateBarangay(rows);
            document.getElementById('highRiskCountCard').textContent = byBarangay.length;
            document.getElementById('highRiskNamesCard').textContent = byBarangay.length ?
                byBarangay.slice(0, 3).map(b => b.barangay).join(', ') :
                'No data for this period';
        }

        function renderTypeDistribution(rows) {
            const counts = {};
            rows.forEach(r => counts[r.type] = (counts[r.type] || 0) + 1);
            const total = rows.length;
            const body = document.getElementById('typeDistributionBody');

            if (!total) {
                body.innerHTML = '<div class="empty-state">No incidents match this filter.</div>';
                return;
            }

            const ALL_TYPES = ['Fire', 'Medical', 'Flood', 'Accident', 'Calamity', 'Other'];
            body.innerHTML = ALL_TYPES.map((type, i) => {
                const count = counts[type] || 0;
                const percent = total > 0 ? Math.round((count / total) * 100) : 0;
                const color = TYPE_COLORS[i % TYPE_COLORS.length];
                return `
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div style="width:80px;" class="small">${type}</div>
                    <div class="type-bar-track">
                        <div class="type-bar-fill" style="width:${percent}%; background:${color};"></div>
                    </div>
                    <div style="width:36px;" class="small text-end">${percent}%</div>
                </div>`;
            }).join('');
        }

        function renderCharts(rows) {
            if (barangayChart) barangayChart.destroy();
            if (trendChart) trendChart.destroy();

            const barangayRows = aggregateBarangay(rows);
            const trendRows = aggregateMonthly(rows);

            document.getElementById('barangayEmpty').style.display = barangayRows.length ? 'none' : 'block';
            document.getElementById('trendEmpty').style.display = trendRows.length ? 'none' : 'block';

            barangayChart = new Chart(document.getElementById('barangayChart'), {
                type: 'bar',
                data: {
                    labels: barangayRows.map(r => r.barangay),
                    datasets: [{
                        label: 'Incidents',
                        data: barangayRows.map(r => r.count),
                        backgroundColor: '#8C9EFF',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            trendChart = new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: trendRows.map(r => r.month),
                    datasets: [{
                        label: 'Actual Incidents',
                        data: trendRows.map(r => r.actual),
                        borderColor: '#E0554F',
                        backgroundColor: '#E0554F',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }

        function renderAll(rows) {
            computeStats(rows);
            renderTypeDistribution(rows);
            renderCharts(rows);
        }

        function applyFilters() {
            let filtered = allIncidents;
            const labelParts = [];

            if (mode === 'day') {
                const day = document.getElementById('dayFilter').value;
                if (day) {
                    filtered = allIncidents.filter(r => r.date === day);
                    labelParts.push(`date = ${day}`);
                }
            } else {
                const year = document.getElementById('yearFilter').value;
                const month = document.getElementById('monthFilter').value;
                if (year) {
                    filtered = filtered.filter(r => String(r.year) === String(year));
                    labelParts.push(`year = ${year}`);
                }
                if (month) {
                    filtered = filtered.filter(r => String(r.month_num) === String(month));
                    labelParts.push(`month = ${MONTH_NAMES[parseInt(month) - 1]}`);
                }
            }

            document.getElementById('filterSummary').textContent = labelParts.length ?
                `Showing: ${labelParts.join(', ')} (${filtered.length} incidents)` :
                `Showing: all data (${filtered.length} incidents)`;

            renderAll(filtered);
        }

        function resetFilter() {
            document.getElementById('yearFilter').value = '';
            document.getElementById('monthFilter').value = '';
            document.getElementById('dayFilter').value = '';
            setMode('month');
            document.getElementById('filterSummary').textContent = `Showing: all data (${allIncidents.length} incidents)`;
            renderAll(allIncidents);
        }

        function exportCsv() {
            const barangayRows = aggregateBarangay(allIncidents);
            const trendRows = aggregateMonthly(allIncidents);
            let csv = 'Barangay,Incident Count\n';
            barangayRows.forEach(r => csv += `${r.barangay},${r.count}\n`);
            csv += '\nMonth,Actual\n';
            trendRows.forEach(r => csv += `${r.month},${r.actual}\n`);
            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'srea-analytics.csv';
            link.click();
        }

        document.getElementById('dayFilter').value = '';
        resetFilter();
    </script>
@endsection
