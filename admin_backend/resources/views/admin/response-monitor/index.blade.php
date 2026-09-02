@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Assign & Dispatch' => false];
@endphp

@section('content')
<style>
    .badge-status-Responding { background:#FDE8E8; color:#D63939; }
    .badge-status-Pending    { background:#E7E9FB; color:#4C51BF; }
    .badge-status-Escalated  { background:#FFF3CD; color:#856404; }
    .waiting-card { background:#F8F5F0; border:1px solid #EFE6D8; border-radius:8px; padding:16px; margin-bottom:12px; }
    .dispatch-btn { background:#F7D9B0; color:#7A4A00; border:none; width:100%; padding:10px; border-radius:6px; font-weight:500; }
    .escalation-note { background:#FFF3CD; border:1px solid #F0C36D; border-radius:8px; padding:10px; font-size:13px; margin-bottom:10px; }
    .media-thumb { width:60px; height:60px; border-radius:8px; object-fit:cover; background:#EEF0F4; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:420px; max-width:90vw; padding:24px; max-height:85vh; overflow-y:auto; }
</style>

<div class="container-fluid">
    <h2 class="mb-1">Response Monitor</h2>
    <div class="text-muted mb-4">Live status of all active incidents and assigned responders</div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="text-warning mb-3">⚠ Waiting Response ({{ $waiting->count() }})</h4>
            @foreach ($waiting as $incident)
                <div class="waiting-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $incident->type }}</div>
                            <div class="text-muted small mb-2">#{{ $incident->id }} &nbsp;·&nbsp; Brgy. {{ $incident->barangay }} &nbsp;·&nbsp; Reporter: {{ $incident->reporter_name ?? 'Anonymous' }}</div>
                        </div>
                        @if ($incident->photo_path)
                            <img src="{{ $incident->photo_path }}" class="media-thumb" alt="Incident photo">
                        @elseif ($incident->video_path)
                            <div class="media-thumb d-flex align-items-center justify-content-center">🎥</div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button class="dispatch-btn" onclick="openDispatch({{ $incident->id }})">⚡ Dispatch Responder</button>
                        <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37; white-space:nowrap;" onclick="openReject({{ $incident->id }})">Reject</button>
                    </div>
                </div>
            @endforeach
            @if ($waiting->isEmpty())
                <div class="text-muted small">No incidents waiting for response.</div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="text-success mb-3">✓ Active Assignments ({{ $active->count() }})</h4>
            @foreach ($active as $incident)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-bold">#{{ $incident->id }}</span>
                        <span class="badge badge-status-{{ $incident->status }}">{{ $incident->status }}</span>
                    </div>
                    <div class="text-muted small mb-3">{{ strtoupper($incident->type) }} &nbsp;·&nbsp; 📍 {{ $incident->address }} &nbsp;·&nbsp; Reporter: {{ $incident->reporter_name ?? 'Anonymous' }}</div>

                    @if ($incident->assignedTo)
                        <div class="bg-light rounded p-3">
                            <div class="text-muted small mb-1">Assigned Responder</div>
                            <div class="fw-bold mb-2">
                                {{ $incident->assignedTo->name }}
                                @if ($incident->assignedTo->responderProfile)
                                    <span class="text-muted fw-normal">— {{ $incident->assignedTo->responderProfile->team }} · {{ $incident->assignedTo->responderProfile->vehicle }}</span>
                                @endif
                            </div>

                            <div class="row text-center small mb-2">
                                <div class="col">
                                    <div class="text-muted">Status</div>
                                    <div class="fw-bold">{{ $incident->assignedTo->responderProfile->current_status ?? '-' }}</div>
                                </div>
                                <div class="col">
                                    <div class="text-muted">Reported</div>
                                    <div class="fw-bold">{{ $incident->reported_at->diffForHumans() }}</div>
                                </div>
                            </div>

                            @if ($incident->responder_notes)
                                <div class="small mt-2"><strong>Notes:</strong> {{ $incident->responder_notes }}</div>
                            @endif
                        </div>
                    @endif

                    <button class="btn btn-sm btn-light mt-2" onclick="openIncidentDetails({{ $incident->id }})">View Details</button>
                </div>
            @endforeach
            @if ($active->isEmpty())
                <div class="text-muted small">No active assignments.</div>
            @endif
        </div>
    </div>

    @if ($escalated->isNotEmpty())
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="mb-3" style="color:#856404;">⚠ Escalated ({{ $escalated->count() }})</h4>
                @foreach ($escalated as $incident)
                    <div class="border rounded p-3 mb-2">
                        <div class="fw-bold">#{{ $incident->id }} — {{ $incident->type }}</div>
                        <div class="text-muted small mb-2">Brgy. {{ $incident->barangay }}</div>
                        <div class="escalation-note">
                            Escalated by <strong>{{ $incident->escalatedBy->name ?? 'a responder' }}</strong> at {{ $incident->escalated_at?->format('g:i A') ?? '-' }}<br>
                            {{ $incident->escalation_reason }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<div class="modal-backdrop-custom" id="incidentModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="incidentModalTitle">Incident Details</h4>
            <button class="btn-close" onclick="closeModal('incidentModal')"></button>
        </div>
        <div id="incidentModalBody"></div>
        <button class="btn btn-light mt-3" onclick="closeModal('incidentModal')">Close</button>
    </div>
</div>

<div class="modal-backdrop-custom" id="rejectModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="rejectModalTitle">Reject Incident</h4>
            <button class="btn-close" onclick="closeModal('rejectModal')"></button>
        </div>
        <div class="small text-muted mb-2">Please provide a reason (minimum 10 characters).</div>
        <textarea id="rejectReason" class="form-control mb-2" rows="3" placeholder="Reason for rejecting this report..."></textarea>
        <div id="rejectError" class="text-danger small mb-2" style="display:none;"></div>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light" onclick="closeModal('rejectModal')">Cancel</button>
            <button class="btn" style="background:#B02A37;color:#fff;" onclick="confirmReject()">Confirm Reject</button>
        </div>
    </div>
</div>

<div id="toast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:2000; min-width:300px; padding:16px 20px; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.2); color:#fff; font-weight:500;"></div>

<script>
    const incidents = @json($incidents);
    const standbyResponders = @json($standbyResponders);
    let dispatchTargetId = null;

    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }
    let rejectTargetId = null;

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.style.background = type === 'success' ? '#1CA97B' : '#D63939';
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

function openReject(incidentId) {
    rejectTargetId = incidentId;
    document.getElementById('rejectModalTitle').textContent = `Reject #${incidentId}`;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectError').style.display = 'none';
    openModal('rejectModal');
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
        closeModal('rejectModal');
        showToast(`Incident #${rejectTargetId} rejected successfully.`);
        setTimeout(() => window.location.reload(), 1000);
    })
    .catch(() => {
        document.getElementById('rejectError').textContent = 'Something went wrong. Please try again.';
        document.getElementById('rejectError').style.display = 'block';
    });
}

    function openIncidentDetails(incidentId) {
        const inc = incidents.find(i => i.id === incidentId);
        if (!inc) return;

        let mediaHtml = '';
        if (inc.photo_path) {
            mediaHtml = `<img src="${inc.photo_path}" style="width:100%; height:160px; object-fit:cover; border-radius:8px;" class="mb-2">`;
        } else if (inc.video_path) {
            mediaHtml = `<video src="${inc.video_path}" style="width:100%; height:160px; border-radius:8px;" class="mb-2" controls></video>`;
        }

        document.getElementById('incidentModalTitle').textContent = `Incident #${inc.id}`;
        document.getElementById('incidentModalBody').innerHTML = `
            ${mediaHtml}
            <div class="bg-light rounded p-2 mb-2">
                <strong>${inc.type} — ${inc.barangay}</strong><br>
                <small class="text-muted">Reporter: ${inc.reporter_name ?? 'Anonymous'}<br>
                Address: ${inc.address ?? '-'}<br>
                Status: ${inc.status}</small>
            </div>
            <div class="small"><strong>Description</strong><br>${inc.description ?? '-'}</div>
        `;
        openModal('incidentModal');
    }

    function openDispatch(incidentId) {
        dispatchTargetId = incidentId;

        let rows = standbyResponders.map(r => `
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                <div>
                    <strong>${r.name}</strong><br>
                    <small class="text-muted">${r.responder_profile?.team ?? ''} · ${r.responder_profile?.vehicle ?? ''}</small>
                </div>
                <button class="btn btn-sm" style="background:#1CA97B; color:#fff;" onclick="confirmDispatch(${r.id})">Assign</button>
            </div>
        `).join('');

        document.getElementById('dispatchModalBody').innerHTML = rows || '<div class="text-muted small">No standby responders available right now.</div>';
        openModal('dispatchModal');
    }

    function confirmDispatch(responderId) {
        fetch(`/admin/response-monitor/${dispatchTargetId}/dispatch`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ responder_id: responderId }),
        })
        .then(res => res.json())
        .then(() => {
            closeModal('dispatchModal');
            window.location.reload();
        })
        .catch(e => alert('Dispatch failed: ' + e));
    }
</script>
@endsection