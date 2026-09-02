@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Send Alert' => false];
@endphp

@section('content')
<style>
    .alert-tabs a { padding:8px 16px; border-radius:8px; text-decoration:none; color:#5C6270; }
    .alert-tabs a.active { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.15); color:#111; font-weight:600; }
    .badge-tstatus-active  { background:#FDE8E8; color:#D63939; }
    .badge-tstatus-cleared { background:#D9F2E3; color:#137A45; }
    .modal-backdrop-custom { position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:1050; }
    .modal-backdrop-custom.show { display:flex; }
    .modal-box { background:#fff; border-radius:10px; width:420px; max-width:90vw; padding:24px; }
</style>

<h2 class="mb-1">Send Alert & Announcement</h2>
<div class="text-muted mb-3">Broadcast disaster alerts and manage road warnings for San Rafael</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="alert-tabs d-inline-flex gap-2 border rounded p-1" style="background:#F0F2F7;">
        <a href="{{ backpack_url('send-alert') }}">📣 Send Alert</a>
        <a href="{{ backpack_url('traffic-advisory') }}" class="active">🚧 Traffic Advisory</a>
    </div>
    <button type="button" class="btn" style="background:#1CA97B; color:#fff;" onclick="openAdvisoryModal()">+ Add Traffic Advisory</button>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Traffic Advisories</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>Title</th><th>Location</th><th>Status</th><th>Posted By</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($advisories as $adv)
                    <tr>
                        <td class="fw-bold">{{ $adv->title }}</td>
                        <td>{{ $adv->location }}</td>
                        <td><span class="badge badge-tstatus-{{ $adv->is_active ? 'active' : 'cleared' }}">{{ $adv->is_active ? 'Active' : 'Cleared' }}</span></td>
                        <td>{{ $adv->creator->name ?? '-' }}</td>
                        <td>{{ $adv->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted small py-3">No traffic advisories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop-custom" id="advisoryModal">
    <div class="modal-box">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h4>Add Traffic Advisory</h4>
            <button class="btn-close" onclick="closeAdvisoryModal()"></button>
        </div>
        <div class="small text-muted mb-1">Title</div>
        <input type="text" id="advTitle" class="form-control mb-3" placeholder="e.g Road Closure - Flooding">
        <div class="small text-muted mb-1">Road / Area Affected</div>
        <input type="text" id="advLocation" class="form-control mb-3" placeholder="e.g Banca-Banca Road">
        <div class="small text-muted mb-1">Content</div>
        <textarea id="advDescription" class="form-control mb-3" rows="3" placeholder="Describe the traffic situation...."></textarea>
        <div id="advError" class="text-danger small mb-2" style="display:none;">Please fill in all fields.</div>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light" onclick="closeAdvisoryModal()">Cancel</button>
            <button class="btn" style="background:#1CA97B; color:#fff;" onclick="submitAdvisory()">Save</button>
        </div>
    </div>
</div>

<script>
    function openAdvisoryModal() { document.getElementById('advisoryModal').classList.add('show'); }
    function closeAdvisoryModal() { document.getElementById('advisoryModal').classList.remove('show'); }

    function submitAdvisory() {
        const title = document.getElementById('advTitle').value.trim();
        const location = document.getElementById('advLocation').value.trim();
        const description = document.getElementById('advDescription').value.trim();

        if (!title || !location || !description) {
            document.getElementById('advError').style.display = 'block';
            return;
        }

        fetch("{{ route('traffic-advisory.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ title, location, description }),
        })
        .then(res => res.json())
        .then(() => window.location.reload())
        .catch(() => alert('Failed to save advisory. Please try again.'));
    }
</script>
@endsection