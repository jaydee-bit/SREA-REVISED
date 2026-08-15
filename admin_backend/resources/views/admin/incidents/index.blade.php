@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        'SREA' => backpack_url('dashboard'),
        'Incidents' => false,
    ];
@endphp

@section('content')
<style>
    .badge-status-responding { background:#FDE8E8; color:#D63939; }
    .badge-status-waiting    { background:#E7E9FB; color:#4C51BF; }
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1">Incidents</h2>
            <div class="text-muted">All reported incidents in San Rafael</div>
        </div>
        <div style="width: 260px;">
            <input type="text" id="incidentSearch" class="form-control" placeholder="Search incidents...">
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="btn-group" id="statusTabs">
                <button type="button" class="btn btn-success btn-sm active" data-filter="all">All</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="waiting">Waiting Response</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="responding">Responding</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-vcenter mb-0" id="incidentsTable">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>ID</th>
                        <th>Type</th>
                        <th>Barangay</th>
                        <th>Reporter</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidents as $incident)
                        <tr class="incident-row-{{ $incident['level'] }}" data-status="{{ $incident['status'] }}">
                            <td>{{ $incident['id'] }}</td>
                            <td>{{ $incident['type'] }}</td>
                            <td>{{ $incident['barangay'] }}</td>
                            <td>{{ $incident['reporter'] }}</td>
                            
                            <td>
                                <span class="badge badge-status-{{ $incident['status'] }}">
                                    {{ $incident['status'] === 'responding' ? 'Responding' : 'Waiting for Response' }}
                                </span>
                            </td>
                            <td>{{ $incident['assigned']['name'] ?? '-' }}</td>
                            <td>{{ $incident['time'] }}</td>
                            <td>
                                @if ($incident['assigned'])
                                    <button class="btn btn-sm btn-light">View</button>
                                    <button class="btn btn-sm" style="background:#F59F00; color:#fff;">Reassign</button>
                                @else
                                    <button class="btn btn-sm" style="background:#F59F00; color:#fff;">Assigned</button>
                                    <button class="btn btn-sm btn-light">View</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('#statusTabs button');
        const rows = document.querySelectorAll('#incidentsTable tbody tr');
        const search = document.getElementById('incidentSearch');

        function applyFilters() {
            const activeFilter = document.querySelector('#statusTabs button.active').dataset.filter;
            const query = search.value.toLowerCase();

            rows.forEach(row => {
                const matchesStatus = activeFilter === 'all' || row.dataset.status === activeFilter;
                const matchesSearch = row.textContent.toLowerCase().includes(query);
                row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
            });
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active', 'btn-success'));
                tabs.forEach(t => t.classList.add('btn-outline-secondary'));
                tab.classList.remove('btn-outline-secondary');
                tab.classList.add('active', 'btn-success');
                applyFilters();
            });
        });

        search.addEventListener('input', applyFilters);
    });
</script>
@endsection