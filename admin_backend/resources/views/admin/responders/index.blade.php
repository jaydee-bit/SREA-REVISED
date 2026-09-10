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
    <div class="card-header d-flex justify-content-end">
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
        const rows = document.querySelectorAll('#respondersTable tbody tr');
        const search = document.getElementById('responderSearch');

        search.addEventListener('input', function () {
            const query = search.value.toLowerCase();
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    });
</script>
@endsection