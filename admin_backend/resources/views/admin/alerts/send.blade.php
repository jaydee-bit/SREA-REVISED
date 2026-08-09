@extends(backpack_view('blank'))

@php
    $breadcrumbs = ['SREA' => backpack_url('dashboard'), 'Send Alert' => false];
@endphp

@section('content')
<style>
    .alert-tabs a { padding:8px 16px; border-radius:8px; text-decoration:none; color:#5C6270; }
    .alert-tabs a.active { background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.15); color:#111; font-weight:600; }
    .category-btn { border:1px solid #D9DCE3; border-radius:20px; padding:6px 14px; background:#fff; font-size:13px; cursor:pointer; }
    .category-btn.active { background:#111827; color:#fff; border-color:#111827; }
    .badge-target { background:#E7EEFC; color:#2C5AC7; }
</style>

<h2 class="mb-1">Send Alert & Announcement</h2>
<div class="text-muted mb-3">Broadcast disaster notifications to all users</div>

<div class="alert-tabs d-inline-flex gap-2 border rounded p-1 mb-4" style="background:#F0F2F7;">
    <a href="{{ backpack_url('send-alert') }}" class="active"> Send Alert</a>
    <a href="{{ backpack_url('traffic-advisory') }}">Traffic Advisory</a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Compose Alert</h5>
                <div class="small text-muted mb-1">ASSIGNED</div>
                <div class="d-flex flex-wrap gap-2 mb-3" id="categoryButtons">
                    <button type="button" class="category-btn" data-category="flood">Flood</button>
                    <button type="button" class="category-btn" data-category="fire">Fire</button>
                    <button type="button" class="category-btn" data-category="landslide"> Landslide</button>
                    <button type="button" class="category-btn" data-category="earthquake">Earthquake</button>
                    <button type="button" class="category-btn" data-category="typhoon"> Typhoon</button>
                    <button type="button" class="category-btn" data-category="general"> General</button>
                </div>

                <div class="small text-muted mb-1">Alert Message</div>
                <textarea id="alertMessage" class="form-control mb-3" rows="4" placeholder="Type you alert message here...."></textarea>

                <div class="small text-muted mb-1">Target Area</div>
                <select class="form-select mb-3">
                    <option>All Barangays - San Rafael</option>
                    @foreach (\App\Support\MockBarangays::all() as $b)
                        <option>{{ $b['name'] }}</option>
                    @endforeach
                </select>

                <div class="small text-muted mb-2">Send To</div>
                <div class="d-flex gap-3 mb-3">
                    <label class="d-flex align-items-center gap-1"><input type="checkbox" checked> Public User</label>
                    <label class="d-flex align-items-center gap-1"><input type="checkbox" checked> Responders</label>
                </div>

                <button type="button" class="btn w-100" style="background:#1CA97B; color:#fff;" onclick="broadcastAlert()">  BROADCAST ALERT</button>
                <div id="broadcastConfirm" class="alert alert-success mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Recent Traffic Advisory</h6>
                    <a href="{{ backpack_url('traffic-advisory') }}" class="small">See All</a>
                </div>
                @foreach ($recentAdvisories as $adv)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-bold small" style="color:{{ $adv['status'] === 'active' ? '#D63939' : '#F76707' }};">{{ $adv['title'] }}</div>
                        <div class="small text-muted">{{ $adv['road'] }}, {{ $adv['title'] }}</div>
                        <div class="small text-muted">Sent to {{ number_format($adv['users_reached']) }} users · {{ $adv['target'] }} &nbsp; <span class="float-end">{{ $adv['time_ago'] }}</span></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Broadcast Stats</h6>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted small">Total Users Reached</span>
                    <span class="fw-bold text-success">{{ number_format($stats['total_reached']) }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted small">Sent Today</span>
                    <span class="fw-bold text-danger">{{ $stats['sent_today'] }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted small">Open Rate</span>
                    <span class="fw-bold text-warning">{{ $stats['open_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Announcement</h6>
        <input type="text" class="form-control form-control-sm" style="width:220px;" placeholder="Search">
    </div>
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>ID</th><th>Title</th><th>Preview</th><th>Target</th><th>Posted By</th><th>Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($announcements as $a)
                    <tr>
                        <td class="text-success">{{ $a['id'] }}</td>
                        <td class="fw-bold">{{ $a['title'] }}</td>
                        <td class="small text-muted">{{ $a['preview'] }}</td>
                        <td><span class="badge badge-target">{{ $a['target'] }}</span></td>
                        <td>{{ $a['posted_by'] }}</td>
                        <td>{{ $a['date'] }}</td>
                        <td>
                            <button class="btn btn-sm btn-light">Edit</button>
                            <button class="btn btn-sm" style="background:#F8D7DA; color:#B02A37;">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    const templates = {
        flood: 'FLOOD WARNING: Rising water levels detected. Residents in low-lying areas are advised to move to higher ground immediately. Stay tuned for updates.',
        fire: 'FIRE ALERT: A fire incident has been reported in your area. Please evacuate calmly and avoid the affected zone until further notice.',
        landslide: 'LANDSLIDE WARNING: Ground movement detected in your barangay. Residents near slopes are advised to evacuate to designated safe areas.',
        earthquake: 'EARTHQUAKE ADVISORY: A significant earthquake has occurred. Check your surroundings for damage and avoid entering weakened structures.',
        typhoon: 'TYPHOON ADVISORY: A typhoon signal has been raised for our municipality. Secure your homes and prepare emergency supplies.',
        general: 'ANNOUNCEMENT: Please be advised of the following important update from MDRRMO San Rafael.',
    };

    document.querySelectorAll('#categoryButtons button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#categoryButtons button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('alertMessage').value = templates[btn.dataset.category] || '';
        });
    });

    function broadcastAlert() {
        const msg = document.getElementById('alertMessage').value.trim();
        const box = document.getElementById('broadcastConfirm');
        if (!msg) {
            box.className = 'alert alert-danger mt-3';
            box.textContent = 'Please select a category or write a message before broadcasting.';
        } else {
            box.className = 'alert alert-success mt-3';
            box.textContent = 'Alert broadcasted (mock) — no real notification was sent.';
        }
        box.style.display = 'block';
    }
</script>
@endsection