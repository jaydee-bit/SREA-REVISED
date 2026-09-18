@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Incoming Requests' => false];
@endphp

@section('content')
<style>
    .badge-status-pending  { background:#FFF3CD; color:#856404; }
    .badge-status-accepted { background:#D9F2E3; color:#137A45; }
    .badge-status-declined { background:#F8D7DA; color:#B02A37; }
    .request-card { border:1px solid #E4E7ED; border-radius:8px; padding:16px; margin-bottom:12px; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:440px; max-width:90vw; padding:24px; max-height:85vh; overflow-y:auto; }
</style>

<div class="container-fluid">
    <h2 class="mb-1">Barangay Assistance Requests</h2>
    <div class="text-muted mb-4">Ask a neighboring barangay for help on an incident, or respond to requests sent to you</div>

    @if (isset($ownOpenIncidents) && $ownOpenIncidents->isNotEmpty())
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Request Assistance</h5>
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted">Your incident</label>
                    <select id="requestIncidentId" class="form-select">
                        @foreach ($ownOpenIncidents as $inc)
                            <option value="{{ $inc->id }}">#{{ $inc->id }} — {{ $inc->type }} ({{ $inc->status }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted">Ask which barangay?</label>
                    <select id="requestTargetBarangay" class="form-select">
                        @foreach ($otherBarangays as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn w-100" style="background:#1CA97B; color:#fff;" onclick="submitAssistanceRequest()">Send Request</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (backpack_user()->isSuperAdmin())
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-3">All Assistance Requests ({{ $incoming->where('status', 'pending')->count() }} pending)</h4>
            @if ($incoming->isEmpty())
                <div class="text-muted small">No assistance requests yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Incident</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Requested By</th>
                                <th>Status</th>
                                <th>When</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($incoming as $req)
                                <tr>
                                    <td>#{{ $req->incident->id ?? '—' }} — {{ $req->incident->type ?? 'Incident deleted' }}</td>
                                    <td>{{ $req->requesting_barangay }}</td>
                                    <td>{{ $req->target_barangay }}</td>
                                    <td>{{ $req->requester->name ?? 'Unknown' }}</td>
                                    <td>
                                        <span class="badge badge-status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                                        @if ($req->status === 'declined' && $req->decline_reason)
                                            <div class="small text-danger mt-1">{{ $req->decline_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $req->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if ($req->status === 'pending')
                                            <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;" onclick="openDecline({{ $req->id }})" title="Use this if the request was sent to the wrong barangay">Decline (Wrong Barangay)</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @else
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-3">Incoming ({{ $incoming->where('status', 'pending')->count() }} pending)</h4>
            @forelse ($incoming as $req)
                <div class="request-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold">#{{ $req->incident->id ?? '—' }} — {{ $req->incident->type ?? 'Incident deleted' }}</div>
                            <div class="text-muted small">From {{ $req->requesting_barangay }} · requested by {{ $req->requester->name ?? 'Unknown' }} · {{ $req->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="badge badge-status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                    </div>

                    @if ($req->incident)
                        <div class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($req->incident->description, 140) }}</div>
                    @endif

                    @if ($req->status === 'declined' && $req->decline_reason)
                        <div class="small text-danger mb-2">Declined: {{ $req->decline_reason }}</div>
                    @endif

                    @if ($req->status === 'pending')
                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-sm" style="background:#1CA97B; color:#fff;" onclick="acceptRequest({{ $req->id }})">Accept</button>
                            <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;" onclick="openDecline({{ $req->id }})">Decline</button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-muted small">No incoming requests.</div>
            @endforelse
        </div>
    </div>
    @endif

    @if (isset($sent) && $sent->isNotEmpty())
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="mb-3">Sent</h4>
            @foreach ($sent as $req)
                <div class="request-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold">#{{ $req->incident->id ?? '—' }} — {{ $req->incident->type ?? 'Incident deleted' }}</div>
                            <div class="text-muted small">To {{ $req->target_barangay }} · {{ $req->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="badge badge-status-{{ $req->status }}">{{ ucfirst($req->status) }}</span>
                    </div>
                    @if ($req->status === 'declined' && $req->decline_reason)
                        <div class="small text-danger">Declined: {{ $req->decline_reason }}</div>
                    @endif
                    @if ($req->status === 'accepted')
                        <div class="small text-success">Accepted by {{ $req->responder->name ?? $req->target_barangay }} — this incident is now scoped to {{ $req->target_barangay }}.</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="modal-backdrop-custom" id="declineModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4>Decline Request</h4>
            <button class="btn-close" onclick="closeModal('declineModal')"></button>
        </div>
        <div class="small text-muted mb-2">Please provide a reason (minimum 10 characters).</div>
        <textarea id="declineReason" class="form-control mb-2" rows="3" placeholder="e.g. vehicle already responding elsewhere..."></textarea>
        <div id="declineError" class="text-danger small mb-2" style="display:none;">Reason must be at least 10 characters.</div>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light" onclick="closeModal('declineModal')">Cancel</button>
            <button class="btn" style="background:#B02A37; color:#fff;" onclick="confirmDecline()">Confirm Decline</button>
        </div>
    </div>
</div>

<div id="toast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:2000; min-width:300px; padding:16px 20px; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.2); color:#fff; font-weight:500;"></div>

<script>
    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.style.background = type === 'success' ? '#1CA97B' : '#D63939';
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    function submitAssistanceRequest() {
        const incidentId = document.getElementById('requestIncidentId')?.value;
        const targetBarangay = document.getElementById('requestTargetBarangay')?.value;
        if (!incidentId || !targetBarangay) return;

        fetch(`/admin/incidents/${incidentId}/request-assistance`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_barangay: targetBarangay }),
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Request failed');
            showToast('Assistance request sent.');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch((e) => showToast(e.message, 'error'));
    }

    function acceptRequest(id) {
        fetch(`/admin/assistance-requests/${id}/accept`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Accept failed');
            showToast('Request accepted — incident is now yours.');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch((e) => showToast(e.message, 'error'));
    }

    let declineTargetId = null;

    function openDecline(id) {
        declineTargetId = id;
        document.getElementById('declineReason').value = '';
        document.getElementById('declineError').style.display = 'none';
        openModal('declineModal');
    }

    function confirmDecline() {
        const reason = document.getElementById('declineReason').value.trim();
        if (reason.length < 10) {
            document.getElementById('declineError').style.display = 'block';
            return;
        }

        fetch(`/admin/assistance-requests/${declineTargetId}/decline`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Content-Type': 'application/json' },
            body: JSON.stringify({ reason }),
        })
        .then(async (res) => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Decline failed');
            closeModal('declineModal');
            showToast('Request declined.');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch((e) => showToast(e.message, 'error'));
    }
</script>
@endsection