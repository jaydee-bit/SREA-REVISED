@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Incidents' => false];
@endphp

@section('content')
<style>
    .badge-status-Responding { background:#FDE8E8; color:#D63939; }
    .badge-status-Pending    { background:#E7E9FB; color:#4C51BF; }
    .badge-status-Escalated  { background:#FFF3CD; color:#856404; }
    .badge-status-Resolved   { background:#D9F2E3; color:#137A45; }
    .badge-status-Rejected   { background:#F8D7DA; color:#B02A37; }
    .badge-nearby { background:#EEF0F4; color:#5C6270; }
    .media-thumb { width:40px; height:40px; border-radius:6px; object-fit:cover; background:#EEF0F4; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:440px; max-width:90vw; padding:24px; max-height:85vh; overflow-y:auto; }
    #incidentsTable td { vertical-align: middle; }
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1">Incidents</h2>
            <div class="text-muted">All reported incidents in San Rafael</div>
        </div>
        <div class="d-flex gap-2">
            <div style="width: 220px;">
                <input type="text" id="incidentSearch" class="form-control" placeholder="Search incidents...">
            </div>
            <a href="{{ backpack_url('incidents/export/csv') }}" class="btn btn-outline-secondary">⬇ CSV</a>
            <a href="{{ backpack_url('incidents/export/pdf') }}" class="btn btn-outline-secondary">⬇ PDF</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="btn-group" id="statusTabs">
                <button type="button" class="btn btn-success btn-sm active" data-filter="all">All</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="Pending">Waiting Response</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="Responding">Responding</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="Escalated">Escalated</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="Rejected">Rejected</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="Resolved">Resolved</button>
            </div>
        </div>
        <div class="card-body p-0" style="overflow-x:auto;">
            <table class="table table-vcenter mb-0" id="incidentsTable">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Media</th>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Barangay</th>
                        <th>Reporter</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Reported</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidents as $incident)
                        <tr data-status="{{ $incident->status }}">
                            <td>
                                @if ($incident->photo_path)
                                    <img src="{{ $incident->photo_path }}" class="media-thumb" alt="photo">
                                @elseif ($incident->video_path)
                                    <div class="media-thumb d-flex align-items-center justify-content-center">🎥</div>
                                @else
                                    <div class="media-thumb d-flex align-items-center justify-content-center text-muted">—</div>
                                @endif
                            </td>
                            <td>#{{ $incident->id }}</td>
                            <td>{{ $incident->type }}</td>
                            <td>{{ $incident->barangay }}</td>
                            <td>{{ $incident->reporter_name ?? 'Anonymous' }}</td>
                            <td>
                                <span class="badge badge-status-{{ $incident->status }}">{{ $incident->status }}</span>
                                @if ($incident->nearby_count > 0)
                                    <span class="badge badge-nearby">⚠️ {{ $incident->nearby_count }} nearby</span>
                                @endif
                            </td>
                            <td>{{ $incident->assignedTo->name ?? '-' }}</td>
                            <td>{{ $incident->reported_at->diffForHumans() }}</td>
                            <td>
                                <button class="btn btn-sm btn-light" onclick="viewDescription({{ $incident->id }})">View</button>
                                @if ($incident->status === 'Pending')
                                    <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;" onclick="openReject({{ $incident->id }})">Reject</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Description Modal --}}
<div class="modal-backdrop-custom" id="descriptionModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="descModalTitle">Description</h4>
            <button class="btn-close" onclick="document.getElementById('descriptionModal').classList.remove('show')"></button>
        </div>
        <div id="descModalBody"></div>
        <button class="btn btn-light mt-2" onclick="document.getElementById('descriptionModal').classList.remove('show')">Close</button>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-backdrop-custom" id="rejectModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="rejectModalTitle">Reject Incident</h4>
            <button class="btn-close" onclick="document.getElementById('rejectModal').classList.remove('show')"></button>
        </div>
        <div class="small text-muted mb-2">Please provide a reason (minimum 10 characters).</div>
        <textarea id="rejectReason" class="form-control mb-2" rows="3" placeholder="Reason for rejecting this report..."></textarea>
        <div id="rejectError" class="text-danger small mb-2" style="display:none;">Reason must be at least 10 characters.</div>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light" onclick="document.getElementById('rejectModal').classList.remove('show')">Cancel</button>
            <button class="btn" style="background:#B02A37; color:#fff;" onclick="confirmReject()">Confirm Reject</button>
        </div>
    </div>
</div>

<div id="toast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:2000; min-width:300px; padding:16px 20px; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.2); color:#fff; font-weight:500;"></div>

<script>
    const incidents = @json($incidents);

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

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.style.background = type === 'success' ? '#1CA97B' : '#D63939';
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    function viewDescription(incidentId) {
        const inc = incidents.find(i => i.id === incidentId);
        if (!inc) return;
        document.getElementById('descModalTitle').textContent = `#${inc.id} — Description`;
        document.getElementById('descModalBody').innerHTML = `<div class="small">${inc.description ?? 'No description available.'}</div>`;
        document.getElementById('descriptionModal').classList.add('show');
    }

    let rejectTargetId = null;

    function openReject(incidentId) {
        rejectTargetId = incidentId;
        document.getElementById('rejectModalTitle').textContent = `Reject #${incidentId}`;
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectError').style.display = 'none';
        document.getElementById('rejectModal').classList.add('show');
    }

    function confirmReject() {
        const reason = document.getElementById('rejectReason').value.trim();
        if (reason.length < 10) {
            document.getElementById('rejectError').textContent = 'Reason must be at least 10 characters.';
            document.getElementById('rejectError').style.display = 'block';
            return;
        }

        fetch(`/admin/incidents/${rejectTargetId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason }),
        })
        .then(res => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(() => {
            document.getElementById('rejectModal').classList.remove('show');
            showToast(`Incident #${rejectTargetId} rejected successfully.`);
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(() => {
            document.getElementById('rejectError').textContent = 'Something went wrong. Please try again.';
            document.getElementById('rejectError').style.display = 'block';
        });
    }
</script>
@endsection