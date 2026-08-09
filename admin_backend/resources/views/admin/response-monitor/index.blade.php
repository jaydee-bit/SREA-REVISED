@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        'SREA' => backpack_url('dashboard'),
        'Assign & Dispatch' => false,
    ];
@endphp

@section('content')
<style>
    .stage-track { display: flex; align-items: center; margin-top: 12px; }
    .stage-dot { width: 12px; height: 12px; border-radius: 50%; background: #D9DCE3; flex-shrink: 0; }
    .stage-dot.done { background: #2FB344; }
    .stage-dot.current { background: #F59F00; }
    .stage-line { flex: 1; height: 3px; background: #D9DCE3; }
    .stage-line.done { background: #2FB344; }
    .stage-labels { display: flex; justify-content: space-between; font-size: 11px; color: #8A8F98; margin-top: 4px; }
    .badge-level-critical { background:#D63939; color:#fff; }
    .badge-level-high     { background:#F76707; color:#fff; }
    .badge-level-medium   { background:#F59F00; color:#fff; }
    .badge-level-low      { background:#2FB344; color:#fff; }
    .badge-status-responding { background:#FDE8E8; color:#D63939; }
    .badge-status-waiting    { background:#E7E9FB; color:#4C51BF; }
    .waiting-card { background:#F8F5F0; border:1px solid #EFE6D8; border-radius:8px; padding:16px; margin-bottom:12px; }
    .dispatch-btn { background:#F7D9B0; color:#7A4A00; border:none; width:100%; padding:10px; border-radius:6px; font-weight:500; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:420px; max-width:90vw; padding:24px; }
</style>

<div class="container-fluid">
    <h2 class="mb-1">Response Monitor</h2>
    <div class="text-muted mb-4">Live status of all active incidents and assigned responders</div>

    {{-- Waiting Response --}}
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="text-warning mb-3">⚠ Waiting Response ({{ $waiting->count() }})</h4>
            @foreach ($waiting as $incident)
                <div class="waiting-card">
                    <div class="fw-bold">{{ $incident['type'] }}</div>
                    <div class="text-muted small mb-2">{{ $incident['id'] }} &nbsp;·&nbsp; Brgy. {{ $incident['barangay'] }} &nbsp;·&nbsp; Reporter: {{ $incident['reporter'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Active Assignments --}}
    <div class="card">
        <div class="card-body">
            <h4 class="text-success mb-3">✓ Active Assignments ({{ $active->count() }})</h4>
            @foreach ($active as $incident)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fw-bold">{{ $incident['id'] }}</span>
                        <span class="badge badge-level-{{ $incident['level'] }}">{{ ucfirst($incident['level']) }}</span>
                        <span class="badge badge-status-{{ $incident['status'] }}">Responding</span>
                    </div>
                    <div class="text-muted small mb-3">{{ strtoupper($incident['type']) }} &nbsp;·&nbsp; 📍 {{ $incident['coordinates'] }} &nbsp;·&nbsp; Reporter: {{ $incident['reporter'] }}</div>

                    <div class="bg-light rounded p-3">
                        <div class="text-muted small mb-1">Assigned Responder</div>
                        <div class="fw-bold mb-2">{{ $incident['assigned']['name'] }} <span class="text-muted fw-normal">— {{ $incident['assigned']['team'] }}</span></div>

                        <div class="row text-center small mb-2">
                            <div class="col">
                                <div class="text-muted">Current Status</div>
                                <div class="fw-bold">{{ $incident['assigned']['current_status'] }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted">Location</div>
                                <div class="fw-bold">📍 {{ $incident['assigned']['location'] }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted">{{ $incident['assigned']['metric_label'] }}</div>
                                <div class="fw-bold">{{ $incident['assigned']['metric_value'] }}</div>
                            </div>
                            <div class="col">
                                <div class="text-muted">Last Update</div>
                                <div class="fw-bold">{{ $incident['assigned']['last_update'] }}</div>
                            </div>
                        </div>

                        @php $stage = $incident['stage']; $stages = ['Assigned','En Route','On Scene','Resolved']; @endphp
                        <div class="stage-track">
                            @foreach ($stages as $i => $label)
                                <div class="stage-dot {{ $i < $stage ? 'done' : ($i == $stage ? 'current' : '') }}"></div>
                                @if (!$loop->last)
                                    <div class="stage-line {{ $i < $stage ? 'done' : '' }}"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="stage-labels">
                            @foreach ($stages as $label)
                                <span>{{ $label }}</span>
                            @endforeach
                        </div>
                    </div>

                    <button class="btn btn-sm btn-light mt-2" onclick="openIncidentDetails('{{ $incident['id'] }}')">View Details</button>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Incident Details Modal --}}
<div class="modal-backdrop-custom" id="incidentModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="incidentModalTitle">Incident Details</h4>
            <button class="btn-close" onclick="closeModal('incidentModal')"></button>
        </div>
        <div id="incidentModalBody"></div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-light" onclick="closeModal('incidentModal')">Close</button>
            <button class="btn" style="background:#F59F00;color:#fff;">Reassign</button>
        </div>
    </div>
</div>

{{-- Dispatch Modal --}}
<div class="modal-backdrop-custom" id="dispatchModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4>Dispatch Responder</h4>
            <button class="btn-close" onclick="closeModal('dispatchModal')"></button>
        </div>
        <div id="dispatchModalBody"></div>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <button class="btn btn-light" onclick="closeModal('dispatchModal')">Cancel</button>
            <button class="btn" style="background:#1CA97B;color:#fff;">Confirm Dispatch</button>
        </div>
    </div>
</div>

<script>
    const incidents = @json($incidents);

    function openModal(id) { document.getElementById(id).classList.add('show'); }
    function closeModal(id) { document.getElementById(id).classList.remove('show'); }

    function openIncidentDetails(incidentId) {
        const inc = incidents.find(i => i.id === incidentId);
        if (!inc) return;
        document.getElementById('incidentModalTitle').textContent = `Incident Details — ${inc.id}`;
        document.getElementById('incidentModalBody').innerHTML = `
            <div class="bg-light rounded p-2 mb-2">
                <strong>${inc.type} — ${inc.barangay}</strong><br>
                <small class="text-muted">Barangay: ${inc.barangay} &nbsp; Reporter: ${inc.reporter}<br>
                Contact: ${inc.contact} &nbsp; Coordinates: ${inc.coordinates}</small>
            </div>
            ${inc.assigned ? `<div class="small">Assigned: <strong>${inc.assigned.name}</strong> — ${inc.assigned.team}</div>` : '<div class="small text-muted">No responder assigned yet.</div>'}
        `;
        openModal('incidentModal');
    }

    function openDispatch(incidentId) {
        const inc = incidents.find(i => i.id === incidentId);
        if (!inc) return;
        let rows = (inc.standby || []).map(r => `
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                <div><strong>${r.name}</strong><br><small class="text-muted">${r.team}</small></div>
                <span class="badge bg-secondary">Standby</span>
            </div>
        `).join('');
        document.getElementById('dispatchModalBody').innerHTML = `
            <div class="text-muted small mb-2">${inc.id} — ${inc.type} @ Brgy. ${inc.barangay}</div>
            <div class="text-muted small mb-2">Available — Standby Responders</div>
            ${rows || '<div class="text-muted small">No standby responders available.</div>'}
        `;
        openModal('dispatchModal');
    }
</script>
@endsection