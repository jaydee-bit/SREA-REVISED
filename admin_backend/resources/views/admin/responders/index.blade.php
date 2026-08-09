@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        'SREA' => backpack_url('dashboard'),
        'Responders' => false,
    ];
@endphp

@section('content')
<style>
    .badge-status-deployed { background:#FCE8CC; color:#8A5A00; }
    .badge-status-standby  { background:#D9F2E3; color:#137A45; }
    .badge-status-off_duty { background:#E4E7ED; color:#5C6270; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h2 class="mb-1">Responders</h2>
        <div class="text-muted">Manage MDRRMO rescue team members</div>
    </div>
    <div>
        <button class="btn btn-outline-secondary me-2">Distribute App</button>
        <button class="btn" style="background:#1CA97B; color:#fff;">+ Add Responders</button>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-sm-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small mb-1">Total Responders</div>
            <div class="h1 mb-0">{{ count($responders) }}</div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small mb-1">Deployed</div>
            <div class="h1 mb-0 text-warning">{{ collect($responders)->where('status', 'deployed')->count() }}</div>
        </div></div>
    </div>
    <div class="col-sm-4">
        <div class="card"><div class="card-body">
            <div class="text-muted small mb-1">Standby</div>
            <div class="h1 mb-0 text-success">{{ collect($responders)->where('status', 'standby')->count() }}</div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="btn-group btn-group-sm" id="statusTabs">
            <button type="button" class="btn btn-success active" data-filter="all">All</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="deployed">Deployed</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="standby">Standby</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="off_duty">Off Duty</button>
        </div>
        <input type="text" id="responderSearch" class="form-control form-control-sm" style="width:220px;" placeholder="Search Incidents....">
    </div>
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0" id="respondersTable">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>ID</th><th>Name</th><th>Team</th><th>Vehicle</th><th>Phone</th>
                    <th>Status</th><th>Location</th><th>Assigned</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($responders as $r)
                    <tr data-status="{{ $r['status'] }}">
                        <td>{{ $r['id'] }}</td>
                        <td>{{ $r['name'] }}</td>
                        <td>{{ $r['team'] }}</td>
                        <td>{{ $r['vehicle'] }}</td>
                        <td>{{ $r['phone'] }}</td>
                        <td><span class="badge badge-status-{{ $r['status'] }}">{{ ucfirst(str_replace('_', ' ', $r['status'])) }}</span></td>
                        <td>{{ $r['location'] ? '📍 '.$r['location'] : '-' }}</td>
                        <td>{{ $r['assigned_incident'] ? $r['assigned_incident'] : '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-light">Edit</button>
                            <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;">Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('#statusTabs button');
        const rows = document.querySelectorAll('#respondersTable tbody tr');
        const search = document.getElementById('responderSearch');

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