@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        'SREA' => backpack_url('dashboard'),
        'Responders Reports' => false,
    ];
@endphp

@section('content')
<style>
    .badge-status-Resolved  { background:#D9F2E3; color:#137A45; }
    .badge-status-Escalated { background:#FDE8E8; color:#B02A37; }
    .badge-status-Responding { background:#E7E9FB; color:#4C51BF; }
    .report-photo-placeholder {
        width: 100%; height: 120px; background: #EEF0F4; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #8A8F98; font-size: 13px; overflow: hidden;
    }
    .report-photo-placeholder img { width: 100%; height: 100%; object-fit: cover; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:480px; max-width:90vw; padding:24px; max-height:85vh; overflow-y:auto; }
</style>

<h2 class="mb-1">Responders Reports</h2>
<div class="text-muted mb-4">Field reports filed by responders after handling an incident</div>

<div class="row row-cards">
    @forelse ($reports as $report)
        <div class="col-lg-4">
            <div class="card h-100" style="cursor:pointer;" onclick="openReport({{ $report->id }})">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold">#{{ $report->id }} — {{ $report->type }}</div>
                            <div class="text-muted small">{{ $report->barangay }} · Filed by {{ $report->assignedTo->name ?? 'Unknown' }}</div>
                        </div>
                        <span class="badge badge-status-{{ $report->status }}">{{ $report->status }}</span>
                    </div>

                    <div class="report-photo-placeholder mb-2">
                        @if ($report->photo_path)
                            <img src="{{ $report->photo_path }}" alt="photo">
                        @else
                            No photo attached
                        @endif
                    </div>

                    <div class="small">{{ \Illuminate\Support\Str::limit($report->description, 90) }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-4">No field reports filed yet.</div>
    @endforelse
</div>

{{-- Report Detail Modal --}}
<div class="modal-backdrop-custom" id="reportModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="reportModalTitle">Report Details</h4>
            <button class="btn-close" onclick="closeReportModal()"></button>
        </div>
        <div id="reportModalBody"></div>
        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-light" onclick="closeReportModal()">Close</button>
        </div>
    </div>
</div>

<script>
    const reports = @json($reports);

    function openReport(id) {
        const r = reports.find(rep => rep.id === id);
        if (!r) return;

        document.getElementById('reportModalTitle').textContent = `#${r.id} — ${r.type}`;

        let escalationHtml = r.status === 'Escalated' ? `
            <div class="border rounded p-2 mb-2" style="background:#FDE8E8;">
                <div class="small fw-bold text-danger">Escalated</div>
                <div class="small">${r.escalation_reason ?? ''}</div>
                <div class="small text-muted">By ${r.escalated_by?.name ?? '-'} · ${r.escalated_at ?? '-'}</div>
            </div>` : '';

        let resolutionHtml = r.status === 'Resolved' ? `
            <div class="border rounded p-2 mb-2" style="background:#D9F2E3;">
                <div class="small fw-bold text-success">Resolved</div>
                <div class="small">${r.resolution_notes ?? ''}</div>
                <div class="small text-muted">${r.resolved_at ?? '-'}</div>
            </div>` : '';

        document.getElementById('reportModalBody').innerHTML = `
            <div class="text-muted small mb-2">Barangay: ${r.barangay} · Filed by: ${r.assigned_to?.name ?? 'Unknown'}</div>
            <div class="mb-2"><strong>Description</strong><div class="small">${r.description}</div></div>
            <div class="mb-2"><strong>Responder Notes</strong><div class="small">${r.responder_notes ?? '-'}</div></div>
            ${escalationHtml}
            ${resolutionHtml}
        `;
        document.getElementById('reportModal').classList.add('show');
    }

    function closeReportModal() {
        document.getElementById('reportModal').classList.remove('show');
    }
</script>
@endsection