@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Responders' => false];
@endphp

@section('content')
<style>
    .badge-status-Deployed { background:#FCE8CC; color:#8A5A00; }
    .badge-status-Standby  { background:#D9F2E3; color:#137A45; }
    .badge-status-OffDuty  { background:#E4E7ED; color:#5C6270; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h2 class="mb-1">Responders</h2>
        <div class="text-muted">Manage MDRRMO rescue team members</div>
    </div>
</div>


<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="btn-group btn-group-sm" id="statusTabs">
            <button type="button" class="btn btn-success active" data-filter="all">All</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="Deployed">Deployed</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="Standby">Standby</button>
            <button type="button" class="btn btn-outline-secondary" data-filter="Off Duty">Off Duty</button>
        </div>
        <input type="text" id="responderSearch" class="form-control form-control-sm" style="width:220px;" placeholder="Search responders...">
    </div>
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0" id="respondersTable">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>Name</th><th>Team</th><th>Vehicle</th><th>Email</th>
                    <th>Assigned Incident</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($responders as $r)
                    <tr data-status="{{ $r->responderProfile->current_status ?? 'Off Duty' }}">
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->responderProfile->team ?? '-' }}</td>
                        <td>{{ $r->responderProfile->vehicle ?? '-' }}</td>
                        <td>{{ $r->email }}</td>
                        <td>{{ $r->incidentsAssigned->first()?->id ? '#' . $r->incidentsAssigned->first()->id : '-' }}</td>
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