@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Audit Trail' => false];
@endphp

@section('content')
<style>
    .badge-role-Admin { background:#E7E9FB; color:#4C51BF; }
    .badge-role-Responder { background:#D9F2E3; color:#137A45; }
    .badge-role-System { background:#EEF0F4; color:#5C6270; }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h2 class="mb-1">Audit Trail</h2>
        <div class="text-muted">A log of every action taken across the system</div>
    </div>
    <div style="width: 260px;">
        <input type="text" id="logSearch" class="form-control" placeholder="Search log...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0" id="logTable">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Event</th>
                    <th>Description</th>
                    <th>Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-muted small">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        <td>{{ $log->causer->name ?? 'System' }}</td>
                        <td><span class="badge badge-role-{{ $log->causer?->role === 'admin' ? 'Admin' : ($log->causer?->role === 'responder' ? 'Responder' : 'System') }}">{{ ucfirst($log->event ?? '-') }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td class="small text-muted">
                            @if ($log->attribute_changes ?? false)
                                @foreach ($log->attribute_changes['attributes'] ?? [] as $key => $value)
                                    {{ $key }}: {{ $value }}@if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted small py-3">No activity logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rows = document.querySelectorAll('#logTable tbody tr');
        const search = document.getElementById('logSearch');

        search.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
            });
        });
    });
</script>
@endsection