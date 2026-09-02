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
    <a href="{{ backpack_url('send-alert') }}" class="active">📣 Send Alert</a>
    <a href="{{ backpack_url('traffic-advisory') }}">🚧 Traffic Advisory</a>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Compose Alert</h5>

                <div class="small text-muted mb-1">Quick Templates</div>
                <div class="d-flex flex-wrap gap-2 mb-3" id="categoryButtons">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-category="flood">🌊 Flood</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-category="fire">🔥 Fire</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-category="landslide">⛰️ Landslide</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-category="earthquake">📍 Earthquake</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-category="typhoon">🌀 Typhoon</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-category="general">📢 General</button>
                </div>

                <div class="small text-muted mb-1">Title</div>
                <input type="text" id="alertTitle" class="form-control mb-3" placeholder="e.g. Flood Warning - Banca-Banca">

                <div class="small text-muted mb-1">Message</div>
                <textarea id="alertMessage" class="form-control mb-3" rows="4" placeholder="Type your alert message here..."></textarea>

                <div class="small text-muted mb-1">Target Area</div>
                <select id="alertBarangay" class="form-select mb-3">
                    <option>All Barangays - San Rafael</option>
                    @foreach (\App\Models\Barangay::orderBy('name')->get() as $b)
                        <option>{{ $b->name }}</option>
                    @endforeach
                </select>

                <button type="button" class="btn w-100" style="background:#1CA97B; color:#fff;" onclick="broadcastAlert()">📣 BROADCAST ALERT</button>
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
                @forelse ($recentAdvisories as $adv)
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-bold small">{{ $adv->title }}</div>
                        <div class="small text-muted">{{ $adv->location }}</div>
                        <div class="small text-muted">{{ $adv->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="small text-muted">No traffic advisories yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Broadcast Stats</h6>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted small">Total Alerts Sent</span>
                    <span class="fw-bold text-success">{{ $stats['total_alerts'] }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted small">Sent Today</span>
                    <span class="fw-bold text-danger">{{ $stats['sent_today'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Announcements</h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-vcenter mb-0">
            <thead>
                <tr class="text-muted small text-uppercase">
                    <th>Title</th><th>Body</th><th>Target</th><th>Posted By</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($announcements as $a)
                    <tr>
                        <td class="fw-bold">{{ $a->title }}</td>
                        <td class="small text-muted">{{ \Illuminate\Support\Str::limit($a->body, 60) }}</td>
                        <td><span class="badge badge-target">{{ $a->barangay ?? 'All Barangays' }}</span></td>
                        <td>{{ $a->creator->name ?? '-' }}</td>
                        <td>{{ $a->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted small py-3">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="toast" style="display:none; position:fixed; bottom:24px; right:24px; z-index:2000; min-width:300px; padding:16px 20px; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.2); color:#fff; font-weight:500;"></div>

<script>
    const templates = {
        flood: { title: 'Flood Warning', message: 'FLOOD WARNING: Rising water levels detected. Residents in low-lying areas are advised to move to higher ground immediately. Stay tuned for updates.' },
        fire: { title: 'Fire Alert', message: 'FIRE ALERT: A fire incident has been reported in your area. Please evacuate calmly and avoid the affected zone until further notice.' },
        landslide: { title: 'Landslide Warning', message: 'LANDSLIDE WARNING: Ground movement detected in your barangay. Residents near slopes are advised to evacuate to designated safe areas.' },
        earthquake: { title: 'Earthquake Advisory', message: 'EARTHQUAKE ADVISORY: A significant earthquake has occurred. Check your surroundings for damage and avoid entering weakened structures.' },
        typhoon: { title: 'Typhoon Advisory', message: 'TYPHOON ADVISORY: A typhoon signal has been raised for our municipality. Secure your homes and prepare emergency supplies.' },
        general: { title: 'Announcement', message: 'ANNOUNCEMENT: Please be advised of the following important update from MDRRMO San Rafael.' },
    };

    document.querySelectorAll('#categoryButtons button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#categoryButtons button').forEach(b => b.classList.remove('active', 'btn-dark'));
            btn.classList.add('active', 'btn-dark');
            const t = templates[btn.dataset.category];
            document.getElementById('alertTitle').value = t.title;
            document.getElementById('alertMessage').value = t.message;
        });
    });

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.style.background = type === 'success' ? '#1CA97B' : '#D63939';
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3500);
    }

    function broadcastAlert() {
        const title = document.getElementById('alertTitle').value.trim();
        const message = document.getElementById('alertMessage').value.trim();
        const barangay = document.getElementById('alertBarangay').value;

        if (!title || !message) {
            showToast('Please fill in both a title and a message before broadcasting.', 'error');
            return;
        }

        fetch("{{ route('send-alert.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ title, message, barangay }),
        })
        .then(res => res.json())
        .then(() => {
            showToast('Alert broadcasted successfully.');
            document.getElementById('alertTitle').value = '';
            document.getElementById('alertMessage').value = '';
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(() => {
            showToast('Broadcast failed. Please try again.', 'error');
        });
    }
</script>
@endsection