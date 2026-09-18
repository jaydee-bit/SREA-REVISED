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
    .page-btn.active { background:#1CA97B; color:#fff; border-color:#1CA97B; }
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-start mb-4">
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
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th>Reported</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="incidentsTableBody">
                    <tr><td colspan="10" class="text-center text-muted py-4">Loading…</td></tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="small text-muted" id="paginationSummary"></div>
            <div class="btn-group btn-group-sm" id="paginationControls"></div>
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

{{-- Request Assistance Modal --}}
<div class="modal-backdrop-custom" id="assistanceModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 id="assistanceModalTitle">Request Assistance</h4>
            <button class="btn-close" onclick="document.getElementById('assistanceModal').classList.remove('show')"></button>
        </div>
        <div id="assistanceIncidentContext" class="d-flex align-items-center gap-2 mb-3 p-2" style="background:#F7F8FA; border-radius:6px;"></div>
        <div class="small text-muted mb-2">Which barangay should respond to this incident?</div>
        <select id="assistanceTargetBarangay" class="form-select mb-2">
            <option value="">Select a barangay…</option>
            @foreach ($barangays as $barangay)
                <option value="{{ $barangay }}">{{ $barangay }}</option>
            @endforeach
        </select>
        <div id="assistanceError" class="text-danger small mb-2" style="display:none;"></div>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light" onclick="document.getElementById('assistanceModal').classList.remove('show')">Cancel</button>
            <button class="btn" style="background:#4C5FD5; color:#fff;" onclick="confirmAssistanceRequest()">Send Request</button>
        </div>
    </div>
</div>


<div id="toast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:2000; min-width:300px; padding:16px 20px; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.2); color:#fff; font-weight:500;"></div>

<script>
    let currentPageIncidents = [];
    let currentPage = 1;
    let currentStatus = 'all';
    let currentSearch = '';
    let searchDebounce = null;

    function statusBadge(incident) {
        let html = `<span class="badge badge-status-${incident.status}">${incident.status}</span>`;
        if (incident.nearby_count > 0) {
            html += ` <span class="badge badge-nearby">⚠️ ${incident.nearby_count} nearby</span>`;
        }
        return html;
    }

    function mediaThumb(incident) {
        if (incident.photo_path) return `<img src="${incident.photo_path}" class="media-thumb" alt="photo">`;
        if (incident.video_path) return `<div class="media-thumb d-flex align-items-center justify-content-center">🎥</div>`;
        return `<div class="media-thumb d-flex align-items-center justify-content-center text-muted">—</div>`;
    }

    function renderRows(incidents) {
        const tbody = document.getElementById('incidentsTableBody');
        if (incidents.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-4">No incidents found.</td></tr>`;
            return;
        }
        tbody.innerHTML = incidents.map(inc => `
            <tr>
                <td>${mediaThumb(inc)}</td>
                <td>#${inc.id}</td>
                <td>${inc.type}</td>
                <td>${inc.barangay}</td>
                <td>${inc.reporter_name ?? inc.reporter?.name ?? 'Anonymous'}</td>
                <td>${inc.contact_number ?? '-'}</td>
                <td>${statusBadge(inc)}</td>
                <td>${inc.assigned_to?.name ?? '-'}</td>
                <td>${new Date(inc.reported_at).toLocaleString()}</td>
                <td>
                    <button class="btn btn-sm btn-light" onclick="viewDescription(${inc.id})">View</button>
                    ${inc.status === 'Pending' ? `<button class="btn btn-sm" style="background:#E7E9FB; color:#4C5FD5;" onclick="openAssistanceRequest(${inc.id})">🆘 Assistance</button>` : ''}
                    ${inc.status === 'Pending' ? `<button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;" onclick="openReject(${inc.id})">Reject</button>` : ''}
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(meta) {
        document.getElementById('paginationSummary').textContent =
            `Showing ${meta.from ?? 0}–${meta.to ?? 0} of ${meta.total} incidents`;

        const controls = document.getElementById('paginationControls');
        let html = '';
        html += `<button class="btn btn-outline-secondary page-btn" ${meta.current_page <= 1 ? 'disabled' : ''} onclick="goToPage(${meta.current_page - 1})">Prev</button>`;
        html += `<button class="btn btn-outline-secondary page-btn active" disabled>${meta.current_page} / ${meta.last_page}</button>`;
        html += `<button class="btn btn-outline-secondary page-btn" ${meta.current_page >= meta.last_page ? 'disabled' : ''} onclick="goToPage(${meta.current_page + 1})">Next</button>`;
        controls.innerHTML = html;
    }

    function goToPage(page) {
        currentPage = page;
        loadIncidents();
    }

    function loadIncidents() {
        const params = new URLSearchParams({
            page: currentPage,
            status: currentStatus,
            search: currentSearch,
        });

        fetch(`{{ route('incidents.data') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                currentPageIncidents = data.data;
                renderRows(currentPageIncidents);
                renderPagination(data);
            })
            .catch(() => {
                document.getElementById('incidentsTableBody').innerHTML =
                    `<tr><td colspan="10" class="text-center text-danger py-4">Failed to load incidents.</td></tr>`;
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadIncidents();

        document.querySelectorAll('#statusTabs button').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('#statusTabs button').forEach(t => {
                    t.classList.remove('active', 'btn-success');
                    t.classList.add('btn-outline-secondary');
                });
                tab.classList.remove('btn-outline-secondary');
                tab.classList.add('active', 'btn-success');
                currentStatus = tab.dataset.filter;
                currentPage = 1;
                loadIncidents();
            });
        });

        document.getElementById('incidentSearch').addEventListener('input', (e) => {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                currentSearch = e.target.value;
                currentPage = 1;
                loadIncidents();
            }, 300);
        });
    });

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.style.background = type === 'success' ? '#1CA97B' : '#D63939';
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    function viewDescription(incidentId) {
        const inc = currentPageIncidents.find(i => i.id === incidentId);
        if (!inc) return;

        document.getElementById('descModalTitle').textContent = `#${inc.id} — Incident Details`;

        let mediaHtml = '';
        if (inc.photo_path) {
            mediaHtml = `<img src="${inc.photo_path}" style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;" class="mb-3" alt="Reported photo">`;
        } else if (inc.video_path) {
            mediaHtml = `<video src="${inc.video_path}" controls style="width:100%; max-height:220px; border-radius:8px;" class="mb-3"></video>`;
        }

        const reporterName = inc.reporter_name ?? inc.reporter?.name ?? 'Anonymous';
        const assignedHtml = inc.assigned_to
            ? `<div class="small mb-1"><strong>Assigned responder:</strong> ${inc.assigned_to.name}</div>`
            : '';

        document.getElementById('descModalBody').innerHTML = `
            <div class="mb-2 d-flex align-items-center gap-2">
                <span class="badge badge-status-${inc.status}">${inc.status}</span>
                <span class="text-muted small">${inc.type} · ${inc.barangay}</span>
            </div>
            ${mediaHtml}
            <div class="small mb-3">${inc.description ?? 'No description available.'}</div>
            <hr>
            <div class="small mb-1"><strong>Reporter:</strong> ${reporterName}</div>
            <div class="small mb-1"><strong>Contact:</strong> ${inc.contact_number ?? '-'}</div>
            <div class="small mb-1"><strong>Reported at:</strong> ${new Date(inc.reported_at).toLocaleString()}</div>
            ${assignedHtml}
        `;
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
            setTimeout(() => loadIncidents(), 1000);
        })
        .catch(() => {
            document.getElementById('rejectError').textContent = 'Something went wrong. Please try again.';
            document.getElementById('rejectError').style.display = 'block';
        });
    }

    let assistanceTargetId = null;

    function openAssistanceRequest(incidentId) {
        assistanceTargetId = incidentId;
        const inc = currentPageIncidents.find(i => i.id === incidentId);

        document.getElementById('assistanceModalTitle').textContent = `Request Assistance — #${incidentId}`;
        document.getElementById('assistanceError').style.display = 'none';

        const contextEl = document.getElementById('assistanceIncidentContext');
        contextEl.innerHTML = inc ? `
            ${mediaThumb(inc)}
            <div>
                <div class="fw-bold small">${inc.type} — ${inc.barangay}</div>
                <div class="text-muted small">This incident currently belongs to ${inc.barangay}.</div>
            </div>
        ` : '';

        const select = document.getElementById('assistanceTargetBarangay');
        select.value = '';
        Array.from(select.options).forEach(opt => {
            opt.disabled = !!(inc && opt.value === inc.barangay);
        });

        document.getElementById('assistanceModal').classList.add('show');
    }

    function confirmAssistanceRequest() {
        const targetBarangay = document.getElementById('assistanceTargetBarangay').value;
        const errorEl = document.getElementById('assistanceError');

        if (!targetBarangay) {
            errorEl.textContent = 'Please select a barangay.';
            errorEl.style.display = 'block';
            return;
        }

        fetch(`/admin/incidents/${assistanceTargetId}/request-assistance`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ target_barangay: targetBarangay }),
        })
        .then(async res => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Something went wrong. Please try again.');
            return data;
        })
        .then(() => {
            document.getElementById('assistanceModal').classList.remove('show');
            showToast(`Assistance requested for incident #${assistanceTargetId}.`);
        })
        .catch(err => {
            errorEl.textContent = err.message;
            errorEl.style.display = 'block';
        });
    }
</script>
@endsection