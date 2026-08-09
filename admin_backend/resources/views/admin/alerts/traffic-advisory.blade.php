@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Send Alert' => false];
@endphp

@section('content')
<style>
    .alert-tabs a { padding:8px 16px; border-radius:8px; text-decoration:none; color:#5C6270; }
    .alert-tabs a.active { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.15); color:#111; font-weight:600; }
    .badge-target { background:#E7EEFC; color:#2C5AC7; }
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
        <a href="{{ backpack_url('send-alert') }}"> Send Alert</a>
        <a href="{{ backpack_url('traffic-advisory') }}" class="active"> Traffic Advisory</a>
    </div>
    <button type="button" class="btn" style="background:#1CA97B; color:#fff;" onclick="openAdvisoryModal()">+ Add Traffic Advisory</button>
</div>
<div class="text-muted small mb-3">Manage active road warnings and closures per barangay</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Announcement</h6>
        <input type="text" class="form-control form-control-sm" style="width:220px;" placeholder="Search">
    </div>
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>ID</th><th>Title</th><th>Road / Area</th><th>Target</th><th>Status</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($advisories as $adv)
                    <tr>
                        <td class="text-success">{{ $adv['id'] }}</td>
                        <td class="fw-bold">{{ $adv['title'] }}</td>
                        <td>{{ $adv['road'] }}</td>
                        <td><span class="badge badge-target">{{ $adv['target'] }}</span></td>
                        <td><span class="badge badge-tstatus-{{ $adv['status'] }}">{{ ucfirst($adv['status']) }}</span></td>
                        <td>{{ $adv['date'] }}</td>
                        <td>
                            @if ($adv['status'] === 'active')
                                <button class="btn btn-sm btn-light">Edit</button>
                                <button class="btn btn-sm btn-outline-success">Clear</button>
                                <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;">Delete</button>
                            @else
                                <button class="btn btn-sm btn-light">View</button>
                                <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;">Delete</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
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
        <input type="text" class="form-control mb-3" placeholder="e.g Road Closure - Flooding">
        <div class="small text-muted mb-1">Road / Area Affected</div>
        <input type="text" class="form-control mb-3" placeholder="e.g Banca-Banca Road">
        <div class="small text-muted mb-1">Content</div>
        <textarea class="form-control mb-3" rows="3" placeholder="Describe the traffic situation...."></textarea>
        <div class="small text-muted mb-1">Target Barangay</div>
        <select class="form-select mb-3">
            <option>All Barangays - San Rafael</option>
            @foreach (\App\Support\MockBarangays::all() as $b)
                <option>{{ $b['name'] }}</option>
            @endforeach
        </select>
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-light" onclick="closeAdvisoryModal()">Cancel</button>
            <button class="btn" style="background:#1CA97B; color:#fff;" onclick="closeAdvisoryModal()">Save</button>
        </div>
    </div>
</div>

<script>
    function openAdvisoryModal() { document.getElementById('advisoryModal').classList.add('show'); }
    function closeAdvisoryModal() { document.getElementById('advisoryModal').classList.remove('show'); }
</script>
@endsection